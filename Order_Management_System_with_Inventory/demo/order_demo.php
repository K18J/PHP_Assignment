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

$order = new Order(null, 'ORD-' . time(), 'Demo Customer');
$order->addItem(new OrderItem(productId: 1, warehouseId: 1, quantity: 2, unitPrice: 10.00));
$order->addItem(new OrderItem(productId: 2, warehouseId: 2, quantity: 1, unitPrice: 15.00));

ob_start();
echo "Creating order...\n";
$orderId = $orderService->createOrder($order);
echo "Order #{$orderId} created with total {$order->total()}\n";

echo "Reserving inventory...\n";
try {
    $orderService->reserveInventoryForOrder($orderId, $order);
    echo "Inventory reserved. Order status set to 'reserved'\n";
} catch (Throwable $e) {
    echo "Reservation failed: {$e->getMessage()}\n";
    ob_end_flush();
    exit(1);
}

echo "Marking order as fulfilled...\n";
$orderService->fulfill($orderId);
echo "Done.\n";
$output = ob_get_clean();

// Path to public assets: when this script is under demo/ use ../public/assets
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
$cssPath = (strpos($scriptPath, 'demo') !== false) ? '../public/assets/style.css' : 'assets/style.css';
$appLink = (strpos($scriptPath, 'demo') !== false) ? '../public/index.php' : 'index.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Demo – Order Management</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($cssPath) ?>">
</head>
<body>
<header class="topbar">
    <div class="brand">Order Management</div>
    <nav class="nav">
        <a href="<?= htmlspecialchars($appLink) ?>">Back to app</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <div class="card-header">
            <h2>Order demo</h2>
            <p>CLI-style output from order creation, reservation, and fulfillment.</p>
        </div>
        <pre class="demo-output"><?= htmlspecialchars($output) ?></pre>
    </section>
</main>
</body>
</html>