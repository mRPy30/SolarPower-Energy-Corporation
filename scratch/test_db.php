<?php
require_once 'config/db_pdo.php';
try {
    $db = getPDO();
    echo "Connected successfully.\n";
    $stmt = $db->query('SHOW TABLES');
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
