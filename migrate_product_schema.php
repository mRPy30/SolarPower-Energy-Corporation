<?php
include "config/dbconn.php";

$queries = [
    "ALTER TABLE product ADD COLUMN packageType ENUM('On-Grid', 'Hybrid', 'Off-Grid') NULL AFTER category",
    "ALTER TABLE product ADD COLUMN status ENUM('Active', 'Hidden') NOT NULL DEFAULT 'Active' AFTER moq"
];

foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        echo "Successfully executed: $q\n";
    } else {
        echo "Error executing $q: " . $conn->error . "\n";
    }
}
$conn->close();
?>
