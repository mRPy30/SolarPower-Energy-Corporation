<?php
/**
 * PDO Database Connection — SolarPower Energy Corporation
 * Automatically shares connection credentials with config/dbconn.php to prevent duplication.
 */

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        // Default fallbacks
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "solar_power";

        $dbconn_path = __DIR__ . '/dbconn.php';
        if (file_exists($dbconn_path)) {
            include $dbconn_path;
        }

        $dsn = 'mysql:host=' . $servername . ';dbname=' . $dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $username, $password, $options);

        // Run migrations for subscribers table to ensure schema compatibility
        try {
            // 1. Create table if not exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `subscribers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `status` VARCHAR(50) NOT NULL DEFAULT 'potential_client',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // 2. Check existing columns
            $columnsStmt = $pdo->query("SHOW COLUMNS FROM `subscribers`");
            $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = [];
            if ($columns) {
                foreach ($columns as $col) {
                    foreach ($col as $key => $val) {
                        if (strtolower($key) === 'field') {
                            $columnNames[] = strtolower($val);
                            break;
                        }
                    }
                }
            }

            // Modify status column type to VARCHAR(50) to support potential_client and contacted
            $pdo->exec("ALTER TABLE `subscribers` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'potential_client'");

            // If subscribed_at exists but created_at does not, rename it to created_at
            if (in_array('subscribed_at', $columnNames) && !in_array('created_at', $columnNames)) {
                $pdo->exec("ALTER TABLE `subscribers` CHANGE COLUMN `subscribed_at` `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            } elseif (!in_array('created_at', $columnNames)) {
                // If created_at doesn't exist at all, add it
                $pdo->exec("ALTER TABLE `subscribers` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            }
        } catch (Exception $e) {
            // Log or ignore migration error so it doesn't block connection
            error_log("Subscribers migration error: " . $e->getMessage());
        }
    }
    return $pdo;
}
