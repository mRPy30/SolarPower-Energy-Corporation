<?php
// ============================================
// FILE 4: payment-success.php
// Success redirect page from Maya
// ============================================

$orderRef = $_GET['ref'] ?? 'Unknown';

// Update order status
require_once 'config/dbconn.php';

if ($orderRef !== 'Unknown') {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET payment_status = 'paid', 
            order_status = 'processing'
        WHERE order_reference = ?
    ");
    $stmt->bind_param('s', $orderRef);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Solar Power</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <i class="fas fa-check-circle success-icon mb-4"></i>
        <h1 class="mb-3">Payment Successful!</h1>
        <p class="lead text-muted">Thank you for your order.</p>
        <div class="alert alert-success mt-4">
            <strong>Order Reference:</strong><br>
            <span style="font-size: 1.2rem; font-weight: bold;"><?php echo htmlspecialchars($orderRef); ?></span>
        </div>
        <p class="text-muted">A confirmation email has been sent to your email address.</p>
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>