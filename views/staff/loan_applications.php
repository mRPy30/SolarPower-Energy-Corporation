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
    // Fail silently
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

// Statistics count helper
$totalCount = count($applications);
$pendingCount = count(array_filter($applications, fn($a) => $a['status'] === 'Pending'));
$reviewCount = count(array_filter($applications, fn($a) => $a['status'] === 'Under Review'));
$approvedCount = count(array_filter($applications, fn($a) => $a['status'] === 'Approved'));
$rejectedCount = count(array_filter($applications, fn($a) => $a['status'] === 'Rejected'));
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

        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-review {
            background-color: #e0f2fe;
            color: #0284c7;
        }

        .badge-approved {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .badge-rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .status-select {
            font-size: 12px;
            border-radius: 6px;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            outline: none;
            background-color: #fff;
            cursor: pointer;
        }

        .status-select:focus {
            border-color: #f59e0b;
        }

        .btn-doc {
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
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
            <i class="fas fa-file-signature" style="color: #64748b; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $totalCount ?></span>
                <span class="inq-stat-lbl">Total Applications</span>
            </div>
        </div>
        <div class="inq-stat" style="border-left: 4px solid #f59e0b;">
            <i class="fas fa-clock" style="color: #f59e0b; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $pendingCount ?></span>
                <span class="inq-stat-lbl">Pending</span>
            </div>
        </div>
        <div class="inq-stat" style="border-left: 4px solid #0284c7;">
            <i class="fas fa-file-invoice" style="color: #0284c7; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $reviewCount ?></span>
                <span class="inq-stat-lbl">Under Review</span>
            </div>
        </div>
        <div class="inq-stat" style="border-left: 4px solid #16a34a;">
            <i class="fas fa-check-circle" style="color: #16a34a; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $approvedCount ?></span>
                <span class="inq-stat-lbl">Approved</span>
            </div>
        </div>
        <div class="inq-stat" style="border-left: 4px solid #dc2626;">
            <i class="fas fa-times-circle" style="color: #dc2626; font-size: 22px;"></i>
            <div>
                <span class="inq-stat-num"><?= $rejectedCount ?></span>
                <span class="inq-stat-lbl">Rejected</span>
            </div>
        </div>
    </div>

    <!-- Filter, Search & Action Bar -->
    <div class="inq-toolbar">
        <input type="text" id="loanSearch" class="inq-search" placeholder="Search by name, email or phone…">
        <select id="loanStatusFilter" class="inq-filter-sel">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Under Review">Under Review</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
        </select>
    </div>

    <!-- Modern Table Overhaul -->
    <div class="inq-table-wrap">
        <table class="inq-table">
            <thead>
                <tr>
                    <th class="inq-td-num">#</th>
                    <th>Client Name</th>
                    <th>Contact & Email</th>
                    <th>Monthly Bill</th>
                    <th>Requirement Documents</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody id="loanBody">
                <?php if (empty($applications)): ?>
                    <tr class="inq-empty-row">
                        <td colspan="7">
                            <i class="fas fa-folder-open"></i>
                            No loan applications received yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $idx => $row): 
                        $statusClass = 'badge-pending';
                        if ($row['status'] === 'Under Review') $statusClass = 'badge-review';
                        if ($row['status'] === 'Approved') $statusClass = 'badge-approved';
                        if ($row['status'] === 'Rejected') $statusClass = 'badge-rejected';
                        
                        $safeName = htmlspecialchars($row['full_name'], ENT_QUOTES);
                        $safeEmail = htmlspecialchars($row['email_address'], ENT_QUOTES);
                        $safePhone = htmlspecialchars($row['contact_number'], ENT_QUOTES);
                    ?>
                        <tr class="loan-row" data-name="<?= strtolower($safeName) ?>" data-email="<?= strtolower($safeEmail) ?>" data-phone="<?= strtolower($safePhone) ?>" data-status="<?= htmlspecialchars($row['status']) ?>">
                            <td class="inq-td-num"><?= $idx + 1 ?></td>
                            <td>
                                <div class="inq-name-cell">
                                    <div class="inq-avatar">
                                        <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="inq-fullname"><?= $safeName ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><i class="fas fa-phone me-1 text-muted"></i><?= $safePhone ?></div>
                                <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><a href="mailto:<?= $safeEmail ?>" class="text-decoration-none text-success"><?= $safeEmail ?></a></div>
                            </td>
                            <td class="fw-bold text-success">
                                ₱<?= number_format($row['monthly_bill'], 2) ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1" style="max-width: 200px;">
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
                                <span class="badge-pill-custom <?= $statusClass ?>" id="status-badge-<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <select class="form-select form-select-sm status-select" onchange="updateApplicationStatus(<?= $row['id'] ?>, this.value)" style="width: auto; display: inline-block;">
                                    <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Under Review" <?= $row['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                                    <option value="Approved" <?= $row['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                    <option value="Rejected" <?= $row['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    // Client-side search and filtering
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('loanSearch');
        const filterSelect = document.getElementById('loanStatusFilter');
        const tableRows = document.querySelectorAll('.loan-row');

        function filterTable() {
            const query = searchInput.value.toLowerCase().trim();
            const selectedStatus = filterSelect.value.trim();

            tableRows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const phone = row.getAttribute('data-phone') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesSearch = name.includes(query) || email.includes(query) || phone.includes(query);
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
                badge.className = 'badge-pill-custom';
                
                if (newStatus === 'Pending') badge.classList.add('badge-pending');
                else if (newStatus === 'Under Review') badge.classList.add('badge-review');
                else if (newStatus === 'Approved') badge.classList.add('badge-approved');
                else if (newStatus === 'Rejected') badge.classList.add('badge-rejected');

                // Update row attribute for instant filter updates
                const row = badge.closest('.loan-row');
                if (row) {
                    row.setAttribute('data-status', newStatus);
                }
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
