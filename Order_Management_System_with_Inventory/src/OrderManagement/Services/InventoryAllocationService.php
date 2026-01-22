<?php

declare(strict_types=1);

namespace OrderManagement\Services;

use OrderManagement\Entities\ReservationRequest;
use OrderManagement\Entities\ReservationResult;
use OrderManagement\Repositories\InventoryRepository;
use PDO;

final class InventoryAllocationService
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly PDO $pdo
    ) {
    }

    public function reserve(ReservationRequest $request): ReservationResult
    {
        return $this->inventoryRepository->reserveStock($request);
    }

    public function release(int $reservationId): void
    {
        $this->inventoryRepository->releaseReservation($reservationId);
    }

    /**
     * Marks expired reservations and frees the reserved quantity.
     */
    public function expireReservations(): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->query(
                'SELECT id FROM reservations WHERE status = "active" AND expires_at < NOW() FOR UPDATE'
            );
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->pdo->commit();

            foreach ($ids as $id) {
                $this->inventoryRepository->releaseReservation((int) $id);
                $this->pdo->prepare(
                    'UPDATE reservations SET status = "expired", updated_at = NOW() WHERE id = :id'
                )->execute(['id' => $id]);
            }
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

