<?php
require_once 'config/dbconn.php';

$result = mysqli_query($conn, "SELECT * FROM portfolio_projects ORDER BY id DESC LIMIT 1");
$project = mysqli_fetch_assoc($result);

if ($project) {
    echo "<h3>Latest Project:</h3>";
    echo "ID: " . $project['id'] . "<br>";
    echo "Name: " . $project['project_name'] . "<br>";
    echo "Raw image_url: <code>" . htmlspecialchars($project['image_url']) . "</code><br>";
    
    echo "<hr>";
    echo "<h3>Decoded Images:</h3>";
    $images = json_decode($project['image_url'], true);
    
    if (is_array($images)) {
        foreach ($images as $idx => $img) {
            echo "<strong>Image " . ($idx + 1) . ":</strong> " . htmlspecialchars($img) . "<br>";
            echo "File exists? ";
            if (file_exists($img)) {
                echo "<span style='color:green;'>✓ YES</span>";
            } else {
                echo "<span style='color:red;'>✗ NO</span>";
            }
            echo "<br>";
            echo "Preview: <img src='" . htmlspecialchars($img) . "' width='200' onerror=\"this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22%3E%3Ctext%3EImage Not Found%3C/text%3E%3C/svg%3E'\" style='border:1px solid #ccc;'><br>";
        }
    } else {
        echo "Not a JSON array! Raw: " . htmlspecialchars($project['image_url']);
    }
} else {
    echo "No projects found!";
}
?>
