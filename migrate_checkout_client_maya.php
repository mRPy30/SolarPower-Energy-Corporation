<?php
require_once __DIR__ . '/config/dbconn.php';

function columnExists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return intval($count) > 0;
}

function indexExists(mysqli $conn, string $table, string $index): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $stmt->bind_param('ss', $table, $index);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return intval($count) > 0;
}

function constraintExists(mysqli $conn, string $constraint): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?");
    $stmt->bind_param('s', $constraint);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return intval($count) > 0;
}

$changes = [];

if (!columnExists($conn, 'orders', 'client_id')) {
    $conn->query("ALTER TABLE orders ADD COLUMN client_id INT(11) NULL AFTER id");
    $changes[] = 'orders.client_id';
}
if (!columnExists($conn, 'orders', 'items_subtotal')) {
    $conn->query("ALTER TABLE orders ADD COLUMN items_subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER customer_city");
    $changes[] = 'orders.items_subtotal';
}
if (!columnExists($conn, 'orders', 'delivery_fee')) {
    $conn->query("ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER items_subtotal");
    $changes[] = 'orders.delivery_fee';
}
if (!columnExists($conn, 'orders', 'delivery_location')) {
    $conn->query("ALTER TABLE orders ADD COLUMN delivery_location VARCHAR(150) DEFAULT NULL AFTER delivery_fee");
    $changes[] = 'orders.delivery_location';
}
if (!indexExists($conn, 'orders', 'idx_orders_client_id')) {
    $conn->query("ALTER TABLE orders ADD KEY idx_orders_client_id (client_id)");
    $changes[] = 'idx_orders_client_id';
}
if (!constraintExists($conn, 'fk_orders_client_id')) {
    $conn->query("ALTER TABLE orders ADD CONSTRAINT fk_orders_client_id FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE SET NULL");
    $changes[] = 'fk_orders_client_id';
}

echo empty($changes) ? "Checkout client migration already applied.\n" : "Applied: " . implode(', ', $changes) . "\n";
