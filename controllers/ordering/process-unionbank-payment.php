<?php
/**
 * Process UnionBank Payment
 * File: controllers/ordering/process-unionbank-payment.php
 */

session_start();
require_once '../../config/dbconn.php';

// PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // Make sure you have PHPMailer installed

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get form data
    $customerName = $_POST['customerName'] ?? '';
    $customerEmail = $_POST['customerEmail'] ?? '';
    $customerPhone = $_POST['customerPhone'] ?? '';
    $customerAddress = $_POST['customerAddress'] ?? '';
    $paymentType = $_POST['paymentType'] ?? 'full';
    $amountToPay = floatval($_POST['amountToPay'] ?? 0);
    $totalAmount = floatval($_POST['totalAmount'] ?? 0);
    $items = json_decode($_POST['items'] ?? '[]', true);
    $referenceNumber = $_POST['referenceNumber'] ?? '';
    
    // Validate required fields
    if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($customerAddress)) {
        throw new Exception('Missing required customer information');
    }
    
    if (empty($items)) {
        throw new Exception('No items in order');
    }
    
    if ($amountToPay <= 0 || $totalAmount <= 0) {
        throw new Exception('Invalid payment amount');
    }
    
    // Handle file upload (transaction receipt)
    $receiptPath = '';
    if (isset($_FILES['transactionReceipt']) && $_FILES['transactionReceipt']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/receipts/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['transactionReceipt']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and PDF are allowed');
        }
        
        // Generate unique filename
        $fileName = 'receipt_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $receiptPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($_FILES['transactionReceipt']['tmp_name'], $receiptPath)) {
            throw new Exception('Failed to upload receipt');
        }
    } else {
        throw new Exception('Transaction receipt is required');
    }
    
    // Generate order reference
    $orderRef = 'UB-' . strtoupper(substr($paymentType, 0, 1)) . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                order_reference,
                customer_name,
                customer_email,
                customer_phone,
                customer_address,
                total_amount,
                amount_paid,
                payment_method,
                payment_status,
                order_status,
                receipt_path,
                reference_number,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, NOW())
        ");
        
        $paymentMethod = 'unionbank_' . $paymentType;
        $paymentStatus = 'pending_verification'; // Admin needs to verify receipt
        
        $stmt->bind_param(
            'sssssddsss',
            $orderRef,
            $customerName,
            $customerEmail,
            $customerPhone,
            $customerAddress,
            $totalAmount,
            $amountToPay,
            $paymentMethod,
            $receiptPath,
            $referenceNumber
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order: ' . $stmt->error);
        }
        
        $orderId = $conn->insert_id;
        $stmt->close();
        
        // 2. Insert order items
        $stmtItems = $conn->prepare("
            INSERT INTO order_items (
                order_id,
                product_name,
                price,
                quantity
            ) VALUES (?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $productName = $item['name'] ?? 'Unknown Product';
            $price = floatval($item['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            
            $stmtItems->bind_param('isdi', $orderId, $productName, $price, $quantity);
            
            if (!$stmtItems->execute()) {
                throw new Exception('Failed to add order item: ' . $stmtItems->error);
            }
        }
        $stmtItems->close();
        
        // 3. Send email notifications
        sendOrderConfirmationEmail($orderRef, $customerName, $customerEmail, $items, $totalAmount, $amountToPay, $paymentType, $receiptPath);
        sendAdminNotificationEmail($orderRef, $customerName, $customerEmail, $customerPhone, $items, $totalAmount, $amountToPay, $paymentType, $receiptPath);
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'orderRef' => $orderRef,
            'message' => 'Order submitted successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('UnionBank Payment Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();

/**
 * Send order confirmation email to customer
 */
function sendOrderConfirmationEmail($orderRef, $customerName, $customerEmail, $items, $totalAmount, $amountPaid, $paymentType, $receiptPath) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP configuration (update with your settings)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Your email
        $mail->Password = 'your-app-password'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('your-email@gmail.com', 'SolarPower Energy');
        $mail->addAddress($customerEmail, $customerName);
        
        // Payment type labels
        $paymentLabels = [
            'full' => 'Full Payment (100%)',
            'downpayment' => '50% Down Payment',
            'initial' => '20% Initial Payment'
        ];
        $paymentLabel = $paymentLabels[$paymentType] ?? 'Payment';
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation - $orderRef";
        
        $itemsList = '';
        foreach ($items as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $itemsList .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #ddd;'>{$item['name']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>{$item['quantity']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>₱" . number_format($item['price'], 2) . "</td>
                    <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>₱" . number_format($itemTotal, 2) . "</td>
                </tr>
            ";
        }
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #004b8d; color: white; padding: 20px; text-align: center;'>
                    <h1>Order Confirmation</h1>
                </div>
                
                <div style='padding: 20px; background: #f8f9fa;'>
                    <p>Dear <strong>$customerName</strong>,</p>
                    <p>Thank you for your order! We have received your payment and are processing your order.</p>
                    
                    <div style='background: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #004b8d;'>Order Details</h3>
                        <p><strong>Order Reference:</strong> $orderRef</p>
                        <p><strong>Payment Method:</strong> UnionBank Direct Deposit</p>
                        <p><strong>Payment Type:</strong> $paymentLabel</p>
                    </div>
                    
                    <table style='width: 100%; border-collapse: collapse; background: white;'>
                        <thead>
                            <tr style='background: #004b8d; color: white;'>
                                <th style='padding: 10px; text-align: left;'>Product</th>
                                <th style='padding: 10px; text-align: center;'>Qty</th>
                                <th style='padding: 10px; text-align: right;'>Price</th>
                                <th style='padding: 10px; text-align: right;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            $itemsList
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold;'>Amount Paid:</td>
                                <td style='padding: 10px; text-align: right; font-weight: bold; color: #28a745;'>₱" . number_format($amountPaid, 2) . "</td>
                            </tr>
                            <tr>
                                <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold;'>Total Amount:</td>
                                <td style='padding: 10px; text-align: right; font-weight: bold;'>₱" . number_format($totalAmount, 2) . "</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>⏳ Payment Verification</strong></p>
                        <p>Your transaction receipt is being verified by our team. You will receive an email confirmation once verified (usually within 24 hours).</p>
                    </div>
                    
                    <p>If you have any questions, please contact us at:</p>
                    <p>📧 Email: solar@solarpower.com.ph<br>
                    📞 Phone: 0995-394-7379</p>
                </div>
                
                <div style='background: #004b8d; color: white; padding: 15px; text-align: center;'>
                    <p style='margin: 0;'>Thank you for choosing SolarPower Energy!</p>
                </div>
            </div>
        ";
        
        $mail->send();
        
    } catch (Exception $e) {
        error_log("Failed to send customer email: {$mail->ErrorInfo}");
    }
}

/**
 * Send notification email to admin
 */
function sendAdminNotificationEmail($orderRef, $customerName, $customerEmail, $customerPhone, $items, $totalAmount, $amountPaid, $paymentType, $receiptPath) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('your-email@gmail.com', 'SolarPower System');
        $mail->addAddress('janvierericksonaraque@gmail.com', 'Admin'); // Boss email
        
        // Attach receipt
        if (file_exists($receiptPath)) {
            $mail->addAttachment($receiptPath);
        }
        
        // Payment type labels
        $paymentLabels = [
            'full' => 'Full Payment (100%)',
            'downpayment' => '50% Down Payment',
            'initial' => '20% Initial Payment'
        ];
        $paymentLabel = $paymentLabels[$paymentType] ?? 'Payment';
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = "🔔 New Order - $orderRef (UnionBank Payment)";
        
        $itemsList = '';
        foreach ($items as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $itemsList .= "
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$item['name']}</td>
                    <td style='padding: 8px; border: 1px solid #ddd; text-align: center;'>{$item['quantity']}</td>
                    <td style='padding: 8px; border: 1px solid #ddd; text-align: right;'>₱" . number_format($item['price'], 2) . "</td>
                    <td style='padding: 8px; border: 1px solid #ddd; text-align: right;'>₱" . number_format($itemTotal, 2) . "</td>
                </tr>
            ";
        }
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 700px;'>
                <div style='background: #dc3545; color: white; padding: 20px;'>
                    <h1>🔔 New Order Received</h1>
                    <p style='margin: 0;'>UnionBank Direct Deposit - Needs Verification</p>
                </div>
                
                <div style='padding: 20px; background: #f8f9fa;'>
                    <div style='background: white; padding: 15px; border-left: 4px solid #dc3545; margin-bottom: 20px;'>
                        <h3 style='margin-top: 0; color: #dc3545;'>⚠️ ACTION REQUIRED</h3>
                        <p><strong>Verify the attached transaction receipt and update order status in admin panel.</strong></p>
                    </div>
                    
                    <h3>Order Information</h3>
                    <table style='width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px;'>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 30%;'>Order Reference:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$orderRef</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Payment Method:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>UnionBank Direct Deposit</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Payment Type:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$paymentLabel</td>
                        </tr>
                    </table>
                    
                    <h3>Customer Details</h3>
                    <table style='width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px;'>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 30%;'>Name:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$customerName</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Email:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$customerEmail</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Phone:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$customerPhone</td>
                        </tr>
                    </table>
                    
                    <h3>Order Items</h3>
                    <table style='width: 100%; border-collapse: collapse; background: white;'>
                        <thead>
                            <tr style='background: #004b8d; color: white;'>
                                <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Product</th>
                                <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Qty</th>
                                <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Price</th>
                                <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            $itemsList
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold; border: 1px solid #ddd;'>Amount Paid:</td>
                                <td style='padding: 10px; text-align: right; font-weight: bold; color: #28a745; border: 1px solid #ddd;'>₱" . number_format($amountPaid, 2) . "</td>
                            </tr>
                            <tr>
                                <td colspan='3' style='padding: 10px; text-align: right; font-weight: bold; border: 1px solid #ddd;'>Total Order Amount:</td>
                                <td style='padding: 10px; text-align: right; font-weight: bold; border: 1px solid #ddd;'>₱" . number_format($totalAmount, 2) . "</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p><strong>📎 Transaction Receipt Attached</strong></p>
                        <p>Please verify the receipt and update the order status in the admin panel.</p>
                    </div>
                </div>
            </div>
        ";
        
        $mail->send();
        
    } catch (Exception $e) {
        error_log("Failed to send admin email: {$mail->ErrorInfo}");
    }
}
?>