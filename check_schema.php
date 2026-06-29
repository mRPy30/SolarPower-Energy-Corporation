<?php
include "config/dbconn.php";
echo "--- delivery_rates ---\n";
$result = $conn->query("SELECT * FROM delivery_rates");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
