<?php
// views/staff/estimates.php - Staff dashboard section for Free Estimate Requests
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

// Handle AJAX Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    $allowed_statuses = ['Pending', 'Reviewed', 'Scheduled'];
    if ($id > 0 && in_array($status, $allowed_statuses)) {
        try {
            $stmt = $db->prepare("UPDATE `estimates` SET `status` = :status WHERE `id` = :id");
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

// Fetch all estimates, latest first
try {
    $stmt = $db->query("SELECT * FROM `estimates` ORDER BY `created_at` DESC");
    $estimates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $estimates = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Free Estimate Requests</title>
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
            background: linear-gradient(135deg, #0f766e, #0d9488);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px 25px;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
            font-weight: 600;
        }
        .badge-reviewed {
            background-color: #e0f2fe;
            color: #0284c7;
            font-weight: 600;
        }
        .badge-scheduled {
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
        .status-select {
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 4px 8px;
            width: auto;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="card dashboard-card">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i> Free Estimate Requests</h5>
            <span class="badge bg-white fs-6" style="color: #0f766e;"><?= count($estimates) ?> Requests Total</span>
        </div>
        <div class="card-body p-4">
            <?php if (empty($estimates)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 48px;"></i>
                    <p class="text-muted mb-0">No solar estimate requests found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date Submitted</th>
                                <th>Customer Name</th>
                                <th>Contact & Email</th>
                                <th>Property / Roof</th>
                                <th>Monthly Bill</th>
                                <th>Pref. Assessment Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estimates as $row): 
                                $statusClass = 'badge-pending';
                                if ($row['status'] === 'Reviewed') $statusClass = 'badge-reviewed';
                                if ($row['status'] === 'Scheduled') $statusClass = 'badge-scheduled';
                            ?>
                                <tr>
                                    <td>
                                        <small class="text-muted d-block"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><?= htmlspecialchars($row['full_name']) ?></strong>
                                        <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($row['complete_address']) ?></div>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($row['contact_number']) ?></div>
                                        <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><a href="mailto:<?= htmlspecialchars($row['email_address']) ?>"><?= htmlspecialchars($row['email_address']) ?></a></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($row['property_type']) ?></span>
                                        <div class="small text-muted mt-1"><?= htmlspecialchars($row['roof_type']) ?></div>
                                    </td>
                                    <td class="fw-bold text-success">
                                        ₱<?= number_format($row['monthly_bill'], 2) ?>
                                    </td>
                                    <td class="fw-semibold">
                                        <?= date('M d, Y', strtotime($row['inspection_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?> px-3 py-2 rounded-pill fs-7" id="status-badge-<?= $row['id'] ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <select class="form-select form-select-sm status-select" onchange="updateEstimateStatus(<?= $row['id'] ?>, this.value)">
                                                <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Reviewed" <?= $row['status'] === 'Reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                                <option value="Scheduled" <?= $row['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                            </select>
                                            <?php if (!empty($row['additional_notes'])): ?>
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="popover" data-bs-trigger="focus" title="Additional Notes" data-bs-content="<?= htmlspecialchars($row['additional_notes']) ?>">
                                                    <i class="fas fa-sticky-note"></i>
                                                </button>
                                            <?php endif; ?>
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
    // Initialize Popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    async function updateEstimateStatus(id, newStatus) {
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('status', newStatus);

        try {
            const response = await fetch('estimates.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                const badge = document.getElementById('status-badge-' + id);
                badge.textContent = newStatus;
                badge.className = 'badge px-3 py-2 rounded-pill fs-7';
                
                if (newStatus === 'Pending') badge.classList.add('badge-pending');
                else if (newStatus === 'Reviewed') badge.classList.add('badge-reviewed');
                else if (newStatus === 'Scheduled') badge.classList.add('badge-scheduled');
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
