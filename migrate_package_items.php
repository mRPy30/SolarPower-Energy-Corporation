<?php
include "config/dbconn.php";

$sql = "CREATE TABLE IF NOT EXISTS `package_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    FOREIGN KEY (`package_id`) REFERENCES `product`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "SUCCESS: package_items table created successfully.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

$conn->close();
?>
