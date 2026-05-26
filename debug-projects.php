<?php
require_once 'config/dbconn.php';

// Fetch all projects
$result = mysqli_query($conn, "SELECT id, project_name, image_url FROM portfolio_projects ORDER BY created_at DESC");

echo "<h2>Projects in Database:</h2>";
echo "<pre>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "ID: " . $row['id'] . "\n";
    echo "Name: " . $row['project_name'] . "\n";
    echo "Image URL (raw): " . $row['image_url'] . "\n";
    
    $images = json_decode($row['image_url'], true);
    echo "Image URLs (decoded):\n";
    print_r($images);
    echo "\n---\n";
}
echo "</pre>";

echo "<h2>Checking file existence:</h2>";
echo "<pre>";
$dir = 'uploads/portfolio/';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "Files in $dir:\n";
    print_r($files);
} else {
    echo "Directory $dir does not exist!";
}
echo "</pre>";
?>
