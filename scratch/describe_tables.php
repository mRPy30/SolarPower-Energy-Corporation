<?php
include 'config/dbconn.php';

echo "=== supplier_brands ===\n";
$res = $conn->query("DESCRIBE supplier_brands");
while($row = $res->fetch_assoc()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']}\n";
}

echo "\n=== product_brand_variants ===\n";
$res2 = $conn->query("DESCRIBE product_brand_variants");
while($row = $res2->fetch_assoc()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']}\n";
}

