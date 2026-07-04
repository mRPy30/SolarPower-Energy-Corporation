<?php
// ============================================
// FILE 6: payment-cancelled.php
// Cancelled redirect page from Maya
// ============================================

$orderRef = $_GET['ref'] ?? 'Unknown';

if ($orderRef !== 'Unknown') {
    require_once 'config/dbconn.php';
    require_once 'includes/checkout-service.php';

    try {
        checkout_mark_pending_maya_status($conn, $orderRef, 'cancelled');
    } catch (Throwable $e) {
        // Cancelled payments must never create rows in orders/tracking.
    }

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - Solar Power</title>
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cancel-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="cancel-card">
        <i class="fas fa-ban text-warning" style="font-size: 80px;"></i>
        <h1 class="mt-4">Payment Cancelled</h1>
        <p class="lead text-muted">You have cancelled the payment.</p>
        <div class="alert alert-warning mt-4">
            <strong>Order Reference:</strong> <?php echo htmlspecialchars($orderRef); ?>
        </div>
        <p class="text-muted">No order was recorded because the payment was not completed.</p>
        <div class="mt-4">
            <a href="checkout.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-shopping-cart me-2"></i> Complete Order
            </a>
            <a href="index.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>
