-- Order Management System with Inventory
-- Run with: mysql -u user -p < sql/schema.sql

CREATE DATABASE IF NOT EXISTS order_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE order_management;

-- Warehouses
CREATE TABLE warehouses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sku (sku)
);

-- Inventory with optimistic locking
CREATE TABLE inventory (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    quantity_on_hand INT NOT NULL DEFAULT 0,
    quantity_reserved INT NOT NULL DEFAULT 0,
    version INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_inventory_product_warehouse (product_id, warehouse_id),
    CONSTRAINT fk_inventory_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_inventory_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

-- Reservation soft-locks with expiry
CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    quantity INT NOT NULL,
    expires_at DATETIME NOT NULL,
    status ENUM('active','released','consumed','expired') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reservation_status_expiry (status, expires_at),
    INDEX idx_reservation_order (order_id),
    CONSTRAINT fk_reservation_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_reservation_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

-- Orders and items
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('draft','reserved','paid','fulfilled','partially_fulfilled','cancelled') NOT NULL DEFAULT 'draft',
    customer_name VARCHAR(255) NOT NULL,
    total DECIMAL(12, 2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_items_order (order_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id),
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_order_items_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

-- Stock movements (receipts, transfers, adjustments)
CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    from_warehouse_id BIGINT UNSIGNED NULL,
    to_warehouse_id BIGINT UNSIGNED NULL,
    quantity INT NOT NULL,
    movement_type ENUM('receipt','transfer','adjustment','reservation','release','consume') NOT NULL,
    reference VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sm_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_sm_from_wh FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id),
    CONSTRAINT fk_sm_to_wh FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id)
);

-- Audit trail (append-only)
CREATE TABLE audit_trail (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    performed_by VARCHAR(100) NULL,
    before_data JSON NULL,
    after_data JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity (entity_type, entity_id)
);

-- Seed sample data
INSERT INTO warehouses (code, name) VALUES
('WH-NORTH', 'North Warehouse'),
('WH-SOUTH', 'South Warehouse');

INSERT INTO products (sku, name, unit_price) VALUES
('SKU-100', 'Sample Widget', 10.00),
('SKU-200', 'Sample Gadget', 15.00);

INSERT INTO inventory (product_id, warehouse_id, quantity_on_hand, quantity_reserved)
SELECT p.id, w.id, 100, 0
FROM products p CROSS JOIN warehouses w;

