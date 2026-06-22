<?php
// views/staff/subscribers.php - Staff dashboard section for Newsletter Subscribers
error_reporting(0);
ini_set('display_errors', 0);
session_start();

// Redirect to login if not authorized
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../../config/db_pdo.php';
$db = getPDO();

// Auto-create table if not exists just in case
$db->exec("CREATE TABLE IF NOT EXISTS `subscribers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `status` VARCHAR(50) NOT NULL DEFAULT 'potential_client',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Handle AJAX email sending via Resend API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_email') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all email fields.']);
        exit();
    }

    try {
        // Send email via Resend API
        $resendApiKey = 're_Fh6X1rKo_JzjtWaAfUfRiEQs5HHxE4VsV'; 
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fcfcfc; }
                .header { background: linear-gradient(135deg, #115e59, #0f766e); color: #fff; padding: 20px; text-align: center; border-radius: 6px 6px 0 0; }
                .content { padding: 20px; background: #fff; }
                .footer { font-size: 12px; color: #999; text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin:0;'>SolarPower Energy Corporation</h2>
                </div>
                <div class='content'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <div class='footer'>
                    You received this email because you subscribed to our solar updates.<br>
                    Ayala Alabang, Muntinlupa City, Metro Manila, Philippines
                </div>
            </div>
        </body>
        </html>";

        $payload = [
            'from' => 'SolarPower Info <onboarding@resend.dev>',
            'to' => [$email],
            'subject' => $subject,
            'html' => $emailBody
        ];

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $resendApiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            // Update subscriber status to show engagement
            $updateStmt = $db->prepare("UPDATE `subscribers` SET `status` = 'contacted' WHERE `email` = ?");
            $updateStmt->execute([$email]);
            echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Resend API failed with status ' . $httpCode]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

// Fetch all subscribers, latest first
try {
    $stmt = $db->query("SELECT * FROM `subscribers` ORDER BY `created_at` DESC");
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $subscribers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Potential Clients</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: none;
            margin-bottom: 30px;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #0d5c3a, #0a5c3d);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px 25px;
        }
        .badge-client {
            background-color: #fef3c7;
            color: #d97706;
            font-weight: 600;
        }
        .badge-contacted {
            background-color: #dcfce7;
            color: #16a34a;
            font-weight: 600;
        }
        .table th {
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="card dashboard-card">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-newspaper me-2"></i> Potential Clients / Newsletter Leads</h5>
            <span class="badge bg-white fs-6" style="color: #0d5c3a;"><?= count($subscribers) ?> Leads Total</span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($subscribers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users-slash text-muted mb-3" style="font-size: 48px;"></i>
                    <p class="text-muted mb-0">No potential client leads found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email Address</th>
                                <th>Status</th>
                                <th>Date Joined</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $row): 
                                $statusClass = ($row['status'] === 'contacted') ? 'badge-contacted' : 'badge-client';
                                $statusLabel = ($row['status'] === 'contacted') ? 'Contacted' : 'Potential Client';
                            ?>
                                <tr>
                                    <td><strong>#<?= $row['id'] ?></strong></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['email']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-success btn-sm" onclick="openEmailModal('<?= htmlspecialchars($row['email']) ?>')">
                                            <i class="fas fa-envelope-open-text me-1"></i> Send Update
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="modal-header bg-success text-white border-0 py-3" style="background-color: #0d5c3a !important;">
                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i> Email Newsletter Lead</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="emailForm">
                    <input type="hidden" name="action" value="send_email">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Recipient Email</label>
                        <input type="email" class="form-control" name="email" id="recipientEmail" readonly style="background-color: #f1f5f9;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Subject</label>
                        <input type="text" class="form-control" name="subject" required placeholder="Weekly Solar Energy Updates & Tips">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Message Body</label>
                        <textarea class="form-control" name="message" rows="5" required placeholder="Hi, thank you for subscribing to SolarPower Energy Corp. Here are your latest solar updates..."></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="sendBtn" style="background-color: #0d5c3a; border-color: #0d5c3a;">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    let emailModalInstance = null;

    function openEmailModal(email) {
        document.getElementById('recipientEmail').value = email;
        const modalEl = document.getElementById('emailModal');
        emailModalInstance = new bootstrap.Modal(modalEl);
        emailModalInstance.show();
    }

    document.getElementById('emailForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const sendBtn = document.getElementById('sendBtn');
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        const formData = new FormData(this);

        try {
            const response = await fetch('subscribers.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                emailModalInstance.hide();
                location.reload();
            } else {
                alert('Failed to send: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('Network error. Please try again later.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send Email';
        }
    });
</script>
</body>
</html>
