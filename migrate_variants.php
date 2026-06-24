<?php
include "config/dbconn.php";

// 1. Create supplier_brands
$sql1 = "CREATE TABLE IF NOT EXISTS `supplier_brands` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `brandName` VARCHAR(255) NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `status` VARCHAR(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql1) === TRUE) {
    echo "SUCCESS: supplier_brands table created.\n";
} else {
    echo "ERROR supplier_brands: " . $conn->error . "\n";
}

// 2. Create product_brand_variants
$sql2 = "CREATE TABLE IF NOT EXISTS `product_brand_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT,
    `brand_id` INT,
    `price` DECIMAL(10,2),
    `variant_image` VARCHAR(255),
    FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`brand_id`) REFERENCES `supplier_brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql2) === TRUE) {
    echo "SUCCESS: product_brand_variants table created.\n";
} else {
    echo "ERROR product_brand_variants: " . $conn->error . "\n";
}

// 3. Populate supplier_brands if empty
$check = $conn->query("SELECT COUNT(*) as count FROM supplier_brands");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    // Attempt to copy from existing brands / categories
    $res = $conn->query("SELECT b.brand_name, c.category_name FROM brands b JOIN categories c ON b.category_id = c.category_id");
    if ($res && $res->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO supplier_brands (brandName, category, status) VALUES (?, ?, 'Active')");
        while ($r = $res->fetch_assoc()) {
            $stmt->bind_param("ss", $r['brand_name'], $r['category_name']);
            $stmt->execute();
        }
        $stmt->close();
        echo "Populated supplier_brands from brands table.\n";
    } else {
        // Fallback default inserts
        $defaults = [
            ['Aiko', 'Panels'],
            ['JA Solar', 'Panels'],
            ['Longi', 'Panels'],
            ['LVTopsun', 'Panels'],
            ['Ian Solar', 'Panels'],
            ['Jinko Solar', 'Panels'],
            ['Trina Solar', 'Panels'],
            ['Growatt', 'Inverters'],
            ['Huawei', 'Inverters'],
            ['Solis', 'Inverters'],
            ['Hopewind', 'Inverters'],
            ['HyxiPower', 'Inverters'],
            ['Solax Power', 'Inverters'],
            ['Hoymiles', 'Inverters'],
            ['BYD', 'Battery'],
            ['Pylontech', 'Battery']
        ];
        $stmt = $conn->prepare("INSERT INTO supplier_brands (brandName, category, status) VALUES (?, ?, 'Active')");
        foreach ($defaults as $item) {
            $stmt->bind_param("ss", $item[0], $item[1]);
            $stmt->execute();
        }
        $stmt->close();
        echo "Populated supplier_brands with defaults.\n";
    }
}

$conn->close();
?>
