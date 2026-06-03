<?php
include 'config/dbconn.php';

echo "=== BRANDS ===\n";
$res = $conn->query("DESCRIBE brands");
while($row = $res->fetch_assoc()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']}\n";
}

echo "\n=== SUPPLIER ===\n";
$res2 = $conn->query("DESCRIBE supplier");
while($row = $res2->fetch_assoc()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']}\n";
}
