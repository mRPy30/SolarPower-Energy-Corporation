<?php
include "config/dbconn.php";
$result = $conn->query("DESCRIBE order_items");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
