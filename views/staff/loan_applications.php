<?php
// views/staff/loan_applications.php - Staff dashboard section for Pre-App Loan Submissions
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

// Ensure the table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `loan_applications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `full_name` VARCHAR(255) NOT NULL,
        `email_address` VARCHAR(255) NOT NULL,
        `contact_number` VARCHAR(50) NOT NULL,
        `monthly_bill` DECIMAL(10, 2) NOT NULL,
        `meralco_bill_path` VARCHAR(255) NOT NULL,
        `land_title_path` VARCHAR(255) NOT NULL,
        `membership_proof_path` VARCHAR(255) NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    // Fail silently or handle
}

// Handle AJAX Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    $allowed_statuses = ['Pending', 'Under Review', 'Approved', 'Rejected'];
    if ($id > 0 && in_array($status, $allowed_statuses)) {
        try {
            $stmt = $db->prepare("UPDATE `loan_applications` SET `status` = :status WHERE `id` = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    }
    exit();
}

// Fetch all loan applications
try {
    $stmt = $db->query("SELECT * FROM `loan_applications` ORDER BY `created_at` DESC");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $applications = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Loan Applications</title>
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
            background: linear-gradient(135deg, #0d5c3a, #08492d);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px 25px;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
            font-weight: 600;
        }
        .badge-review {
            background-color: #e0f2fe;
            color: #0284c7;
            font-weight: 600;
        }
        .badge-approved {
            background-color: #dcfce7;
            color: #16a34a;
            font-weight: 600;
        }
        .badge-rejected {
            background-color: #fee2e2;
            color: #dc2626;
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
        .status-select {
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 4px 8px;
            width: auto;
            display: inline-block;
        }
        .btn-doc {
            padding: 4px 10px;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="card dashboard-card">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2"></i> Pre-App Loan Applications</h5>
            <span class="badge bg-white fs-6" style="color: #0d5c3a;"><?= count($applications) ?> Applications Total</span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($applications)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open text-muted mb-3" style="font-size: 48px;"></i>
                    <p class="text-muted mb-0">No loan applications received yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date Submitted</th>
                                <th>Client Name</th>
                                <th>Contact & Email</th>
                                <th>Monthly Bill</th>
                                <th>Requirements Documents</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $row): 
                                $statusClass = 'badge-pending';
                                if ($row['status'] === 'Under Review') $statusClass = 'badge-review';
                                if ($row['status'] === 'Approved') $statusClass = 'badge-approved';
                                if ($row['status'] === 'Rejected') $statusClass = 'badge-rejected';
                            ?>
                                <tr>
                                    <td>
                                        <small class="text-muted d-block"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><?= htmlspecialchars($row['full_name']) ?></strong>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($row['contact_number']) ?></div>
                                        <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><a href="mailto:<?= htmlspecialchars($row['email_address']) ?>"><?= htmlspecialchars($row['email_address']) ?></a></div>
                                    </td>
                                    <td class="fw-bold text-success">
                                        ₱<?= number_format($row['monthly_bill'], 2) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <a href="../../<?= htmlspecialchars($row['meralco_bill_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-doc">
                                                <i class="fas fa-file-invoice-dollar"></i> Meralco Bill
                                            </a>
                                            <a href="../../<?= htmlspecialchars($row['land_title_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info btn-doc">
                                                <i class="fas fa-file-signature"></i> Land Title (TCT)
                                            </a>
                                            <a href="../../<?= htmlspecialchars($row['membership_proof_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary btn-doc">
                                                <i class="fas fa-id-card"></i> Member Proof
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?> px-3 py-2 rounded-pill fs-7" id="status-badge-<?= $row['id'] ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <select class="form-select form-select-sm status-select" onchange="updateApplicationStatus(<?= $row['id'] ?>, this.value)">
                                                <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Under Review" <?= $row['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                                                <option value="Approved" <?= $row['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                                <option value="Rejected" <?= $row['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                            </select>
                                        </div>
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

<!-- Bootstrap Bundle JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    async function updateApplicationStatus(id, newStatus) {
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('status', newStatus);

        try {
            const response = await fetch('loan_applications.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                const badge = document.getElementById('status-badge-' + id);
                badge.textContent = newStatus;
                badge.className = 'badge px-3 py-2 rounded-pill fs-7';
                
                if (newStatus === 'Pending') badge.classList.add('badge-pending');
                else if (newStatus === 'Under Review') badge.classList.add('badge-review');
                else if (newStatus === 'Approved') badge.classList.add('badge-approved');
                else if (newStatus === 'Rejected') badge.classList.add('badge-rejected');
            } else {
                alert('Error updating status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating status:', error);
            alert('Failed to update status.');
        }
    }
</script>
</body>
</html>
