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

function tableExists(mysqli $conn, string $table): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return intval($count) > 0;
}

function addColumnIfMissing(mysqli $conn, string $table, string $column, string $definition, array &$changes): void
{
    if (columnExists($conn, $table, $column)) {
        return;
    }

    if (!$conn->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}")) {
        throw new RuntimeException("Failed to add {$table}.{$column}: " . $conn->error);
    }

    $changes[] = "{$table}.{$column}";
}

$changes = [];

addColumnIfMissing($conn, 'orders', 'client_id', 'INT(11) NULL', $changes);
addColumnIfMissing($conn, 'orders', 'items_subtotal', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00', $changes);
addColumnIfMissing($conn, 'orders', 'delivery_fee', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00', $changes);
addColumnIfMissing($conn, 'orders', 'delivery_location', 'VARCHAR(150) DEFAULT NULL', $changes);
addColumnIfMissing($conn, 'orders', 'sales_channel', "VARCHAR(50) NOT NULL DEFAULT 'Website'", $changes);

if (!indexExists($conn, 'orders', 'idx_orders_client_id')) {
    if ($conn->query("ALTER TABLE `orders` ADD KEY `idx_orders_client_id` (`client_id`)")) {
        $changes[] = 'idx_orders_client_id';
    }
}

if (!constraintExists($conn, 'fk_orders_client_id') && tableExists($conn, 'client')) {
    if ($conn->query("ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_client_id` FOREIGN KEY (`client_id`) REFERENCES `client`(`id`) ON DELETE SET NULL")) {
        $changes[] = 'fk_orders_client_id';
    }
}

if (!$conn->query("
    CREATE TABLE IF NOT EXISTS `maya_pending_checkouts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `order_reference` VARCHAR(100) NOT NULL UNIQUE,
        `checkout_id` VARCHAR(150) DEFAULT NULL,
        `checkout_url` TEXT DEFAULT NULL,
        `success_token` VARCHAR(80) DEFAULT NULL,
        `payload_json` LONGTEXT NOT NULL,
        `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
        `order_id` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `paid_at` DATETIME DEFAULT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_maya_pending_status` (`status`),
        INDEX `idx_maya_pending_order_id` (`order_id`)
    )
")) {
    throw new RuntimeException('Failed to create maya_pending_checkouts: ' . $conn->error);
}

addColumnIfMissing($conn, 'maya_pending_checkouts', 'success_token', 'VARCHAR(80) DEFAULT NULL', $changes);

$changes[] = 'maya_pending_checkouts ready';

echo "Applied: " . implode(', ', $changes) . "\n";
