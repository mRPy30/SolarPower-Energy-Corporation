<?php
// ============================================
// FILE 4: payment-success.php
// Success redirect page from Maya
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderRef = $_GET['ref'] ?? 'Unknown';
$order = null;
$confirmationError = '';

require_once 'config/dbconn.php';
require_once 'includes/checkout-service.php';

try {
    $order = checkout_finalize_paid_maya_order($conn, $orderRef);
    unset($_SESSION['cart']);
} catch (Throwable $e) {
    $confirmationError = $e->getMessage();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Solar Power</title>
    <link rel="icon" type="image/png" href="assets/img/icon.png">
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
        <?php if ($order): ?>
            <i class="fas fa-check-circle success-icon mb-4"></i>
            <h1 class="mb-3">Payment Successful!</h1>
            <p class="lead text-muted">Thank you for your order.</p>
            <div class="alert alert-success mt-4">
                <strong>Order Reference:</strong><br>
                <span style="font-size: 1.2rem; font-weight: bold;"><?php echo htmlspecialchars($order['reference']); ?></span>
            </div>
            <p class="text-muted">Your paid order has been recorded and is now ready for tracking.</p>
        <?php else: ?>
            <i class="fas fa-exclamation-triangle text-warning mb-4" style="font-size: 80px;"></i>
            <h1 class="mb-3">Payment Needs Verification</h1>
            <p class="lead text-muted">Maya returned to the success page, but we could not safely record the order.</p>
            <div class="alert alert-warning mt-4 text-start">
                <strong>Reference:</strong> <?php echo htmlspecialchars($orderRef); ?><br>
                <strong>Reason:</strong> <?php echo htmlspecialchars($confirmationError ?: 'Missing checkout confirmation data.'); ?>
            </div>
            <p class="text-muted">Please contact support and provide your Maya payment reference so we can verify it manually.</p>
        <?php endif; ?>
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>
        </div>
    </div>
    <?php if ($order): ?>
        <script>
            localStorage.removeItem('solarCart');
        </script>
    <?php endif; ?>
</body>
</html>
