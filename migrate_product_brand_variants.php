<?php
/**
 * migrate_product_brand_variants.php
 *
 * One-shot migration: creates the product_brand_variants junction table
 * that links product rows to brands rows with per-brand price + variant image.
 *
 * Run once from the browser: http://localhost/SolarPower-Energy-Corporation/migrate_product_brand_variants.php
 * Delete this file after a successful run.
 */
include 'config/dbconn.php';

$sql = "
CREATE TABLE IF NOT EXISTS `product_brand_variants` (
  `id`             INT          AUTO_INCREMENT PRIMARY KEY,
  `product_id`     INT          NOT NULL,
  `brand_id`       INT UNSIGNED NOT NULL,
  `price`          DECIMAL(10,2) NOT NULL,
  `variant_image`  VARCHAR(255) NOT NULL DEFAULT '',
  FOREIGN KEY (`product_id`) REFERENCES `product`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`brand_id`)   REFERENCES `brands`(`brand_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conn->query($sql)) {
    echo '<p style="color:green;font-family:monospace;">✅ Table <code>product_brand_variants</code> created (or already exists).</p>';
} else {
    echo '<p style="color:red;font-family:monospace;">❌ Error: ' . htmlspecialchars($conn->error) . '</p>';
}

$conn->close();
