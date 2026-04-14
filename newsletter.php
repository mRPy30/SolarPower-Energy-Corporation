<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../views/login.php");
    exit;
}
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $validUsername || $_SERVER['PHP_AUTH_PW'] !== $validPassword) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied.';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
    
    if (!empty($subject) && !empty($body)) {
        // Get all confirmed subscribers
        $stmt = $pdo->prepare("SELECT email FROM subscription WHERE confirmed = 1");
        $stmt->execute();
        $subscribers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($subscribers)) {
            // Log the newsletter
            $stmt = $pdo->prepare("INSERT INTO newsletters (subject, message) VALUES (?, ?)");
            $stmt->execute([$subject, $body]);
            
            // Send to each subscriber
            $headers = 'From: solar@solarpower.com.ph' . "\r\n" .
                       'Reply-To: solar@solarpower.com.ph' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            foreach ($subscribers as $email) {
                mail($email, $subject, $body, $headers);
            }
            $message = 'Newsletter sent successfully to ' . count($subscribers) . ' subscribers!';
        } else {
            $message = 'No confirmed subscribers found.';
        }
    } else {
        $message = 'Please fill in both subject and body.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Send Newsletter</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Send Newsletter to Subscribers</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="body" class="form-label">Message</label>
                <textarea class="form-control" id="body" name="body" rows="10" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Newsletter</button>
        </form>
    </div>
</body>
</html>