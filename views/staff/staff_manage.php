<?php
// views/staff/staff_manage.php - Secure Staff Management Panel
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

$current_user_id = intval($_SESSION['user_id']);

// Self-healing database schema migrations
try {
    // 1. Add status column to staff table if it does not exist
    $db->exec("ALTER TABLE `staff` ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'Active'");
    
    // 2. Create staff_audit_logs table if it does not exist
    $db->exec("CREATE TABLE IF NOT EXISTS `staff_audit_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `actor_id` INT NOT NULL,
        `actor_name` VARCHAR(150) NOT NULL,
        `action` VARCHAR(100) NOT NULL,
        `target_id` INT DEFAULT NULL,
        `target_name` VARCHAR(150) DEFAULT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `details` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
} catch (Exception $e) {
    // Suppress schema migration errors
}

// Generate CSRF Token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Security: Audit Logging Helper
function logSecurityEvent($db, $actorId, $actorName, $action, $targetId, $targetName, $details) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }
    try {
        $stmt = $db->prepare("INSERT INTO `staff_audit_logs` (actor_id, actor_name, action, target_id, target_name, ip_address, details) VALUES (:actor_id, :actor_name, :action, :target_id, :target_name, :ip_address, :details)");
        $stmt->execute([
            ':actor_id' => $actorId,
            ':actor_name' => $actorName,
            ':action' => $action,
            ':target_id' => $targetId,
            ':target_name' => $targetName,
            ':ip_address' => $ip,
            ':details' => $details
        ]);
    } catch (Exception $e) {
        // Suppress logging database failures
    }
}

// Security: Password Strength Validation Helper
function isStrongPassword($password) {
    // Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number, and one special character
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

// Handle AJAX Post Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // 1. Verify CSRF Token
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        echo json_encode(['success' => false, 'message' => 'CSRF Token validation failed. Action rejected.']);
        exit();
    }
    
    $action = $_POST['action'] ?? '';
    $actor_name = ($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '');

    // ACTION: Add Staff Account
    if ($action === 'add_staff') {
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'First Name, Last Name, Email, and Password are required.']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address format.']);
            exit();
        }
        
        if (!isStrongPassword($password)) {
            echo json_encode(['success' => false, 'message' => 'Password does not meet safety policies (Min 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 special char).']);
            exit();
        }
        
        try {
            // Check if email already exists
            $chk = $db->prepare("SELECT id FROM `staff` WHERE `email` = :email LIMIT 1");
            $chk->execute([':email' => $email]);
            if ($chk->fetch()) {
                echo json_encode(['success' => false, 'message' => 'The email address is already in use by another staff member.']);
                exit();
            }
            
            // Hash and Insert
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO `staff` (firstName, lastName, email, password, contact_number, status) VALUES (:first, :last, :email, :pass, :contact, 'Active')");
            $stmt->execute([
                ':first' => $firstName,
                ':last' => $lastName,
                ':email' => $email,
                ':pass' => $hashed,
                ':contact' => $contact_number
            ]);
            $new_id = $db->lastInsertId();
            
            // Log security event
            logSecurityEvent($db, $current_user_id, $actor_name, 'Create Staff', $new_id, "$firstName $lastName", "Created staff account with email: $email");
            
            echo json_encode(['success' => true, 'message' => 'Staff account created successfully.']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // ACTION: Edit Staff Details
    if ($action === 'edit_staff') {
        $id = intval($_POST['id'] ?? 0);
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        
        if ($id <= 0 || empty($firstName) || empty($lastName) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'All details are required.']);
            exit();
        }
        
        if ($id === $current_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot edit your own details from this management tab. Please use the Profile tab.']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address format.']);
            exit();
        }
        
        try {
            // Check if email already in use by others
            $chk = $db->prepare("SELECT id FROM `staff` WHERE `email` = :email AND id != :id LIMIT 1");
            $chk->execute([':email' => $email, ':id' => $id]);
            if ($chk->fetch()) {
                echo json_encode(['success' => false, 'message' => 'The email address is already in use by another staff member.']);
                exit();
            }
            
            // Get old info for details diff in logs
            $old_stmt = $db->prepare("SELECT firstName, lastName, email, contact_number FROM `staff` WHERE id = :id");
            $old_stmt->execute([':id' => $id]);
            $old = $old_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update details
            $stmt = $db->prepare("UPDATE `staff` SET firstName = :first, lastName = :last, email = :email, contact_number = :contact WHERE id = :id");
            $stmt->execute([
                ':first' => $firstName,
                ':last' => $lastName,
                ':email' => $email,
                ':contact' => $contact_number,
                ':id' => $id
            ]);
            
            // Diff details
            $diffs = [];
            if ($old['firstName'] !== $firstName) $diffs[] = "First Name (from '{$old['firstName']}' to '{$firstName}')";
            if ($old['lastName'] !== $lastName) $diffs[] = "Last Name (from '{$old['lastName']}' to '{$lastName}')";
            if ($old['email'] !== $email) $diffs[] = "Email (from '{$old['email']}' to '{$email}')";
            if ($old['contact_number'] !== $contact_number) $diffs[] = "Contact (from '{$old['contact_number']}' to '{$contact_number}')";
            
            $diff_str = count($diffs) > 0 ? "Modified fields: " . implode(', ', $diffs) : "No changes made.";
            
            logSecurityEvent($db, $current_user_id, $actor_name, 'Edit Staff Info', $id, "$firstName $lastName", $diff_str);
            
            echo json_encode(['success' => true, 'message' => 'Staff details updated successfully.']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // ACTION: Reset Password
    if ($action === 'reset_password') {
        $id = intval($_POST['id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($id <= 0 || empty($new_password)) {
            echo json_encode(['success' => false, 'message' => 'Password cannot be blank.']);
            exit();
        }
        
        if ($id === $current_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot change your own password from here. Please use the Profile security section.']);
            exit();
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit();
        }
        
        if (!isStrongPassword($new_password)) {
            echo json_encode(['success' => false, 'message' => 'Password does not meet strength rules (Min 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 special char).']);
            exit();
        }
        
        try {
            // Get Target Name
            $t_stmt = $db->prepare("SELECT firstName, lastName FROM `staff` WHERE id = :id");
            $t_stmt->execute([':id' => $id]);
            $target = $t_stmt->fetch(PDO::FETCH_ASSOC);
            $target_name = $target ? ($target['firstName'] . ' ' . $target['lastName']) : 'Unknown';
            
            // Hash and update
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE `staff` SET password = :pass WHERE id = :id");
            $stmt->execute([':pass' => $hashed, ':id' => $id]);
            
            logSecurityEvent($db, $current_user_id, $actor_name, 'Reset Password', $id, $target_name, "Forced password reset requested by acting staff member.");
            
            echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // ACTION: Toggle Status (Deactivate / Activate)
    if ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $new_status = trim($_POST['status'] ?? '');
        
        if ($id <= 0 || !in_array($new_status, ['Active', 'Inactive'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            exit();
        }
        
        if ($id === $current_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account! Lockout prevented.']);
            exit();
        }
        
        try {
            // Get Target Name
            $t_stmt = $db->prepare("SELECT firstName, lastName FROM `staff` WHERE id = :id");
            $t_stmt->execute([':id' => $id]);
            $target = $t_stmt->fetch(PDO::FETCH_ASSOC);
            $target_name = $target ? ($target['firstName'] . ' ' . $target['lastName']) : 'Unknown';
            
            // Update
            $stmt = $db->prepare("UPDATE `staff` SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $new_status, ':id' => $id]);
            
            $logAction = ($new_status === 'Active') ? 'Activate Staff' : 'Deactivate Staff';
            logSecurityEvent($db, $current_user_id, $actor_name, $logAction, $id, $target_name, "Account status toggled to: " . $new_status);
            
            echo json_encode(['success' => true, 'message' => 'Staff status updated to ' . $new_status . '.']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // ACTION: Delete Staff Account
    if ($action === 'delete_staff') {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid staff identifier.']);
            exit();
        }
        
        if ($id === $current_user_id) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account! Lockout prevented.']);
            exit();
        }
        
        try {
            // Get Target Details before deleting
            $t_stmt = $db->prepare("SELECT firstName, lastName, email FROM `staff` WHERE id = :id");
            $t_stmt->execute([':id' => $id]);
            $target = $t_stmt->fetch(PDO::FETCH_ASSOC);
            $target_name = $target ? ($target['firstName'] . ' ' . $target['lastName']) : 'Unknown';
            $email = $target ? $target['email'] : 'Unknown';
            
            // Delete
            $stmt = $db->prepare("DELETE FROM `staff` WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            logSecurityEvent($db, $current_user_id, $actor_name, 'Delete Staff', $id, $target_name, "Permanently deleted staff member account (Email: $email)");
            
            echo json_encode(['success' => true, 'message' => 'Staff account permanently deleted.']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action request.']);
    exit();
}

// Retrieve Staff List
try {
    $stmt = $db->query("SELECT id, firstName, lastName, email, contact_number, profile_picture, created_at, status FROM `staff` ORDER BY id DESC");
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $staffList = [];
}

// Retrieve Audit Logs (Latest 15)
try {
    $log_stmt = $db->query("SELECT * FROM `staff_audit_logs` ORDER BY id DESC LIMIT 15");
    $auditLogs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $auditLogs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Staff Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.04);
            border: none;
            margin-bottom: 25px;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #0a5c3d, #0d7049);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px 25px;
        }
        .table th {
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .badge-active {
            background-color: #dcfce7;
            color: #16a34a;
            font-weight: 600;
        }
        .badge-inactive {
            background-color: #fee2e2;
            color: #dc2626;
            font-weight: 600;
        }
        .profile-img-pill {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 50%;
            background-color: #e5e7eb;
        }
        .avatar-placeholder {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #0a5c3d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .audit-log-item {
            font-size: 0.8rem;
            border-left: 3px solid #0d7049;
            padding-left: 12px;
            margin-bottom: 12px;
            position: relative;
        }
        .audit-log-item.deactivate {
            border-left-color: #dc2626;
        }
        .audit-log-item.delete {
            border-left-color: #991b1b;
        }
        .audit-log-item.reset {
            border-left-color: #d97706;
        }
        /* Live Password strength meter styles */
        .strength-bar {
            height: 6px;
            border-radius: 3px;
            transition: all 0.3s ease;
            width: 0%;
        }
        .strength-weak { background-color: #dc2626; width: 33%; }
        .strength-medium { background-color: #d97706; width: 66%; }
        .strength-strong { background-color: #16a34a; width: 100%; }
        .req-item {
            font-size: 0.75rem;
            color: #dc2626;
            transition: color 0.2s ease;
        }
        .req-item.valid {
            color: #16a34a;
        }
        .req-item i {
            margin-right: 4px;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Staff List (Left Panel) -->
        <div class="col-xl-8 col-lg-7">
            <div class="card dashboard-card">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2"></i> Active Staff Roster</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white fs-7" style="color: #0a5c3d;" id="totalStaffBadge"><?= count($staffList) ?> Accounts</span>
                        <button class="btn btn-sm btn-light fw-bold" style="color: #0a5c3d; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="fas fa-user-plus me-1"></i> Create Staff Account
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Search Input -->
                    <div class="input-group mb-3" style="max-width: 350px;">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="staffSearchInput" class="form-control bg-light border-start-0" placeholder="Search by name, email..." onkeyup="searchStaff()">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="staffTable">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Email Address</th>
                                    <th>Contact Info</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($staffList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-user-slash fs-1 mb-3"></i>
                                            <p class="mb-0">No staff members found.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($staffList as $staff): 
                                        $fullName = htmlspecialchars($staff['firstName'] . ' ' . $staff['lastName']);
                                        $initials = strtoupper(substr($staff['firstName'], 0, 1) . substr($staff['lastName'], 0, 1));
                                        $isSelf = ($staff['id'] === $current_user_id);
                                    ?>
                                        <tr data-search-str="<?= strtolower($fullName . ' ' . $staff['email']) ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if(!empty($staff['profile_picture'])): ?>
                                                        <img src="../../uploads/profile_pics/<?= htmlspecialchars($staff['profile_picture']) ?>" class="profile-img-pill" alt="Avatar">
                                                    <?php else: ?>
                                                        <div class="avatar-placeholder"><?= $initials ?></div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong class="text-dark d-block">
                                                            <?= $fullName ?>
                                                            <?php if($isSelf): ?>
                                                                <span class="badge bg-secondary ms-1 fs-8">You</span>
                                                            <?php endif; ?>
                                                        </strong>
                                                        <span class="text-muted small">ID: #<?= $staff['id'] ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($staff['email']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($staff['email']) ?></a>
                                            </td>
                                            <td>
                                                <i class="fas fa-phone-alt text-muted me-1" style="font-size: 0.75rem;"></i> <?= htmlspecialchars($staff['contact_number'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= ($staff['status'] === 'Active') ? 'badge-active' : 'badge-inactive' ?> px-2.5 py-1.5 rounded-pill">
                                                    <?= htmlspecialchars($staff['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= date('M d, Y', strtotime($staff['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    <?php if(!$isSelf): ?>
                                                        <!-- Edit Button -->
                                                        <button class="btn btn-sm btn-outline-primary" title="Edit Profile" onclick="openEditModal(<?= htmlspecialchars(json_encode($staff)) ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <!-- Password Key -->
                                                        <button class="btn btn-sm btn-outline-warning" title="Reset Password" onclick="openResetModal(<?= $staff['id'] ?>, '<?= $fullName ?>')">
                                                            <i class="fas fa-key"></i>
                                                        </button>
                                                        <!-- Toggle Status -->
                                                        <?php if($staff['status'] === 'Active'): ?>
                                                            <button class="btn btn-sm btn-outline-danger" title="Deactivate Account" onclick="toggleStatus(<?= $staff['id'] ?>, 'Inactive')">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-success" title="Activate Account" onclick="toggleStatus(<?= $staff['id'] ?>, 'Active')">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <!-- Delete Button -->
                                                        <button class="btn btn-sm btn-outline-danger" title="Delete Account" onclick="openDeleteModal(<?= $staff['id'] ?>, '<?= $fullName ?>')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small italic">Manage via Profile</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Activity Logs (Right Panel) -->
        <div class="col-xl-4 col-lg-5">
            <div class="card dashboard-card">
                <div class="card-header card-header-custom">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-shield-halved me-2"></i> Security Audit Trail</h5>
                </div>
                <div class="card-body p-4" style="max-height: 600px; overflow-y: auto;">
                    <p class="text-muted small mb-3">Live ledger recording administrative account activities and modifications.</p>
                    
                    <div id="auditLogContainer">
                        <?php if(empty($auditLogs)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-receipt mb-2 fs-4"></i>
                                <p class="small mb-0">No logs generated yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($auditLogs as $log): 
                                $class = '';
                                if (strpos($log['action'], 'Deactivate') !== false) $class = 'deactivate';
                                if (strpos($log['action'], 'Delete') !== false) $class = 'delete';
                                if (strpos($log['action'], 'Password') !== false) $class = 'reset';
                            ?>
                                <div class="audit-log-item <?= $class ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark small"><?= htmlspecialchars($log['action']) ?></strong>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= date('M d, h:i A', strtotime($log['created_at'])) ?></small>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        Actor: <strong><?= htmlspecialchars($log['actor_name']) ?></strong><br>
                                        Target: <strong><?= htmlspecialchars($log['target_name'] ?? 'N/A') ?></strong> (ID: #<?= $log['target_id'] ?? 'N/A' ?>)<br>
                                        IP: <span class="badge bg-light text-dark font-monospace"><?= htmlspecialchars($log['ip_address']) ?></span>
                                    </div>
                                    <?php if(!empty($log['details'])): ?>
                                        <div class="bg-light p-1.5 rounded mt-1 border-start border-3 border-success text-secondary" style="font-size: 0.72rem; word-break: break-all;">
                                            <?= htmlspecialchars($log['details']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: ADD STAFF -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="addStaffForm" onsubmit="submitAddStaff(event)">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="add_staff">
            <div class="modal-header" style="background-color: #0a5c3d; color: white;">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Create Staff Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="form-label small fw-semibold">First Name</label>
                        <input type="text" name="firstName" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input type="text" name="lastName" class="form-control" placeholder="Last Name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="staff@solarpower.com.ph" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" placeholder="e.g. 09171234567">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Security Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="add_pwd" class="form-control" placeholder="Enter strong password" onkeyup="checkPasswordStrength('add_pwd', 'add_pwd_strength', 'add_pwd_req')" required>
                        <button type="button" class="input-group-text" onclick="togglePasswordVisibility('add_pwd')"><i class="fas fa-eye" id="add_pwd_eye"></i></button>
                    </div>
                    <!-- Password strength visualizer -->
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div id="add_pwd_strength" class="progress-bar strength-bar" role="progressbar"></div>
                        </div>
                    </div>
                    <!-- Password requirements checklist -->
                    <div id="add_pwd_req" class="mt-2 row">
                        <div class="col-6 req-item len" id="add_req_len"><i class="fas fa-circle-xmark"></i> 8+ characters</div>
                        <div class="col-6 req-item cap" id="add_req_cap"><i class="fas fa-circle-xmark"></i> Uppercase (A-Z)</div>
                        <div class="col-6 req-item low" id="add_req_low"><i class="fas fa-circle-xmark"></i> Lowercase (a-z)</div>
                        <div class="col-6 req-item num" id="add_req_num"><i class="fas fa-circle-xmark"></i> Number (0-9)</div>
                        <div class="col-12 req-item spc" id="add_req_spc"><i class="fas fa-circle-xmark"></i> Special char (@$!%*?&)</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn text-white" style="background-color: #0a5c3d;">Save Account</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT STAFF DETAILS -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="editStaffForm" onsubmit="submitEditStaff(event)">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="edit_staff">
            <input type="hidden" name="id" id="edit_staff_id">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> Edit Staff Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="form-label small fw-semibold">First Name</label>
                        <input type="text" name="firstName" id="edit_first" class="form-control" required>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input type="text" name="lastName" id="edit_last" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Contact Number</label>
                    <input type="text" name="contact_number" id="edit_contact" class="form-control">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: RESET PASSWORD -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="resetPasswordForm" onsubmit="submitResetPassword(event)">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="id" id="reset_staff_id">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-key me-2"></i> Reset Staff Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-2">
                    <span class="small text-muted">You are changing password for:</span><br>
                    <strong class="fs-5 text-dark" id="reset_staff_name"></strong>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password" name="new_password" id="reset_pwd" class="form-control" placeholder="Enter new strong password" onkeyup="checkPasswordStrength('reset_pwd', 'reset_pwd_strength', 'reset_pwd_req')" required>
                        <button type="button" class="input-group-text" onclick="togglePasswordVisibility('reset_pwd')"><i class="fas fa-eye" id="reset_pwd_eye"></i></button>
                    </div>
                    <!-- Password strength visualizer -->
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div id="reset_pwd_strength" class="progress-bar strength-bar" role="progressbar"></div>
                        </div>
                    </div>
                    <!-- Password requirements checklist -->
                    <div id="reset_pwd_req" class="mt-2 row">
                        <div class="col-6 req-item len" id="reset_req_len"><i class="fas fa-circle-xmark"></i> 8+ characters</div>
                        <div class="col-6 req-item cap" id="reset_req_cap"><i class="fas fa-circle-xmark"></i> Uppercase (A-Z)</div>
                        <div class="col-6 req-item low" id="reset_req_low"><i class="fas fa-circle-xmark"></i> Lowercase (a-z)</div>
                        <div class="col-6 req-item num" id="reset_req_num"><i class="fas fa-circle-xmark"></i> Number (0-9)</div>
                        <div class="col-12 req-item spc" id="reset_req_spc"><i class="fas fa-circle-xmark"></i> Special char (@$!%*?&)</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Retype password" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning fw-semibold">Force Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: DELETE CONFIRMATION -->
<div class="modal fade" id="deleteStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="deleteStaffForm" onsubmit="submitDeleteStaff(event)">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="delete_staff">
            <input type="hidden" name="id" id="delete_staff_id">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Permanent Account Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-trash-alt text-danger fs-1 mb-3"></i>
                <h5 class="fw-bold">Are you absolutely sure?</h5>
                <p class="text-muted small">
                    This action will permanently delete <strong id="delete_staff_name" class="text-dark"></strong>'s staff account. This is irreversible, but their historical actions in the audit logs and orders will remain tracked.
                </p>
                <div class="alert alert-danger small p-2 mb-0">
                    <i class="fas fa-info-circle"></i> Suspension (deactivation) is recommended over deletion to maintain complete trace integrity.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<script>
    // Live Client-Side Search
    function searchStaff() {
        const query = document.getElementById('staffSearchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#staffTable tbody tr');
        let count = 0;
        
        rows.forEach(row => {
            const searchStr = row.getAttribute('data-search-str');
            if (searchStr) {
                if (searchStr.includes(query)) {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            }
        });
        
        document.getElementById('totalStaffBadge').textContent = `${count} Matches`;
    }

    // Toggle Password Input Visibility
    function togglePasswordVisibility(fieldId) {
        const input = document.getElementById(fieldId);
        const eye = document.getElementById(fieldId + '_eye');
        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.remove('fa-eye');
            eye.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            eye.classList.remove('fa-eye-slash');
            eye.classList.add('fa-eye');
        }
    }

    // Dynamic Live Password Strength Checker
    function checkPasswordStrength(fieldId, barId, reqContainerId) {
        const password = document.getElementById(fieldId).value;
        const bar = document.getElementById(barId);
        
        const hasLength = password.length >= 8;
        const hasCaps = /[A-Z]/.test(password);
        const hasLows = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[@$!%*?&]/.test(password);
        
        // Update Requirements UI
        updateReqClass(reqContainerId, '.len', hasLength);
        updateReqClass(reqContainerId, '.cap', hasCaps);
        updateReqClass(reqContainerId, '.low', hasLows);
        updateReqClass(reqContainerId, '.num', hasNumber);
        updateReqClass(reqContainerId, '.spc', hasSpecial);
        
        // Count fulfilled checks
        let points = 0;
        if (hasLength) points++;
        if (hasCaps) points++;
        if (hasLows) points++;
        if (hasNumber) points++;
        if (hasSpecial) points++;
        
        // Reset strength UI
        bar.className = 'progress-bar strength-bar';
        
        if (points === 0) {
            bar.style.width = '0%';
        } else if (points <= 2) {
            bar.classList.add('strength-weak');
        } else if (points <= 4) {
            bar.classList.add('strength-medium');
        } else {
            bar.classList.add('strength-strong');
        }
    }

    function updateReqClass(containerId, selector, isValid) {
        const el = document.querySelector('#' + containerId + ' ' + selector);
        if (el) {
            const icon = el.querySelector('i');
            if (isValid) {
                el.classList.add('valid');
                icon.className = 'fas fa-circle-check';
            } else {
                el.classList.remove('valid');
                icon.className = 'fas fa-circle-xmark';
            }
        }
    }

    // API ACTION: Add Staff Account
    async function submitAddStaff(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        // Validate password matches policy in frontend too
        const pwd = form.password.value;
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passRegex.test(pwd)) {
            alert('Password does not fulfill security policies. Please enter a stronger password.');
            return;
        }

        try {
            const response = await fetch('staff_manage.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Failed to connect to the server.');
        }
    }

    // Modal Control: Edit Staff Details
    function openEditModal(staff) {
        document.getElementById('edit_staff_id').value = staff.id;
        document.getElementById('edit_first').value = staff.firstName;
        document.getElementById('edit_last').value = staff.lastName;
        document.getElementById('edit_email').value = staff.email;
        document.getElementById('edit_contact').value = staff.contact_number || '';
        
        const modal = new bootstrap.Modal(document.getElementById('editStaffModal'));
        modal.show();
    }

    // API ACTION: Edit Staff Details
    async function submitEditStaff(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('staff_manage.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Failed to connect to the server.');
        }
    }

    // Modal Control: Reset Password
    function openResetModal(id, fullName) {
        document.getElementById('reset_staff_id').value = id;
        document.getElementById('reset_staff_name').textContent = fullName;
        document.getElementById('reset_pwd').value = '';
        
        const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        modal.show();
    }

    // API ACTION: Reset Password
    async function submitResetPassword(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        if (form.new_password.value !== form.confirm_password.value) {
            alert('Passwords do not match.');
            return;
        }
        
        const pwd = form.new_password.value;
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passRegex.test(pwd)) {
            alert('Password does not meet safety policies.');
            return;
        }

        try {
            const response = await fetch('staff_manage.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Failed to connect to the server.');
        }
    }

    // API ACTION: Toggle Account Status (Suspend / Reactivate)
    async function toggleStatus(id, newStatus) {
        const msg = `Are you sure you want to ${newStatus === 'Inactive' ? 'deactivate (suspend)' : 'reactivate'} this staff user account?`;
        if (!confirm(msg)) return;
        
        const formData = new FormData();
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
        formData.append('action', 'toggle_status');
        formData.append('id', id);
        formData.append('status', newStatus);
        
        try {
            const response = await fetch('staff_manage.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error connecting to the server.');
        }
    }

    // Modal Control: Delete Staff Account
    function openDeleteModal(id, fullName) {
        document.getElementById('delete_staff_id').value = id;
        document.getElementById('delete_staff_name').textContent = fullName;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteStaffModal'));
        modal.show();
    }

    // API ACTION: Delete Staff Account
    async function submitDeleteStaff(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('staff_manage.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Failed to execute account deletion.');
        }
    }
</script>
</body>
</html>
