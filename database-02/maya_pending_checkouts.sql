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
);
