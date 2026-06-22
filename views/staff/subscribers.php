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

// Statistics count helper
$totalCount = count($subscribers);
$potentialCount = count(array_filter($subscribers, fn($s) => $s['status'] === 'potential_client'));
$contactedCount = count(array_filter($subscribers, fn($s) => $s['status'] === 'contacted'));
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
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            color: #334155;
            padding: 20px;
        }

        /* Stats Grid */
        .inq-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .inq-stat {
            background: #fff;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid #e2e8f0;
        }

        .inq-stat-num {
            display: block;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .inq-stat-lbl {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* Toolbar */
        .inq-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .inq-search {
            flex: 1;
            min-width: 200px;
            padding: 9px 14px 9px 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat 11px center;
        }

        .inq-search:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .15);
        }

        .inq-filter-sel {
            padding: 9px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #fff;
            cursor: pointer;
        }

        .inq-filter-sel:focus {
            border-color: #f59e0b;
        }

        /* Table */
        .inq-table-wrap {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .inq-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .inq-table thead tr {
            background: #0d5c3a;
        }

        .inq-table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 700;
            color: #ffffff;
            font-size: 11px;
            letter-spacing: .5px;
            border-bottom: 2px solid #0a492e;
            white-space: nowrap;
        }

        .inq-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .inq-table tbody tr:hover {
            background: #fffbeb;
        }

        .inq-table tbody tr:last-child td {
            border-bottom: none;
        }

        .inq-td-num {
            color: #94a3b8;
            font-weight: 600;
            font-size: 12px;
            width: 40px;
        }

        /* Avatar & Cells */
        .inq-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .inq-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .inq-fullname {
            font-weight: 700;
            color: #0f172a;
        }

        /* Pill Badges */
        .badge-pill-custom {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            display: inline-block;
        }

        .badge-client {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-contacted {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .inq-empty-row td {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .inq-empty-row i {
            font-size: 40px;
            display: block;
            margin-bottom: 12px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    
    <!-- Top Summary Metrics Cards -->
    <div class="inq-stats">
        <div class="inq-stat" style="border-left: 4px solid #64748b;">
            <i class="fas fa-newspaper" style="color: #64748b; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $totalCount ?></span>
                <span class="inq-stat-lbl">Total Leads</span>
            </div>
        </div>
        <div class="inq-stat" style="border-left: 4px solid #f59e0b;">
            <i class="fas fa-user-clock" style="color: #f59e0b; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $potentialCount ?></span>
                <span class="inq-stat-lbl">Potential Clients</span>
            </div>
        </div>
        <div class="inq-stat" style="border-left: 4px solid #16a34a;">
            <i class="fas fa-user-check" style="color: #16a34a; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $contactedCount ?></span>
                <span class="inq-stat-lbl">Contacted</span>
            </div>
        </div>
    </div>

    <!-- Filter, Search & Action Bar -->
    <div class="inq-toolbar">
        <input type="text" id="subSearch" class="inq-search" placeholder="Search by email address…">
        <select id="subStatusFilter" class="inq-filter-sel">
            <option value="">All Status</option>
            <option value="potential_client">Potential Client</option>
            <option value="contacted">Contacted</option>
        </select>
    </div>

    <!-- Modern Table Overhaul -->
    <div class="inq-table-wrap">
        <table class="inq-table">
            <thead>
                <tr>
                    <th class="inq-td-num">#</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Date Joined</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="subBody">
                <?php if (empty($subscribers)): ?>
                    <tr class="inq-empty-row">
                        <td colspan="5">
                            <i class="fas fa-users-slash"></i>
                            No potential client leads found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $idx => $row): 
                        $statusClass = ($row['status'] === 'contacted') ? 'badge-contacted' : 'badge-client';
                        $statusLabel = ($row['status'] === 'contacted') ? 'Contacted' : 'Potential Client';
                        $safeEmail = htmlspecialchars($row['email'], ENT_QUOTES);
                    ?>
                        <tr class="sub-row" data-email="<?= strtolower($safeEmail) ?>" data-status="<?= htmlspecialchars($row['status']) ?>">
                            <td class="inq-td-num"><?= $idx + 1 ?></td>
                            <td>
                                <div class="inq-name-cell">
                                    <div class="inq-avatar">
                                        <?= strtoupper(substr($row['email'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="inq-fullname"><?= $safeEmail ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-pill-custom <?= $statusClass ?>" id="status-badge-<?= $row['id'] ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted d-block"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                                <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-success btn-sm px-3" onclick="openEmailModal('<?= $safeEmail ?>')" style="border-radius: 6px;">
                                    <i class="fas fa-envelope-open-text me-1"></i> Send Update
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="modal-header text-white border-0 py-3" style="background-color: #0d5c3a !important;">
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
                        <button type="submit" class="btn text-white" id="sendBtn" style="background-color: #0d5c3a; border-color: #0d5c3a;">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    let emailModalInstance = null;

    function openEmailModal(email) {
        document.getElementById('recipientEmail').value = email;
        const modalEl = document.getElementById('emailModal');
        emailModalInstance = new bootstrap.Modal(modalEl);
        emailModalInstance.show();
    }

    // Client-side search and filtering
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('subSearch');
        const filterSelect = document.getElementById('subStatusFilter');
        const tableRows = document.querySelectorAll('.sub-row');

        function filterTable() {
            const query = searchInput.value.toLowerCase().trim();
            const selectedStatus = filterSelect.value.trim();

            tableRows.forEach(row => {
                const email = row.getAttribute('data-email') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesSearch = email.includes(query);
                const matchesStatus = !selectedStatus || status === selectedStatus;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (filterSelect) filterSelect.addEventListener('change', filterTable);
    });

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
