<?php
// Ensure this file is set up to connect to your database (e.g., $conn)
require_once 'config/dbconn.php';
require 'vendor/autoload.php'; // Include PHPMailer autoload file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // 1. Check if email exists
        $stmt = $conn->prepare("SELECT status FROM subscription_tbl WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['status'] === 'confirmed') {
                $response = ['status' => 'error', 'message' => 'This email is already subscribed.'];
            } else {
                // Resend confirmation email for pending subscriptions
                $tokenStmt = $conn->prepare("SELECT confirmation_token FROM subscription_tbl WHERE email = ?");
                $tokenStmt->bind_param("s", $email);
                $tokenStmt->execute();
                $tokenResult = $tokenStmt->get_result();
                $tokenRow = $tokenResult->fetch_assoc();
                $token = $tokenRow['confirmation_token'];
                $tokenStmt->close();
                
                // Send confirmation email again
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'araquejanvier@gmail.com'; // Change to environment variable
                    $mail->Password   = 'mxxb gqka dvgh zjmj'; // Change to environment variable
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                    
                    $mail->Port       = 465;

                    // Recipients
                    $mail->setFrom('no-reply@solarpower.com.ph', 'Solar Power');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Confirm Your Subscription to Solar Power News';
                    
                    // Update with your actual domain
                    $confirmationLink = 'http://localhost/solarpower/confirm.php?email=' . urlencode($email) . '&token=' . $token;
                    
                    $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #2ecc71;'>Welcome to Solar Power!</h2>
                        <p>Thank you for subscribing to our newsletter. Please confirm your subscription by clicking the button below:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$confirmationLink' style='background-color: #2ecc71; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Confirm Subscription</a>
                        </div>
                        <p style='color: #666; font-size: 12px;'>If you didn't subscribe, please ignore this email.</p>
                        <p style='color: #666; font-size: 12px;'>Or copy and paste this link: $confirmationLink</p>
                    </div>";
                    
                    $mail->AltBody = "Thank you for subscribing! Please copy and paste this link to confirm: $confirmationLink";

                    $mail->send();
                    $response = ['status' => 'success', 'message' => 'Confirmation email resent! Please check your inbox.'];
                } catch (Exception $e) {
                    error_log("PHPMailer Error: {$mail->ErrorInfo}");
                    $response = ['status' => 'error', 'message' => 'Could not send confirmation email. Please try again later.'];
                }
            }
        } else {
            // 2. Generate a unique token for confirmation
            $token = bin2hex(random_bytes(32)); // 64 characters long token
            
            // 3. Insert into database with 'pending' status
            $insertStmt = $conn->prepare("INSERT INTO subscription_tbl (email, confirmation_token, status, created_at) VALUES (?, ?, 'pending', NOW())");
            $insertStmt->bind_param("ss", $email, $token);

            if ($insertStmt->execute()) {
                // 4. Send Confirmation Email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'araquejanvier@gmail.com'; // Change to environment variable
                    $mail->Password   = 'mxxb gqka dvgh zjmj'; // Change to environment variable
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                    
                    $mail->Port       = 465;

                    // Recipients
                    $mail->setFrom('no-reply@solarpower.com.ph', 'Solar Power');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Confirm Your Subscription to Solar Power News';
                    
                    // Update with your actual domain
                    $confirmationLink = 'http://localhost/solarpower/confirm.php?email=' . urlencode($email) . '&token=' . $token;
                    
                    $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #2ecc71;'>Welcome to Solar Power!</h2>
                        <p>Thank you for subscribing to our newsletter. Please confirm your subscription by clicking the button below:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$confirmationLink' style='background-color: #2ecc71; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Confirm Subscription</a>
                        </div>
                        <p style='color: #666; font-size: 12px;'>If you didn't subscribe, please ignore this email.</p>
                        <p style='color: #666; font-size: 12px;'>Or copy and paste this link: $confirmationLink</p>
                    </div>";
                    
                    $mail->AltBody = "Thank you for subscribing! Please copy and paste this link to confirm: $confirmationLink";

                    $mail->send();
                    $response = ['status' => 'success', 'message' => 'Subscribed successfully! Check your email for confirmation.'];
                } catch (Exception $e) {
                    error_log("PHPMailer Error: {$mail->ErrorInfo}");
                    // Delete the pending entry if email failed
                    $conn->query("DELETE FROM subscription_tbl WHERE email = '$email'");
                    $response = ['status' => 'error', 'message' => 'Could not send confirmation email. Please try again later.'];
                }

            } else {
                $response = ['status' => 'error', 'message' => 'Database error: Could not save subscription request.'];
            }
            $insertStmt->close();
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'Please enter a valid email address.'];
    }
    
    $conn->close();
    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>