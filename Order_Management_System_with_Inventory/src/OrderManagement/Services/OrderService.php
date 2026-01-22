<?php

declare(strict_types=1);

namespace OrderManagement\Services;

use OrderManagement\Entities\Order;
use OrderManagement\Entities\OrderItem;
use OrderManagement\Entities\ReservationRequest;
use OrderManagement\Repositories\InventoryRepository;
use OrderManagement\Repositories\OrderRepository;
use PDO;
use RuntimeException;

final class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly InventoryRepository $inventoryRepository,
        private readonly PDO $pdo
    ) {
    }

    /**
     * Create an order and persist items.
     */
    public function createOrder(Order $order): int
    {
        $orderId = $this->orderRepository->create($order);
        foreach ($order->items() as $item) {
            $this->orderRepository->addItem($orderId, $item);
        }
        return $orderId;
    }

    /**
     * Reserve inventory for each order item. If any reservation fails, order is cancelled.
     */
    public function reserveInventoryForOrder(int $orderId, Order $order): void
    {
        foreach ($order->items() as $item) {
            $result = $this->inventoryRepository->reserveStock(
                new ReservationRequest(
                    $item->productId,
                    $item->warehouseId,
                    $item->quantity,
                    $orderId
                )
            );

            if (!$result->success) {
                $this->orderRepository->updateStatus($orderId, 'cancelled');
                throw new RuntimeException('Reservation failed: ' . ($result->message ?? 'unknown'));
            }
        }

        $this->orderRepository->updateStatus($orderId, 'reserved');
    }

    /**
     * Marks an order as fulfilled (no shipment logic included).
     */
    public function fulfill(int $orderId, bool $partial = false): void
    {
        $this->orderRepository->updateStatus(
            $orderId,
            $partial ? 'partially_fulfilled' : 'fulfilled'
        );
    }
}

