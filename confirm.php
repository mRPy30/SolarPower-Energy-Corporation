<?php
require_once 'config/dbconn.php';

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($email) || empty($token)) {
    die("Invalid confirmation link.");
}

$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$token = htmlspecialchars($token);

$stmt = $conn->prepare("UPDATE subscription_tbl SET status = 'confirmed', confirmation_token = '' WHERE email = ? AND confirmation_token = ? AND status = 'pending'");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Success: Subscription confirmed
    echo "<h1>✅ Subscription Confirmed!</h1>";
    echo "<p>Thank you! You are now subscribed to our news updates.</p>";
} else {
    // Error: Token/Email mismatch or already confirmed
    $checkStmt = $conn->prepare("SELECT status FROM subscription_tbl WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0 && $result->fetch_assoc()['status'] === 'confirmed') {
        echo "<h1>Already Confirmed</h1>";
        echo "<p>Your subscription is already active. Thank you!</p>";
    } else {
        echo "<h1>❌ Confirmation Failed</h1>";
        echo "<p>The confirmation link is invalid or has expired.</p>";
    }
    $checkStmt->close();
}

$stmt->close();
$conn->close();
?>