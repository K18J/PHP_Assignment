<?php

declare(strict_types=1);

use PDO;
use OrderManagement\Database\ConnectionFactory;
use OrderManagement\Entities\Order;
use OrderManagement\Entities\OrderItem;
use OrderManagement\Entities\StockAdjustment;
use OrderManagement\Entities\StockTransfer;
use OrderManagement\Repositories\InventoryRepository;
use OrderManagement\Repositories\OrderRepository;
use OrderManagement\Services\InventoryAllocationService;
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

$pdo = ConnectionFactory::make(__DIR__ . '/../config/database.php');
$orderRepository = new OrderRepository($pdo);
$inventoryRepository = new InventoryRepository($pdo);
$orderService = new OrderService($orderRepository, $inventoryRepository, $pdo);
$allocationService = new InventoryAllocationService($inventoryRepository, $pdo);

$page = $_GET['page'] ?? 'orders';
$notice = $_GET['notice'] ?? null;
$error = $_GET['error'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $returnTo = $_POST['return_to'] ?? 'orders';

    try {
        switch ($action) {
            case 'create_order':
                handleCreateOrder($_POST, $orderService);
                break;
            case 'reserve_order':
                handleReserveOrder((int) ($_POST['order_id'] ?? 0), $orderRepository, $orderService);
                break;
            case 'fulfill_order':
                $orderId = (int) ($_POST['order_id'] ?? 0);
                $partial = isset($_POST['partial']);
                $orderService->fulfill($orderId, $partial);
                redirectWithMessage($returnTo, 'notice', 'Order marked as fulfilled.');
                break;
            case 'adjust_stock':
                handleAdjustStock($_POST, $inventoryRepository);
                break;
            case 'transfer_stock':
                handleTransferStock($_POST, $inventoryRepository);
                break;
            case 'expire_reservations':
                $allocationService->expireReservations();
                redirectWithMessage($returnTo, 'notice', 'Expired reservations cleaned up.');
                break;
            case 'release_reservation':
                $reservationId = (int) ($_POST['reservation_id'] ?? 0);
                $inventoryRepository->releaseReservation($reservationId);
                redirectWithMessage($returnTo, 'notice', 'Reservation released.');
                break;
            default:
                redirectWithMessage($returnTo, 'error', 'Unknown action.');
        }
    } catch (\Throwable $e) {
        redirectWithMessage($returnTo, 'error', $e->getMessage());
    }
}

$products = fetchProducts($pdo);
$warehouses = fetchWarehouses($pdo);
$orders = fetchOrders($pdo);
$orderItems = fetchOrderItems($pdo, array_column($orders, 'id'));
$inventoryRows = fetchInventory($pdo);
$reservations = fetchReservations($pdo);

function handleCreateOrder(array $payload, OrderService $service): void
{
    $customer = trim((string) ($payload['customer_name'] ?? ''));
    if ($customer === '') {
        throw new RuntimeException('Customer name is required.');
    }

    $orderNumber = trim((string) ($payload['order_number'] ?? ''));
    if ($orderNumber === '') {
        $orderNumber = 'ORD-' . date('Ymd-His');
    }

    $products = $payload['product_id'] ?? [];
    $warehouses = $payload['warehouse_id'] ?? [];
    $quantities = $payload['quantity'] ?? [];
    $prices = $payload['unit_price'] ?? [];

    $order = new Order(null, $orderNumber, $customer);
    $itemCount = count($products);

    for ($i = 0; $i < $itemCount; $i++) {
        $productId = (int) $products[$i];
        $warehouseId = (int) ($warehouses[$i] ?? 0);
        $quantity = (int) ($quantities[$i] ?? 0);
        $unitPrice = (float) ($prices[$i] ?? 0);

        if ($productId <= 0 || $warehouseId <= 0 || $quantity <= 0 || $unitPrice <= 0) {
            continue;
        }

        $order->addItem(new OrderItem($productId, $warehouseId, $quantity, $unitPrice));
    }

    if (count($order->items()) === 0) {
        throw new RuntimeException('Add at least one valid line item.');
    }

    $orderId = $service->createOrder($order);

    if (isset($payload['reserve_now'])) {
        $service->reserveInventoryForOrder($orderId, $order);
    }

    redirectWithMessage('orders', 'notice', "Order #{$orderId} created.");
}

function handleReserveOrder(int $orderId, OrderRepository $repository, OrderService $service): void
{
    if ($orderId <= 0) {
        throw new RuntimeException('Invalid order id.');
    }

    $data = $repository->find($orderId);
    if (!$data) {
        throw new RuntimeException('Order not found.');
    }

    $orderRow = $data['order'];
    $order = new Order(
        (int) $orderRow['id'],
        (string) $orderRow['order_number'],
        (string) $orderRow['customer_name'],
        (string) $orderRow['status']
    );

    foreach ($data['items'] as $item) {
        $order->addItem(
            new OrderItem(
                (int) $item['product_id'],
                (int) $item['warehouse_id'],
                (int) $item['quantity'],
                (float) $item['unit_price']
            )
        );
    }

    $service->reserveInventoryForOrder($orderId, $order);
    redirectWithMessage('orders', 'notice', 'Inventory reserved for order.');
}

function handleAdjustStock(array $payload, InventoryRepository $repository): void
{
    $productId = (int) ($payload['product_id'] ?? 0);
    $warehouseId = (int) ($payload['warehouse_id'] ?? 0);
    $delta = (int) ($payload['delta'] ?? 0);
    $reason = trim((string) ($payload['reason'] ?? ''));

    if ($productId <= 0 || $warehouseId <= 0 || $delta === 0) {
        throw new RuntimeException('Product, warehouse and non-zero quantity are required.');
    }

    $repository->adjustStock(new StockAdjustment($productId, $warehouseId, $delta, $reason));
    redirectWithMessage('stock', 'notice', 'Stock adjusted.');
}

function handleTransferStock(array $payload, InventoryRepository $repository): void
{
    $productId = (int) ($payload['product_id'] ?? 0);
    $from = (int) ($payload['from_warehouse_id'] ?? 0);
    $to = (int) ($payload['to_warehouse_id'] ?? 0);
    $quantity = (int) ($payload['quantity'] ?? 0);
    $reference = trim((string) ($payload['reference'] ?? ''));

    if ($productId <= 0 || $from <= 0 || $to <= 0 || $quantity <= 0) {
        throw new RuntimeException('Product, from/to warehouses and quantity are required.');
    }

    if ($from === $to) {
        throw new RuntimeException('Choose different warehouses.');
    }

    $repository->transferStock(new StockTransfer($productId, $from, $to, $quantity, $reference));
    redirectWithMessage('stock', 'notice', 'Transfer completed.');
}

function fetchProducts(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, sku, name, unit_price FROM products ORDER BY name ASC');
    return $stmt->fetchAll() ?: [];
}

function fetchWarehouses(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, code, name FROM warehouses ORDER BY name ASC');
    return $stmt->fetchAll() ?: [];
}

function fetchOrders(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
    return $stmt->fetchAll() ?: [];
}

function fetchOrderItems(PDO $pdo, array $orderIds): array
{
    if (count($orderIds) === 0) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT oi.*, p.sku, p.name AS product_name, w.code AS warehouse_code
         FROM order_items oi
         JOIN products p ON p.id = oi.product_id
         JOIN warehouses w ON w.id = oi.warehouse_id
         WHERE oi.order_id IN ({$placeholders})
         ORDER BY oi.id ASC"
    );
    $stmt->execute($orderIds);
    $rows = $stmt->fetchAll() ?: [];

    $grouped = [];
    foreach ($rows as $row) {
        $grouped[(int) $row['order_id']][] = $row;
    }

    return $grouped;
}

function fetchInventory(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT i.*, p.sku, p.name AS product_name, w.code AS warehouse_code, w.name AS warehouse_name,
                (i.quantity_on_hand - i.quantity_reserved) AS available
         FROM inventory i
         JOIN products p ON p.id = i.product_id
         JOIN warehouses w ON w.id = i.warehouse_id
         ORDER BY p.name ASC, w.code ASC'
    );

    return $stmt->fetchAll() ?: [];
}

function fetchReservations(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT r.*, p.sku, p.name AS product_name, w.code AS warehouse_code, o.order_number
         FROM reservations r
         LEFT JOIN products p ON p.id = r.product_id
         LEFT JOIN warehouses w ON w.id = r.warehouse_id
         LEFT JOIN orders o ON o.id = r.order_id
         ORDER BY r.created_at DESC'
    );

    return $stmt->fetchAll() ?: [];
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirectWithMessage(string $page, string $type, string $message): void
{
    header('Location: index.php?page=' . urlencode($page) . '&' . $type . '=' . urlencode($message));
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management UI</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">Order Management</div>
    <nav class="nav">
        <a href="?page=orders" class="<?= $page === 'orders' ? 'active' : '' ?>">Orders</a>
        <a href="?page=inventory" class="<?= $page === 'inventory' ? 'active' : '' ?>">Inventory</a>
        <a href="?page=reservations" class="<?= $page === 'reservations' ? 'active' : '' ?>">Reservations</a>
        <a href="?page=stock" class="<?= $page === 'stock' ? 'active' : '' ?>">Stock Ops</a>
    </nav>
</header>

<main class="container">
    <?php if ($notice): ?>
        <div class="alert success"><?= h((string) $notice) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= h((string) $error) ?></div>
    <?php endif; ?>

    <?php if ($page === 'orders'): ?>
        <section class="card">
            <div class="card-header">
                <h2>Create Order</h2>
                <p>Build an order and optionally reserve inventory immediately.</p>
            </div>
            <form method="post" class="stacked-form" id="order-form">
                <input type="hidden" name="action" value="create_order">
                <div class="grid">
                    <label>
                        Order #
                        <input type="text" name="order_number" placeholder="auto-generate if blank">
                    </label>
                    <label>
                        Customer Name
                        <input type="text" name="customer_name" required>
                    </label>
                </div>

                <div class="line-items">
                    <div class="line-items__head">
                        <div>Product</div>
                        <div>Warehouse</div>
                        <div>Qty</div>
                        <div>Unit Price</div>
                        <div></div>
                    </div>
                    <div id="line-items-body">
                        <div class="line-items__row">
                            <select name="product_id[]" required>
                                <option value="">Select product</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= h((string) $p['id']) ?>" data-price="<?= h((string) $p['unit_price']) ?>">
                                        <?= h($p['sku'] . ' - ' . $p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="warehouse_id[]" required>
                                <option value="">Select warehouse</option>
                                <?php foreach ($warehouses as $w): ?>
                                    <option value="<?= h((string) $w['id']) ?>">
                                        <?= h($w['code'] . ' - ' . $w['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="quantity[]" min="1" value="1" required>
                            <input type="number" step="0.01" min="0" name="unit_price[]" value="0.00" required class="unit-price">
                            <button type="button" class="ghost remove-line">✕</button>
                        </div>
                    </div>
                    <div class="line-items__actions">
                        <button type="button" class="secondary" id="add-line">+ Add Line</button>
                    </div>
                </div>

                <label class="checkbox">
                    <input type="checkbox" name="reserve_now" checked>
                    Reserve inventory now
                </label>

                <button type="submit" class="primary">Create Order</button>
            </form>
        </section>

        <section class="card">
            <div class="card-header">
                <h2>Orders</h2>
                <p>Recent orders with items and status.</p>
            </div>
            <?php if (count($orders) === 0): ?>
                <p class="muted">No orders yet.</p>
            <?php else: ?>
                <div class="table">
                    <div class="table__head">
                        <div>Order</div>
                        <div>Customer</div>
                        <div>Status</div>
                        <div>Total</div>
                        <div>Actions</div>
                    </div>
                    <?php foreach ($orders as $order): ?>
                        <?php $items = $orderItems[$order['id']] ?? []; ?>
                        <div class="table__row">
                            <div>
                                <div class="strong"><?= h($order['order_number']) ?></div>
                                <div class="muted small">Created <?= h($order['created_at']) ?></div>
                                <?php if ($items): ?>
                                    <ul class="small muted">
                                        <?php foreach ($items as $it): ?>
                                            <li>
                                                <?= h($it['sku']) ?> @ <?= h((string) $it['unit_price']) ?> × <?= h((string) $it['quantity']) ?>
                                                (<?= h($it['warehouse_code']) ?>)
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div><?= h($order['customer_name']) ?></div>
                            <div><span class="status status-<?= h($order['status']) ?>"><?= h($order['status']) ?></span></div>
                            <div>$<?= h(number_format((float) $order['total'], 2)) ?></div>
                            <div class="actions">
                                <?php if ($order['status'] === 'draft'): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="reserve_order">
                                        <input type="hidden" name="order_id" value="<?= h((string) $order['id']) ?>">
                                        <button type="submit" class="primary">Reserve</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (in_array($order['status'], ['reserved', 'paid'], true)): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="fulfill_order">
                                        <input type="hidden" name="order_id" value="<?= h((string) $order['id']) ?>">
                                        <button type="submit" class="secondary">Fulfill</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php elseif ($page === 'inventory'): ?>
        <section class="card">
            <div class="card-header">
                <h2>Inventory</h2>
                <p>On-hand, reserved, and available per product and warehouse.</p>
            </div>
            <?php if (count($inventoryRows) === 0): ?>
                <p class="muted">No inventory records found.</p>
            <?php else: ?>
                <div class="table">
                    <div class="table__head">
                        <div>Product</div>
                        <div>Warehouse</div>
                        <div>On Hand</div>
                        <div>Reserved</div>
                        <div>Available</div>
                    </div>
                    <?php foreach ($inventoryRows as $row): ?>
                        <div class="table__row">
                            <div>
                                <div class="strong"><?= h($row['sku']) ?></div>
                                <div class="muted small"><?= h($row['product_name']) ?></div>
                            </div>
                            <div><?= h($row['warehouse_code']) ?> (<?= h($row['warehouse_name']) ?>)</div>
                            <div><?= h((string) $row['quantity_on_hand']) ?></div>
                            <div><?= h((string) $row['quantity_reserved']) ?></div>
                            <div class="<?= ((int) $row['available']) < 0 ? 'text-danger' : 'strong' ?>">
                                <?= h((string) $row['available']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php elseif ($page === 'reservations'): ?>
        <section class="card">
            <div class="card-header">
                <h2>Reservations</h2>
                <p>Active and historical reservations with expiry status.</p>
                <form method="post" class="inline">
                    <input type="hidden" name="action" value="expire_reservations">
                    <input type="hidden" name="return_to" value="reservations">
                    <button type="submit" class="secondary">Expire past reservations</button>
                </form>
            </div>
            <?php if (count($reservations) === 0): ?>
                <p class="muted">No reservations yet.</p>
            <?php else: ?>
                <div class="table">
                    <div class="table__head">
                        <div>Product</div>
                        <div>Order</div>
                        <div>Warehouse</div>
                        <div>Qty</div>
                        <div>Status</div>
                        <div>Expires</div>
                        <div>Action</div>
                    </div>
                    <?php foreach ($reservations as $r): ?>
                        <div class="table__row">
                            <div>
                                <div class="strong"><?= h((string) ($r['sku'] ?? 'N/A')) ?></div>
                                <div class="muted small"><?= h((string) ($r['product_name'] ?? '')) ?></div>
                            </div>
                            <div><?= h((string) ($r['order_number'] ?? '—')) ?></div>
                            <div><?= h((string) ($r['warehouse_code'] ?? '')) ?></div>
                            <div><?= h((string) $r['quantity']) ?></div>
                            <div><span class="status status-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></div>
                            <div><?= h((string) $r['expires_at']) ?></div>
                            <div>
                                <?php if ($r['status'] === 'active'): ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="release_reservation">
                                        <input type="hidden" name="reservation_id" value="<?= h((string) $r['id']) ?>">
                                        <input type="hidden" name="return_to" value="reservations">
                                        <button type="submit" class="secondary">Release</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted small">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php elseif ($page === 'stock'): ?>
        <section class="card">
            <div class="card-header">
                <h2>Adjust Stock</h2>
                <p>Receive or deduct inventory in a warehouse.</p>
            </div>
            <form method="post" class="grid-form">
                <input type="hidden" name="action" value="adjust_stock">
                <div>
                    <label>Product
                        <select name="product_id" required>
                            <option value="">Select product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= h((string) $p['id']) ?>">
                                    <?= h($p['sku'] . ' - ' . $p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div>
                    <label>Warehouse
                        <select name="warehouse_id" required>
                            <option value="">Select warehouse</option>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= h((string) $w['id']) ?>">
                                    <?= h($w['code'] . ' - ' . $w['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div>
                    <label>Quantity (+ add / - deduct)
                        <input type="number" name="delta" required>
                    </label>
                </div>
                <div>
                    <label>Reason
                        <input type="text" name="reason" placeholder="Optional note">
                    </label>
                </div>
                <div class="full-row">
                    <button type="submit" class="primary">Apply Adjustment</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="card-header">
                <h2>Transfer Stock</h2>
                <p>Move inventory between warehouses.</p>
            </div>
            <form method="post" class="grid-form">
                <input type="hidden" name="action" value="transfer_stock">
                <div>
                    <label>Product
                        <select name="product_id" required>
                            <option value="">Select product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= h((string) $p['id']) ?>">
                                    <?= h($p['sku'] . ' - ' . $p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div>
                    <label>From Warehouse
                        <select name="from_warehouse_id" required>
                            <option value="">Select warehouse</option>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= h((string) $w['id']) ?>">
                                    <?= h($w['code'] . ' - ' . $w['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div>
                    <label>To Warehouse
                        <select name="to_warehouse_id" required>
                            <option value="">Select warehouse</option>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= h((string) $w['id']) ?>">
                                    <?= h($w['code'] . ' - ' . $w['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div>
                    <label>Quantity
                        <input type="number" name="quantity" min="1" required>
                    </label>
                </div>
                <div>
                    <label>Reference
                        <input type="text" name="reference" placeholder="Optional note">
                    </label>
                </div>
                <div class="full-row">
                    <button type="submit" class="primary">Transfer</button>
                </div>
            </form>
        </section>
    <?php else: ?>
        <p>Unknown page.</p>
    <?php endif; ?>
</main>

<script>
    const products = <?= json_encode(array_values(array_map(fn ($p) => ['id' => (int) $p['id'], 'price' => (float) $p['unit_price']], $products)), JSON_THROW_ON_ERROR); ?>;
</script>
<script src="assets/app.js"></script>
</body>
</html>

