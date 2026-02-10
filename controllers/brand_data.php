<?php
// brand_data.php
header('Content-Type: application/json');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // In a real application, you should log this error and return a generic message
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get the category from the GET request
$category = $_GET['category'] ?? '';

$brands = [];

if (!empty($category)) {
    // Sanitize the input for safety (optional, but good practice, since it's used in a placeholder query)
    $safe_category = $conn->real_escape_string($category);

    // In a real application, you would have a dedicated 'brands' table
    // For this example, we'll return hardcoded brands based on category
    // Replace this with a real DB query like: 
    // $sql = "SELECT DISTINCT brandName FROM product WHERE category = '{$safe_category}' ORDER BY brandName";
    
    // Example hardcoded brand data:
    if ($safe_category === 'Panel') {
        $brands = ["Trina Solar", "Jinko Solar", "Aiko", "Lvtopsun", "Aerosolar", "IanSolar"];
    } elseif ($safe_category === 'Inverter') {
        $brands = ["Huawei", "Solis", "TrinaSolar", "Solax", "Deye", "LuxPower"];
    } elseif ($safe_category === 'Battery') {
        $brands = ["IanSolar", "Solax", "TrinaSolar", "JA Solar", "HoyMiles"];
    } elseif ($safe_category === 'Package') {
        $brands = ["Grid-tie", "Hybrid"];
    } else {
        $brands = ["Universal Brand"];
    }
}

// Return the brands as a JSON array
echo json_encode(array_unique($brands));

$conn->close();
?>