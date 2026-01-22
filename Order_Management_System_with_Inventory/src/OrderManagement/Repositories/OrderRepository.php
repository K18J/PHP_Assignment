<?php

declare(strict_types=1);

namespace OrderManagement\Repositories;

use OrderManagement\Entities\Order;
use OrderManagement\Entities\OrderItem;
use PDO;
use RuntimeException;

final class OrderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(Order $order): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders (order_number, status, customer_name, total, created_at, updated_at)
             VALUES (:order_number, :status, :customer_name, :total, NOW(), NOW())'
        );
        $stmt->execute([
            'order_number' => $order->orderNumber,
            'status' => $order->status,
            'customer_name' => $order->customerName,
            'total' => $order->total(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function addItem(int $orderId, OrderItem $item): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, warehouse_id, quantity, unit_price, created_at, updated_at)
             VALUES (:order_id, :product_id, :warehouse_id, :quantity, :unit_price, NOW(), NOW())'
        );
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item->productId,
            'warehouse_id' => $item->warehouseId,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $orderId]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Failed to update order status');
        }
    }

    public function find(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $itemsStmt = $this->pdo->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
        $itemsStmt->execute(['order_id' => $orderId]);
        $items = $itemsStmt->fetchAll();

        return ['order' => $order, 'items' => $items];
    }
}

