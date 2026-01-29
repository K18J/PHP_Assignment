<?php

declare(strict_types=1);

namespace OrderManagement\Repositories;

use DateInterval;
use DateTimeImmutable;
use OrderManagement\Entities\Inventory;
use OrderManagement\Entities\ReservationRequest;
use OrderManagement\Entities\ReservationResult;
use OrderManagement\Entities\StockAdjustment;
use OrderManagement\Entities\StockTransfer;
use PDO;
use PDOException;
use RuntimeException;

final class InventoryRepository implements InventoryRepositoryInterface
{
    private const DEADLOCK_CODES = ['1213', '40001'];
    private const MAX_RETRIES = 3;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByProductAndWarehouse(int $productId, int $warehouseId): ?Inventory
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM inventory WHERE product_id = :product_id AND warehouse_id = :warehouse_id'
        );
        $stmt->execute(['product_id' => $productId, 'warehouse_id' => $warehouseId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Inventory(
            (int) $row['id'],
            (int) $row['product_id'],
            (int) $row['warehouse_id'],
            (int) $row['quantity_on_hand'],
            (int) $row['quantity_reserved'],
            (int) $row['version']
        );
    }

    public function reserveStock(ReservationRequest $request): ReservationResult
    {
        $expiresAt = $request->expiresAt ?? (new DateTimeImmutable())->add(new DateInterval('PT15M'));

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $this->pdo->beginTransaction();

                $inventory = $this->lockInventory($request->productId, $request->warehouseId);
                if (!$inventory) {
                    throw new RuntimeException('Inventory row not found');
                }

                if ($inventory->available() < $request->quantity) {
                    throw new RuntimeException('Insufficient available stock to reserve');
                }

                $updated = $this->updateInventoryReserved($inventory, $request->quantity);
                if (!$updated) {
                    throw new RuntimeException('Optimistic lock failed during reserve');
                }

                $reservationId = $this->insertReservation(
                    $request,
                    $expiresAt,
                    'active'
                );

                $this->insertStockMovement(
                    $request->productId,
                    null,
                    $request->warehouseId,
                    $request->quantity,
                    'reservation',
                    $request->orderId ? 'order:' . $request->orderId : null
                );

                $this->pdo->commit();

                return new ReservationResult(true, (int) $reservationId, null);
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                if ($this->isDeadlock($e) && $attempt < self::MAX_RETRIES) {
                    usleep(200_000 * $attempt); 
                    continue;
                }
                return new ReservationResult(false, null, $e->getMessage());
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                return new ReservationResult(false, null, $e->getMessage());
            }
        }

        return new ReservationResult(false, null, 'Reservation failed after retries');
    }

    public function releaseReservation(int $reservationId): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM reservations WHERE id = :id AND status = "active" FOR UPDATE'
            );
            $stmt->execute(['id' => $reservationId]);
            $reservation = $stmt->fetch();
            if (!$reservation) {
                $this->pdo->rollBack();
                return;
            }

            $inventory = $this->lockInventory((int) $reservation['product_id'], (int) $reservation['warehouse_id']);
            if (!$inventory) {
                throw new RuntimeException('Inventory row not found while releasing');
            }

            $updated = $this->updateInventoryReserved($inventory, -1 * (int) $reservation['quantity']);
            if (!$updated) {
                throw new RuntimeException('Optimistic lock failed during release');
            }

            $this->pdo->prepare(
                'UPDATE reservations SET status = "released", updated_at = NOW() WHERE id = :id'
            )->execute(['id' => $reservationId]);

            $this->insertStockMovement(
                (int) $reservation['product_id'],
                null,
                (int) $reservation['warehouse_id'],
                (int) $reservation['quantity'],
                'release',
                'reservation:' . $reservationId
            );

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function transferStock(StockTransfer $transfer): void
    {
        $this->pdo->beginTransaction();
        try {
            $fromInv = $this->lockInventory($transfer->productId, $transfer->fromWarehouseId);
            $toInv = $this->lockInventory($transfer->productId, $transfer->toWarehouseId);

            if (!$fromInv || !$toInv) {
                throw new RuntimeException('Inventory rows not found for transfer');
            }

            if ($fromInv->available() < $transfer->quantity) {
                throw new RuntimeException('Insufficient stock to transfer');
            }

            $fromUpdated = $this->updateInventoryOnHand($fromInv, -1 * $transfer->quantity);
            $toUpdated = $this->updateInventoryOnHand($toInv, $transfer->quantity);

            if (!$fromUpdated || !$toUpdated) {
                throw new RuntimeException('Optimistic lock failed during transfer');
            }

            $this->insertStockMovement(
                $transfer->productId,
                $transfer->fromWarehouseId,
                $transfer->toWarehouseId,
                $transfer->quantity,
                'transfer',
                $transfer->reference
            );

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function adjustStock(StockAdjustment $adjustment): void
    {
        $this->pdo->beginTransaction();
        try {
            $inventory = $this->lockInventory($adjustment->productId, $adjustment->warehouseId);
            if (!$inventory) {
                throw new RuntimeException('Inventory row not found for adjustment');
            }

            $updated = $this->updateInventoryOnHand($inventory, $adjustment->delta);
            if (!$updated) {
                throw new RuntimeException('Optimistic lock failed during adjustment');
            }

            $this->insertStockMovement(
                $adjustment->productId,
                $adjustment->delta < 0 ? $adjustment->warehouseId : null,
                $adjustment->delta > 0 ? $adjustment->warehouseId : null,
                abs($adjustment->delta),
                'adjustment',
                $adjustment->reason
            );

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function lockInventory(int $productId, int $warehouseId): ?Inventory
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM inventory WHERE product_id = :product_id AND warehouse_id = :warehouse_id FOR UPDATE'
        );
        $stmt->execute(['product_id' => $productId, 'warehouse_id' => $warehouseId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Inventory(
            (int) $row['id'],
            (int) $row['product_id'],
            (int) $row['warehouse_id'],
            (int) $row['quantity_on_hand'],
            (int) $row['quantity_reserved'],
            (int) $row['version']
        );
    }

    private function updateInventoryReserved(Inventory $inventory, int $delta): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE inventory
             SET quantity_reserved = quantity_reserved + :delta,
                 version = version + 1,
                 updated_at = NOW()
             WHERE id = :id AND version = :version'
        );
        $stmt->execute([
            'delta' => $delta,
            'id' => $inventory->id,
            'version' => $inventory->version,
        ]);

        return $stmt->rowCount() === 1;
    }

    private function updateInventoryOnHand(Inventory $inventory, int $delta): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE inventory
             SET quantity_on_hand = quantity_on_hand + :delta,
                 version = version + 1,
                 updated_at = NOW()
             WHERE id = :id AND version = :version'
        );
        $stmt->execute([
            'delta' => $delta,
            'id' => $inventory->id,
            'version' => $inventory->version,
        ]);

        return $stmt->rowCount() === 1;
    }

    private function insertReservation(ReservationRequest $request, DateTimeImmutable $expiresAt, string $status): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservations (product_id, warehouse_id, order_id, quantity, expires_at, status, created_at, updated_at)
             VALUES (:product_id, :warehouse_id, :order_id, :quantity, :expires_at, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'product_id' => $request->productId,
            'warehouse_id' => $request->warehouseId,
            'order_id' => $request->orderId,
            'quantity' => $request->quantity,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'status' => $status,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function insertStockMovement(
        int $productId,
        ?int $fromWarehouseId,
        ?int $toWarehouseId,
        int $quantity,
        string $movementType,
        ?string $reference = null
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO stock_movements
             (product_id, from_warehouse_id, to_warehouse_id, quantity, movement_type, reference, created_at)
             VALUES (:product_id, :from_wh, :to_wh, :quantity, :movement_type, :reference, NOW())'
        );
        $stmt->execute([
            'product_id' => $productId,
            'from_wh' => $fromWarehouseId,
            'to_wh' => $toWarehouseId,
            'quantity' => $quantity,
            'movement_type' => $movementType,
            'reference' => $reference,
        ]);
    }

    private function isDeadlock(PDOException $e): bool
    {
        $code = $e->errorInfo[0] ?? '';
        return in_array($code, self::DEADLOCK_CODES, true);
    }
}