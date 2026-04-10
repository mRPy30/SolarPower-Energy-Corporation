<?php
// controllers/subscribe.php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "solar_power";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed. Please try again later.'
    ]);
    exit();
}

// Get email from POST request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email
if (empty($email)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please enter an email address.'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please enter a valid email address.'
    ]);
    exit();
}

// Check if email already exists
$check_sql = "SELECT id, is_active FROM subscribers WHERE email = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    if ($row['is_active'] == 1) {
        echo json_encode([
            'status' => 'info',
            'message' => 'This email is already subscribed to our newsletter!'
        ]);
    } else {
        // Reactivate subscription
        $update_sql = "UPDATE subscribers SET is_active = 1, subscribed_at = NOW() WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $email);
        
        if ($update_stmt->execute()) {
            // Send welcome back email
            sendWelcomeEmail($email, true);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Welcome back! Your subscription has been reactivated.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to reactivate subscription. Please try again.'
            ]);
        }
        $update_stmt->close();
    }
} else {
    // Insert new subscriber
    $insert_sql = "INSERT INTO subscribers (email, subscribed_at, is_active, verification_token) VALUES (?, NOW(), 1, ?)";
    $verification_token = bin2hex(random_bytes(32));
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ss", $email, $verification_token);
    
    if ($insert_stmt->execute()) {
        // Send welcome email
        sendWelcomeEmail($email, false);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you for subscribing! Check your email for confirmation.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to subscribe. Please try again later.'
        ]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();

/**
 * Send welcome email to new subscriber
 */
function sendWelcomeEmail($email, $isReturning = false) {
    $to = $email;
    $subject = $isReturning ? "Welcome Back to SolarPower Energy!" : "Welcome to SolarPower Energy!";
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: SolarPower Energy <noreply@solarpowerenergy.com>" . "\r\n";
    $headers .= "Reply-To: support@solarpowerenergy.com" . "\r\n";
    
    // Email body
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            .benefits { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .benefit-item { margin: 15px 0; padding-left: 30px; position: relative; }
            .benefit-item:before { content: "✓"; position: absolute; left: 0; color: #667eea; font-weight: bold; font-size: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🌞 ' . ($isReturning ? 'Welcome Back!' : 'Welcome!') . '</h1>
                <p>' . ($isReturning ? 'We missed you!' : 'Thank you for joining our community!') . '</p>
            </div>
            <div class="content">
                <h2>Hello Solar Enthusiast!</h2>
                <p>' . ($isReturning ? 
                    'We\'re thrilled to have you back! You\'ll continue receiving weekly updates about:' : 
                    'You\'re now subscribed to receive weekly solar tips, updates, and exclusive offers!') . '</p>
                
                <div class="benefits">
                    <h3>What You\'ll Get:</h3>
                    <div class="benefit-item">Weekly solar energy tips and best practices</div>
                    <div class="benefit-item">Latest product updates and new arrivals</div>
                    <div class="benefit-item">Exclusive subscriber-only discounts</div>
                    <div class="benefit-item">Industry news and sustainability insights</div>
                    <div class="benefit-item">Success stories from solar users</div>
                </div>
                
                <p>Ready to start your solar journey? Explore our products and services:</p>
                <a href="https://yourwebsite.com" class="button">Visit Our Website</a>
                
                <p style="margin-top: 30px;">Have questions? We\'re here to help!</p>
                <p><strong>Contact us:</strong><br>
                📞 +639523847379<br>
                📧 support@solarpowerenergy.com<br>
                💬 <a href="https://wa.me/639523847379">WhatsApp</a> | <a href="https://m.me/757917280729034">Messenger</a></p>
            </div>
            <div class="footer">
                <p>You\'re receiving this email because you subscribed to SolarPower Energy newsletter.</p>
                <p><a href="https://yourwebsite.com/unsubscribe?email=' . urlencode($email) . '">Unsubscribe</a> | <a href="https://yourwebsite.com">Visit Website</a></p>
                <p>© ' . date('Y') . ' SolarPower Energy Corporation. All rights reserved.</p>
                <p>4/F PBB Corporate Building, 1906 Finance Drive, Madrigal Business Park 1<br>Muntinlupa City Metro Manila 1770 Philippines</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    mail($to, $subject, $message, $headers);
}
?>