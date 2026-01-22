<?php

declare(strict_types=1);

use OrderManagement\Database\ConnectionFactory;
use OrderManagement\Entities\Order;
use OrderManagement\Entities\OrderItem;
use OrderManagement\Repositories\InventoryRepository;
use OrderManagement\Repositories\OrderRepository;
use OrderManagement\Services\OrderService;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'OrderManagement\\';
        $baseDir = __DIR__ . '/../src/OrderManagement/';
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require $file;
            }
        }
    });
}

$configPath = __DIR__ . '/../config/database.php';
$pdo = ConnectionFactory::make($configPath);

$orderRepo = new OrderRepository($pdo);
$inventoryRepo = new InventoryRepository($pdo);
$orderService = new OrderService($orderRepo, $inventoryRepo, $pdo);

// Build an order with two items
$order = new Order(null, 'ORD-' . time(), 'Demo Customer');
$order->addItem(new OrderItem(productId: 1, warehouseId: 1, quantity: 2, unitPrice: 10.00));
$order->addItem(new OrderItem(productId: 2, warehouseId: 2, quantity: 1, unitPrice: 15.00));

echo "Creating order...\n";
$orderId = $orderService->createOrder($order);
echo "Order #{$orderId} created with total {$order->total()}\n";

echo "Reserving inventory...\n";
try {
    $orderService->reserveInventoryForOrder($orderId, $order);
    echo "Inventory reserved. Order status set to 'reserved'\n";
} catch (Throwable $e) {
    echo "Reservation failed: {$e->getMessage()}\n";
    exit(1);
}

echo "Marking order as fulfilled...\n";
$orderService->fulfill($orderId);
echo "Done.\n";

