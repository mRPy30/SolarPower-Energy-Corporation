<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
// Database connection details
include "../../config/dbconn.php";

// Get user data from session
$user_id = $_SESSION['user_id'];
$firstName = $_SESSION['firstName'] ?? 'User';
$lastName = $_SESSION['lastName'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Database Connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

function get_dashboard_analytics($conn)
{
    $data = [];

    // TOTAL REVENUE
    $sql = "
        SELECT IFNULL(SUM(total_amount), 0) AS revenue 
        FROM orders 
        WHERE order_status IN ('installed', 'completed', 'approved')
    ";
    $data['revenue'] = $conn->query($sql)->fetch_assoc()['revenue'];

    // MONTHLY SALES
    $sql = "
        SELECT IFNULL(SUM(total_amount), 0) AS monthly_sales 
        FROM orders 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
        AND order_status IN ('installed', 'completed', 'approved')
    ";
    $data['monthly_sales'] = $conn->query($sql)->fetch_assoc()['monthly_sales'];

    // LOW STOCK (1–10)
    $sql = "
        SELECT COUNT(*) AS low_stock
        FROM product
        WHERE stockQuantity BETWEEN 1 AND 10
    ";
    $data['low_stock'] = $conn->query($sql)->fetch_assoc()['low_stock'];

    // OUT OF STOCK (0)
    $sql = "
        SELECT COUNT(*) AS out_of_stock
        FROM product
        WHERE stockQuantity = 0
    ";
    $data['out_of_stock'] = $conn->query($sql)->fetch_assoc()['out_of_stock'];

    return $data;
}

function get_sales_by_city($conn)
{
    // This groups your orders by the address to see where most sales come from
    $sql = "SELECT customer_address, COUNT(*) as total_orders, SUM(total_amount) as revenue 
            FROM orders 
            GROUP BY customer_address 
            ORDER BY revenue DESC LIMIT 5";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}
$city_data = get_sales_by_city($conn);

function get_solar_metrics($conn)
{
    $metrics = [
        'panels_sold' => 0,
        'installations' => 0,
        'kw_installed' => 0
    ];

    // TOTAL PANELS SOLD
    $sql = "
        SELECT IFNULL(SUM(oi.quantity), 0) AS total
        FROM order_items oi
        JOIN product p ON oi.product_id = p.id
        WHERE p.category = 'Panel'
    ";
    $metrics['panels_sold'] = $conn->query($sql)->fetch_assoc()['total'];

    // SYSTEM INSTALLATIONS (installed orders)
    $metrics['installations'] = $conn->query("
        SELECT COUNT(*) AS total 
        FROM orders 
        WHERE order_status = 'installed'
    ")->fetch_assoc()['total'];

    // ESTIMATED kW INSTALLED (assume 0.6kW per panel)
    $metrics['kw_installed'] = round($metrics['panels_sold'] * 0.6, 2);

    return $metrics;
}

function get_order_status($conn)
{
    $statuses = ['pending', 'confirmed', 'preparing', 'ready to ship', 'In Transit', 'out for delivery', 'delivered', 'cancelled'];
    $data = [];

    foreach ($statuses as $status) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total 
            FROM orders 
            WHERE order_status = ?
        ");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $data[$status] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }

    return $data;
}

function get_best_seller($conn)
{
    $sql = "
        SELECT 
            oi.product_name,
            SUM(oi.quantity) AS total_qty,
            COUNT(DISTINCT oi.order_id) AS order_frequency
        FROM order_items oi
        GROUP BY oi.product_id
        ORDER BY total_qty DESC
        LIMIT 1
    ";

    $result = $conn->query($sql);
    return $result->num_rows ? $result->fetch_assoc() : null;
}

$stats = [
    'clients'   => $conn->query("SELECT COUNT(*) FROM client")->fetch_row()[0],
    'products'  => $conn->query("SELECT COUNT(*) FROM product")->fetch_row()[0],
    'orders'    => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
    'suppliers' => $conn->query("SELECT COUNT(*) FROM supplier")->fetch_row()[0]
];


// 1. Fetch the full staff record for the profile page
$current_staff = [];
$joinDate = 'Not available';

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $current_staff = $result->fetch_assoc();

        // 2. Format the Join Date for the profile header
        if (!empty($current_staff['created_at'])) {
            $joinDate = date('F Y', strtotime($current_staff['created_at']));
        }
    }
    $stmt->close();
}

// 3. Fallback initials logic (if session values aren't set)
if (empty($fullName) && !empty($current_staff)) {
    $firstName = $current_staff['firstName'];
    $lastName = $current_staff['lastName'];
    $fullName = trim($firstName . ' ' . $lastName);
    $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

// Dashboard Statistics Functions
function get_stats($conn)
{
    $stats = [
        'clients' => 0,
        'products' => 0,
        'orders' => 0,
        'suppliers' => 0,
    ];

    $result = $conn->query("SELECT COUNT(*) FROM client");
    if ($result) {
        $stats['clients'] = $result->fetch_row()[0];
        $result->close();
    }



    $result = $conn->query("SELECT COUNT(*) FROM product");
    if ($result) {
        $stats['products'] = $result->fetch_row()[0];
        $result->close();
    }



    $result = $conn->query("SELECT COUNT(*) FROM orders");
    if ($result) {
        $stats['orders'] = $result->fetch_row()[0];
        $result->close();
    }



    $result = $conn->query("SELECT COUNT(*) FROM supplier");
    if ($result) {
        $stats['suppliers'] = $result->fetch_row()[0];
        $result->close();
    }
    return $stats;
}


function get_recent_orders($conn)
{
    $orders = [];
    // Updated to include all columns requested by your dashboard view
    $query = "SELECT id, customer_name, total_amount, order_status 
              FROM orders 
              ORDER BY created_at DESC 
              LIMIT 5";

    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row; // Store the full row so keys like 'id' and 'total_amount' exist
        }
        $result->close();
    }
    return $orders;
}



// KEEP THIS NEW BLOCK (Line 120)
function get_most_sold_product($conn)
{
    $query = "SELECT oi.product_name, SUM(oi.quantity) as total_qty, COUNT(oi.id) as order_frequency
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              WHERE o.order_status != 'archived'
              GROUP BY oi.product_name
              ORDER BY total_qty DESC
              LIMIT 1";
    $result = $conn->query($query);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
}

function get_all_inquiries($conn)
{
    $inquiries = [];
    $query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $inquiries[] = $row;
        }
        $result->close();
    }
    return $inquiries;
}

// Call functions at the top of your file
$recent_orders = get_recent_orders($conn);
$best_seller = get_most_sold_product($conn);

// Update your variable declarations
$all_inquiries = get_all_inquiries($conn);
$total_inquiries = count($all_inquiries);

function get_all_products($conn)
{
    $products = [];
    $query = "SELECT * FROM product ORDER BY id DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!isset($row['staffFName'])) $row['staffFName'] = '';
            if (!isset($row['staffLName'])) $row['staffLName'] = '';
            $products[] = $row;
        }
        $result->close();
    }
    return $products;
}



function get_all_suppliers($conn)
{
    $suppliers = [];
    $query = "SELECT * FROM supplier ORDER BY id DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
        $result->close();
    }
    return $suppliers;
}



// Fetch data
$dashboard_analytics = get_dashboard_analytics($conn);
$lowStock   = $dashboard_analytics['low_stock'];
$outOfStock = $dashboard_analytics['out_of_stock'];
$solar_stats = get_solar_metrics($conn);
$order_status = get_order_status($conn);
$best_seller = get_best_seller($conn);
$stats = get_stats($conn);
$recent_orders = get_recent_orders($conn);
$most_sold_product = get_most_sold_product($conn);
$all_products = get_all_products($conn);
$all_suppliers = get_all_suppliers($conn);
$product_count = count($all_products);

// Close connection before HTML output starts
$conn->close();

$user_id = $_SESSION['user_id'];
$firstName = $_SESSION['firstName'] ?? 'User';
$lastName = $_SESSION['lastName'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);


// Handle AJAX requests
if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
    header('Content-Type: application/json');


    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
        exit;
    }

    $action = $_GET['action'] ?? ($_POST['action'] ?? 'fetch');

    try {

        switch ($action) {
            case 'fetch':
                $query = "SELECT * FROM supplier ORDER BY registrationDate DESC";
                $result = $conn->query($query);
                $suppliers = [];

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $suppliers[] = $row;
                    }
                }

                echo json_encode([
                    'success' => true,
                    'data' => $suppliers,
                    'count' => count($suppliers)
                ]);

                break;

            case 'create':
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data['supplierName'])) {
                    echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
                    break;
                }
                $supplierName = trim($data['supplierName']);
                $contactPerson = trim($data['contactPerson'] ?? '');
                $email = trim($data['email'] ?? '');
                $phone = trim($data['phone'] ?? '');
                $address = trim($data['address'] ?? '');
                $city = trim($data['city'] ?? '');
                $country = trim($data['country'] ?? '');

                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                    break;
                }

                $checkStmt = $conn->prepare("SELECT id FROM supplier WHERE supplierName = ?");
                $checkStmt->bind_param("s", $supplierName);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'Supplier with this name already exists']);
                    $checkStmt->close();
                    break;
                }

                $checkStmt->close();
                $stmt = $conn->prepare("INSERT INTO supplier (supplierName, contactPerson, email, phone, address, city, country, registrationDate) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $stmt->bind_param("sssssss", $supplierName, $contactPerson, $email, $phone, $address, $city, $country);
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Supplier created successfully',
                        'id' => $stmt->insert_id
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create supplier']);
                }
                $stmt->close();
                break;

            case 'fetch_orders':
                // Use the $conn established in the AJAX handler
                $query = "SELECT id, order_reference, customer_name, total_amount, created_at, payment_method, order_status 
                          FROM orders 
                          ORDER BY created_at DESC";
                $result = $conn->query($query);
                $orders = [];

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $orders[] = $row;
                    }
                    $result->close();
                }

                echo json_encode([
                    'success' => true,
                    'data' => $orders
                ]);
                break;

            case 'update':
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data['id']) || empty($data['supplierName'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                    break;
                }

                $id = intval($data['id']);
                $supplierName = trim($data['supplierName']);
                $contactPerson = trim($data['contactPerson'] ?? '');
                $email = trim($data['email'] ?? '');
                $phone = trim($data['phone'] ?? '');
                $address = trim($data['address'] ?? '');
                $city = trim($data['city'] ?? '');
                $country = trim($data['country'] ?? '');

                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                    break;
                }

                $dupStmt = $conn->prepare("SELECT id FROM supplier WHERE supplierName = ? AND id != ?");
                $dupStmt->bind_param("si", $supplierName, $id);
                $dupStmt->execute();
                $dupResult = $dupStmt->get_result();

                if ($dupResult->num_rows > 0) {
                    echo json_encode(['success' => false, 'message' => 'Another supplier with this name already exists']);
                    $dupStmt->close();
                    break;
                }

                $dupStmt->close();
                $stmt = $conn->prepare("UPDATE supplier SET supplierName = ?, contactPerson = ?, email = ?, phone = ?, address = ?, city = ?, country = ? WHERE id = ?");
                $stmt->bind_param("sssssssi", $supplierName, $contactPerson, $email, $phone, $address, $city, $country, $id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Supplier updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update supplier']);
                }
                $stmt->close();
                break;

            case 'update_inquiry_status':
                $data = json_decode(file_get_contents('php://input'), true);
                $id = intval($data['id']);
                $newStatus = $data['status']; // e.g., 'read'

                $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $newStatus, $id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Update failed']);
                }
                $stmt->close();
                break;

            case 'fetch_clients':
                // Querying unique clients from the orders table based on email
                $query = "SELECT 
                            customer_name, 
                            customer_email, 
                            customer_phone, 
                            customer_address, 
                            COUNT(id) as total_orders 
                          FROM orders 
                          GROUP BY customer_email 
                          ORDER BY customer_name ASC";

                $result = $conn->query($query);
                $clients = [];

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $clients[] = $row;
                    }
                    $result->close();
                }

                echo json_encode([
                    'success' => true,
                    'data' => $clients
                ]);
                break;

            case 'delete':
                $data = json_decode(file_get_contents('php://input'), true);

                if (empty($data['id'])) {
                    echo json_encode(['success' => false, 'message' => 'Supplier ID is required']);
                    break;
                }

                $id = intval($data['id']);

                $checkStmt = $conn->prepare("SELECT supplierName FROM supplier WHERE id = ?");
                $checkStmt->bind_param("i", $id);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();


                if ($checkResult->num_rows === 0) {
                    echo json_encode(['success' => false, 'message' => 'Supplier not found']);
                    $checkStmt->close();
                    break;
                }



                $supplier = $checkResult->fetch_assoc();
                $checkStmt->close();

                $stmt = $conn->prepare("DELETE FROM supplier WHERE id = ?");
                $stmt->bind_param("i", $id);


                if ($stmt->execute()) {

                    echo json_encode([
                        'success' => true,
                        'message' => 'Supplier deleted successfully',
                        'deletedName' => $supplier['supplierName']

                    ]);
                } else {

                    echo json_encode(['success' => false, 'message' => 'Failed to delete supplier']);
                }
                $stmt->close();
                break;
            case 'update_profile':
                $fName = $_POST['firstName'];
                $lName = $_POST['lastName'];
                $email = $_POST['email'];
                $phone = $_POST['contact'];
                $sId   = $_SESSION['staff_id'];

                $update = $conn->prepare("UPDATE staff SET firstName=?, lastName=?, email=?, contact_number=? WHERE id=?");
                $update->bind_param("ssssi", $fName, $lName, $email, $phone, $sId);

                if ($update->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Update failed']);
                }
                break;

            case 'change_password':
                $current = $_POST['currentPassword'];
                $new = $_POST['newPassword'];
                $sId = $_SESSION['staff_id'];

                // Validate new password
                if (strlen($new) < 8) {
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
                    break;
                }

                // First verify current password
                $check = $conn->prepare("SELECT password FROM staff WHERE id=?");
                $check->bind_param("i", $sId);
                $check->execute();
                $res = $check->get_result()->fetch_assoc();

                if (!$res) {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }

                $stored_password = $res['password'];
                $password_correct = false;

                // Check if stored password is hashed or plain text
                if (substr($stored_password, 0, 4) === '$2y$') {
                    // Hashed password - use password_verify
                    $password_correct = password_verify($current, $stored_password);
                } else {
                    // Plain text password (old data) - compare directly
                    $password_correct = ($current === $stored_password);
                }

                if ($password_correct) {
                    // Hash the new password
                    $hashedPassword = password_hash($new, PASSWORD_DEFAULT);

                    // Verify hash was created successfully
                    if (strlen($hashedPassword) < 60) {
                        echo json_encode(['success' => false, 'message' => 'Error creating password hash']);
                        break;
                    }

                    // Update with hashed password
                    $upd = $conn->prepare("UPDATE staff SET password=? WHERE id=?");
                    $upd->bind_param("si", $hashedPassword, $sId);

                    if ($upd->execute() && $upd->affected_rows > 0) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Password changed successfully'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to update password in database'
                        ]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
                }
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../assets/img/icon.png">
    <title>SolarPower - Staff</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container">

        <aside class="sidebar">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="logo">
                <a href="dashboard.php">
                    <img src="../../assets/img/logo_no_background.png" alt="Solar Power Logo">
                </a>
            </div>

            <div class="menu-item active" onclick="showPage('dashboard', 'Dashboard')" data-tooltip="Inquiries">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </div>


            <div class="menu-label">CUSTOMER OPERATIONS</div>
            <div class="menu-item" onclick="showPage('inquiries', 'Inquiries')" data-tooltip="Inquiries">
                <i class="fas fa-envelope-open-text"></i>
                <span>Inquiries</span>
            </div>

            <div class="menu-item" onclick="showPage('clients', 'Clients')" data-tooltip="Clients">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </div>

            <div class="menu-label">PRODUCT MANAGEMENT</div>
            <div class="menu-item" onclick="showPage('product', 'Product')" data-tooltip="Product">
                <i class="fas fa-box"></i>
                <span>Product</span>
            </div>

            <div class="menu-label">SALES & TRANSACTIONS</div>
            <div class="menu-item" onclick="showPage('tracking', 'Tracking')" data-tooltip="Tracking">
                <i class="fas fa-map-marker-alt"></i>
                <span>Tracking</span>
            </div>

            <div class="menu-item" onclick="showPage('orders', 'Orders')" data-tooltip="Orders">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
            </div>

            <div class="menu-item" onclick="showPage('quotation', 'Quotation')" data-tooltip="Quotation">
                <i class="fas fa-file-invoice"></i>
                <span>Quotation</span>
            </div>

            <div class="menu-label">SUPPLY MANAGEMENT</div>
            <div class="menu-item" onclick="showPage('suppliers', 'Suppliers')" data-tooltip="Suppliers">
                <i class="fas fa-truck"></i>
                <span>Suppliers</span>
            </div>

            <div class="menu-label">ARCHIVE</div>
            <div class="menu-item" onclick="showPage('archive', 'Archive')" data-tooltip="Archive">
                <i class="fas fa-archive"></i>
                <span>Archive</span>
            </div>

            <div class="menu-label">ACCOUNT</div>
            <!-- Add this in your sidebar menu, after the settings item -->
            <div class="menu-item" onclick="showPage('settings', 'Settings')" data-tooltip="Settings">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </div>
        </aside>



        <main class="main-content">
            <div class="header">
                <div class="header-left">
                    <h1 id="page-title">Dashboard</h1>
                    <p class="section-subtitle">Welcome back, <?php echo htmlspecialchars($firstName); ?></h1>
                    <p class="section-subtitle">Here's what's happening with your workspace today</p>
                </div>

                <div class="user-menu">
                    <div class="user-avatar">
                        <span><?php echo $initials; ?></span>
                    </div>

                    <div class="dropdown-menu" id="userDropdown">
                        <div class="dropdown-header"><?php echo htmlspecialchars($fullName); ?></div>
                        <ul>
                            <li><a href="../../controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>



            <div id="dashboard" class="page-content active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <i class="fas fa-users"></i>
                            <span>Total Clients</span>
                        </div>
                        <div class="stat-value"><?php echo $stats['clients']; ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <i class="fas fa-box"></i>
                            <span>Total Products</span>
                        </div>
                        <div class="stat-value"><?php echo $stats['products']; ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Total Orders</span>
                        </div>
                        <div class="stat-value"><?php echo $stats['orders']; ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <i class="fas fa-handshake"></i>
                            <span>Total Suppliers</span>
                        </div>
                        <div class="stat-value"><?php echo $stats['suppliers']; ?></div>
                    </div>

                </div>


                <div class="dashboard-details-container" style="display: flex; gap: 20px; margin-top: 25px; flex-wrap: wrap;">

                    <div class="details-card" style="flex: 2; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-clock" style="color: #ffc107;"></i> Recent Orders
                        </h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #f4f4f4; color: #888; font-size: 13px;">
                                    <th style="padding: 12px;">ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_orders)): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr style="border-bottom: 1px solid #f9f9f9; font-size: 14px;">
                                            <td style="padding: 12px; font-weight: bold;">#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge" style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 20px; font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                                    <?php echo $order['order_status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 20px;">No recent orders.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="details-card" style="flex: 1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; min-width: 300px;">
                        <h3 style="margin-bottom: 20px; color: #333;">Top Selling Product</h3>
                        <?php if ($best_seller): ?>
                            <div style="padding: 20px; border: 2px dashed #f1f1f1; border-radius: 10px;">
                                <div style="background: #fff9db; width: 60px; height: 60px; line-height: 60px; border-radius: 50%; margin: 0 auto 15px;">
                                    <i class="fas fa-medal" style="font-size: 28px; color: #f1c40f;"></i>
                                </div>
                                <h2 style="font-size: 22px; margin-bottom: 10px; color: #2c3e50;"><?php echo htmlspecialchars($best_seller['product_name']); ?></h2>
                                <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                                    <div>
                                        <p style="font-size: 20px; font-weight: bold; color: #27ae60;"><?php echo $best_seller['total_qty']; ?></p>
                                        <p style="font-size: 12px; color: #999; text-transform: uppercase;">Sold</p>
                                    </div>
                                    <div>
                                        <p style="font-size: 20px; font-weight: bold; color: #3498db;"><?php echo $best_seller['order_frequency']; ?></p>
                                        <p style="font-size: 12px; color: #999; text-transform: uppercase;">Orders</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p style="color: #999; margin-top: 30px;">No sales data available.</p>
                        <?php endif; ?>
                    </div>

                    <div class="details-card" style="flex:1; min-width:300px;">
                        <h3><i class="fas fa-solar-panel"></i> Solar Metrics</h3>

                        <div class="solar-metric">
                            <span>Total Panels Sold</span>
                            <strong><?php echo $solar_stats['panels_sold']; ?></strong>
                        </div>

                        <div class="solar-metric">
                            <span>Total System Installations</span>
                            <strong><?php echo $solar_stats['installations']; ?></strong>
                        </div>

                        <div class="solar-metric">
                            <span>Estimated kW Installed</span>
                            <strong><?php echo $solar_stats['kw_installed']; ?> kW</strong>
                        </div>
                    </div>

                    <div class="details-card" style="flex:1; min-width:300px;">
                        <h3><i class="fas fa-tasks"></i> Order Status</h3>

                        <ul class="status-list">
                            <li>Pending <span><?php echo $order_status['pending']; ?></span></li>
                            <li>Confirmed <span><?php echo $order_status['confirmed']; ?></span></li>
                            <li>Preparing <span><?php echo $order_status['preparing']; ?></span></li>
                            <li>Ready to Ship <span><?php echo $order_status['ready to ship']; ?></span></li>
                            <li>In Transit <span><?php echo $order_status['In Transit']; ?></span></li>
                            <li>Out for Delivery <span><?php echo $order_status['out for delivery']; ?></span></li>
                            <li>Delivered <span><?php echo $order_status['delivered']; ?></span></li>
                            <li>Cancelled <span><?php echo $order_status['cancelled']; ?></span></li>
                        </ul>
                    </div>

                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                    <div class="details-card sales-location-card" style="grid-column: span 3;">
                        <h3><i class="fas fa-map-marker-alt"></i> Sales by Location (Live)</h3>
                        <div id="freeMap" style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px; border: 1px solid #ddd;"></div>
                    </div>
                </div>

                <div class="dashboard-graph-sales">
                    <?php include "includes/graph-revenue.php"; ?>
                </div>
            </div>


            <div id="inquiries" class="page-content">
                <div class="tracking-page-container">
                    <div class="tracking-stats-grid">
                        <div class="tracking-stat-card stat-all">
                            <div class="stat-icon-wrapper"><i class="fas fa-envelope-open-text"></i></div>
                            <div class="stat-details">
                                <span class="stat-label">Total Inquiries</span>
                                <span class="stat-value" id="totalInquiries"><?php echo $total_inquiries; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="inquiries-cards-container" id="inquiriesContainer">
                        <?php foreach ($all_inquiries as $msg): ?>
                            <div class="inquiry-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 8px; background: #fff;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h3 style="margin: 0;"><?php echo htmlspecialchars($msg['name']); ?></h3>
                                        <small><?php echo $msg['email']; ?> | <?php echo $msg['phone']; ?></small>
                                    </div>
                                    <div style="text-align: right;">
                                        <span class="status-badge status-<?php echo $msg['status']; ?>" style="display:block; margin-bottom: 5px;">
                                            <?php echo strtoupper($msg['status']); ?>
                                        </span>
                                        <button onclick="updateStatus(<?php echo $msg['id']; ?>, 'read')" class="btn-status" style="padding: 4px 8px; cursor: pointer;">
                                            Mark as Read
                                        </button>
                                    </div>
                                </div>
                                <p style="margin-top: 10px; font-style: italic;">"<?php echo htmlspecialchars($msg['message']); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>



            <div id="product" class="page-content">
                <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
                    <div class="bulk-actions-left">
                        <span id="selectedCount">0</span> item(s) selected
                    </div>

                    <div class="bulk-actions-right">
                        <button class="btn-bulk-edit" id="bulkEditBtn">
                            <i class="fas fa-edit"></i> Edit Selected
                        </button>

                        <button class="btn-bulk-delete" id="bulkDeleteBtn">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        <button class="btn-deselect" id="deselectAllBtn">
                            <i class="fas fa-times"></i> Deselect All
                        </button>
                    </div>
                </div>



                <div class="marketplace-header">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="productSearchInput" placeholder="Search for Product">
                        <i class="fas fa-sliders-h filter-icon"></i>
                    </div>

                    <button class="btn-primary" onclick="showPage('add-product', 'Add Product')">
                        <i class="fas fa-plus"></i> Add new Product
                    </button>
                </div>

                <p class="product-count">
                    <span id="displayedProductCount"><?php echo $product_count; ?></span>
                    product<?php echo $product_count != 1 ? 's' : ''; ?>
                </p>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-buttons" id="categoryFilters">
                        <button class="filter-btn active" data-category="all">
                            <i class="fas fa-th"></i> All
                        </button>
                        <button class="filter-btn" data-category="Panel">
                            <i class="fas fa-solar-panel"></i> Panels
                        </button>
                        <button class="filter-btn" data-category="Inverter">
                            <i class="fas fa-plug"></i> Inverters
                        </button>
                        <button class="filter-btn" data-category="Battery">
                            <i class="fas fa-battery-full"></i> Batteries
                        </button>
                        <button class="filter-btn" data-category="Mounting & Accessories">
                            <i class="fas fa-tools"></i> Mounting & Accessories
                        </button>
                        <button class="filter-btn" data-category="Package Setup">
                            <i class="fas fa-tools"></i> Package Setup
                        </button>
                    </div>
                </div>

                <div class="product-list">
                    <?php if (!empty($all_products)): ?>
                        <?php
                        foreach ($all_products as $product):
                            $display_price = number_format($product['price'], 2);
                            $stock_class = $product['stockQuantity'] <= 5 ? 'low-stock' : 'in-stock';

                            // Construct the path to product's image folder
                            $product_id = $product['id'];
                            $image_folder = "../../uploads/products/{$product_id}/";

                            // Default placeholder
                            $image_src = '../assets/img/product-placeholder.png';

                            // Check if the folder exists and get the first image
                            if (is_dir($image_folder)) {
                                $images = glob($image_folder . "*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

                                if (!empty($images)) {
                                    // Get the first image
                                    $image_src = $images[0];
                                }
                            }
                        ?>
                            <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                                <div class="product-select">
                                    <input type="checkbox" class="product-checkbox-input" data-product-id="<?php echo $product['id']; ?>">
                                </div>

                                <div class="product-image">
                                    <img
                                        src="<?php echo $image_src; ?>"
                                        alt="<?php echo htmlspecialchars($product['displayName']); ?>"
                                        onerror="this.src='../assets/img/product-placeholder.png'">
                                </div>

                                <div class="product-content">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['displayName']); ?></h3>
                                    <p class="product-brand"><?php echo htmlspecialchars($product['brandName']); ?></p>

                                    <div class="product-meta">
                                        <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                    </div>

                                    <div class="product-footer">
                                        <span class="product-price">₱<?php echo $display_price; ?></span>
                                        <span class="product-stock <?php echo $stock_class; ?>">
                                            <?php echo $product['stockQuantity']; ?> in stock
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state" style="text-align: center; padding: 20px;">No products found in the database.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Edit Product Modal -->
            <div id="editProductModal" class="modal">
                <div class="modal-content modal-large">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2><i class="fas fa-edit"></i> Edit Product</h2>

                    <form id="editProductForm" method="POST" action="edit_product.php" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" id="editProductId">

                        <!-- Product Images Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-images"></i> Product Images</h3>

                            <!-- Current Images Display -->
                            <div id="currentImagesContainer" class="current-images-grid">
                                <!-- Images will be loaded here via JavaScript -->
                            </div>

                            <!-- Add New Images -->
                            <div class="form-group">
                                <label><i class="fas fa-plus-circle"></i> Add New Images</label>
                                <input type="file" name="new_images[]" id="newImagesInput" accept="image/*" multiple>
                                <small>You can select multiple images at once</small>
                            </div>
                        </div>

                        <!-- Product Details Section -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Product Details</h3>

                            <div class="form-group">
                                <label><i class="fas fa-cube"></i> Display Name</label>
                                <input type="text" name="displayName" id="editDisplayName" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-trademark"></i> Brand Name</label>
                                <input type="text" name="brandName" id="editBrandName" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-peso-sign"></i> Price</label>
                                    <input type="number" step="0.01" name="price" id="editPrice" required>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-tag"></i> Category</label>
                                    <select name="category" id="editCategory" required>
                                        <option value="Panel">Solar Panel</option>
                                        <option value="Battery">Battery</option>
                                        <option value="Inverter">Inverter</option>
                                        <option value="Mounting & Accessories">Mounting & Accessories</option>
                                        <option value="Package">Package</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-boxes"></i> Stock Quantity</label>
                                    <input type="number" name="stockQuantity" id="editStockQuantity" required>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-shield-alt"></i> Warranty</label>
                                    <input type="text" name="warranty" id="editWarranty">
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-align-left"></i> Description</label>
                                <textarea name="description" id="editDescription" rows="5" required></textarea>
                            </div>
                        </div>

                        <input type="hidden" name="delete_images" id="deleteImagesInput" value="">

                        <div class="modal-actions">
                            <button type="button" onclick="closeEditModal()" class="btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Delete Confirmation Modal -->
            <div id="deleteProductModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeDeleteModal()">&times;</span>
                    <h2>Confirm Deletion</h2>
                    <p>Are you sure you want to delete this product?</p>
                    <p class="warning-text">This action cannot be undone.</p>
                    <form id="deleteProductForm" method="POST" action="delete_product.php">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <div class="modal-actions">
                            <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Delete Confirmation Modal -->
            <div id="bulkDeleteModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeBulkDeleteModal()">&times;</span>
                    <h2>Confirm Bulk Deletion</h2>
                    <p>Are you sure you want to delete <strong id="bulkDeleteCount">0</strong> selected product(s)?</p>
                    <p class="warning-text">This action cannot be undone.</p>
                    <form id="bulkDeleteForm" method="POST" action="bulk_delete_products.php">
                        <input type="hidden" name="product_ids" id="bulkDeleteProductIds">
                        <div class="modal-actions">
                            <button type="button" onclick="closeBulkDeleteModal()" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-confirm-delete">Yes, Delete All</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quotation Modal -->
            <div id="quotationModal" class="modal">
                <div class="modal-quotation modal-medium">
                    <span class="close" onclick="closeQuotationModal()">&times;</span>
                    <h2 id="quotationModalTitle"><i class="fas fa-file-invoice"></i> New Quotation</h2>

                    <form id="quotationForm">
                        <input type="hidden" id="quotationId">

                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Client Name *</label>
                            <input type="text" id="clientName" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-envelope"></i> Email *</label>
                            <input type="text" id="email" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-address-book"></i> Contact Number *</label>
                            <input type="text" id="contact" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> Location</label>
                            <input type="text" id="location" placeholder="City, Province">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-solar-panel"></i> System Type *</label>
                                <select id="systemType" required>
                                    <option value="">Select System</option>
                                    <option value="HYBRID">Hybrid</option>
                                    <option value="SUPPLY-ONLY">Supply Only</option>
                                    <option value="GRID-TIE-HYBRID">Grid Tie Hybrid</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-bolt"></i> kW</label>
                                <input type="number" id="kw" min="0" step="0.01" placeholder="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user-tie"></i> Officer *</label>
                                <select id="officer" required>
                                    <option value="">Select Officer</option>
                                    <option value="PRINCESS">Princess</option>
                                    <option value="ANNE">Anne</option>
                                    <option value="GAB">Gab</option>
                                    <option value="JOY">Joy</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-flag"></i> Status *</label>
                                <select id="status" required>
                                    <option value="SENT">Sent</option>
                                    <option value="ONGOING">On Going</option>
                                    <option value="APPROVED">Approved</option>
                                    <option value="CLOSED">Closed</option>
                                    <option value="LOSS">Loss</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-comment"></i> Remarks</label>
                            <textarea id="remarks" rows="3" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="modal-actions">
                            <button type="button" onclick="closeQuotationModal()" class="btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Quotation
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Quotation Modal -->
            <div id="deleteQuotationModal" class="modal">
                <div class="modal-quotation">
                    <span class="close" onclick="closeDeleteQuotationModal()">&times;</span>
                    <h2>Confirm Deletion</h2>
                    <p>Are you sure you want to delete this quotation?</p>
                    <p class="warning-text">This action cannot be undone.</p>
                    <div class="modal-actions">
                        <button type="button" onclick="closeDeleteQuotationModal()" class="btn-cancel">Cancel</button>
                        <button type="button" onclick="confirmDeleteQuotation()" class="btn-confirm-delete">Yes, Delete</button>
                    </div>
                </div>
            </div>

            <!-- Other page sections remain the same -->
            <div id="add-product" class="page-content add-product-page">
                <?php
                $addProductMsg = '';
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_product') {
                    $conn_post = mysqli_connect($servername, $username, $password, $dbname);
                    if ($conn_post->connect_error) {
                        $addProductMsg = "<div class='alert error'>Connection failed: " . $conn_post->connect_error . "</div>";
                    } else {
                        $category = $conn_post->real_escape_string($_POST['category'] ?? '');
                        $brand = $conn_post->real_escape_string($_POST['brand'] ?? '');
                        $productName = $conn_post->real_escape_string($_POST['product-name'] ?? '');
                        $warranty = $conn_post->real_escape_string($_POST['warranty'] ?? '');
                        $price = (float)($_POST['price'] ?? 0);
                        $stockQuantity = (int)($_POST['stock-quantity'] ?? 0);
                        $description = $conn_post->real_escape_string($_POST['description'] ?? '');
                        $imagePath = 'path/to/uploaded/image.jpg';

                        if (empty($category) || empty($brand) || empty($productName) || $price <= 0 || $stockQuantity < 0) {
                            $addProductMsg = "<div class='alert error'>Please fill all required fields correctly.</div>";
                        } else {
                            $stmt = $conn_post->prepare("INSERT INTO product (displayName, brandName, price, category, stockQuantity, warranty, description, imagePath, postedByStaffId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("ssdsisssi", $productName, $brand, $price, $category, $stockQuantity, $warranty, $description, $imagePath, $user_id);

                            if ($stmt->execute()) {

                                $product_id = $stmt->insert_id;

                                // ===== IMAGE UPLOAD =====
                                $uploadDir = "../../uploads/products/$product_id/";
                                if (!is_dir($uploadDir)) {
                                    mkdir($uploadDir, 0777, true);
                                }

                                $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
                                $maxImages = 15;
                                $count = 0;

                                if (!empty($_FILES['product-images']['name'][0])) {

                                    foreach ($_FILES['product-images']['tmp_name'] as $key => $tmpName) {

                                        if ($count >= $maxImages) break;
                                        if ($_FILES['product-images']['error'][$key] !== 0) continue;

                                        $ext = strtolower(pathinfo($_FILES['product-images']['name'][$key], PATHINFO_EXTENSION));
                                        if (!in_array($ext, $allowedTypes)) continue;

                                        $newName = uniqid("img_") . "." . $ext;
                                        $targetPath = $uploadDir . $newName;

                                        if (move_uploaded_file($tmpName, $targetPath)) {

                                            $relativePath = "uploads/products/$product_id/$newName";

                                            $imgStmt = $conn_post->prepare("
                                    INSERT INTO product_images (product_id, image_path)
                                    VALUES (?, ?)
                                ");
                                            $imgStmt->bind_param("is", $product_id, $relativePath);
                                            $imgStmt->execute();
                                            $imgStmt->close();

                                            $count++;
                                        }
                                    }
                                }

                                $addProductMsg = "<div class='alert success'>
                        Product '{$productName}' added successfully with {$count} image(s)!
                    </div>";
                            } else {
                                $addProductMsg = "<div class='alert error'>Error adding product: " . $stmt->error . "</div>";
                            }
                            $stmt->close();
                        }
                        $conn_post->close();
                    }
                }
                ?>
                <style>
                    .alert {
                        padding: 10px;
                        margin-bottom: 20px;
                        border-radius: 5px;
                    }

                    .alert.error {
                        background-color: #fdd;
                        color: #a00;
                        border: 1px solid #f99;
                    }

                    .alert.success {
                        background-color: #dfd;
                        color: #0a0;
                        border: 1px solid #9f9;
                    }
                </style>

                <?php echo $addProductMsg; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">

                    <div class="add-product-layout">
                        <div class="form-section">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="category-select">
                                        <i class="fas fa-tag"></i>
                                        Category <span class="required">*</span>
                                    </label>
                                    <select id="category-select" name="category" required>
                                        <option value="">Select a category</option>
                                        <option value="Panel">Solar Panel</option>
                                        <option value="Battery">Battery</option>
                                        <option value="Inverter">Inverter</option>
                                        <option value="Mounting & Accessories">Mounting & Accessories</option>
                                        <option value="Package">Package Deals</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="brand-select">
                                        <i class="fas fa-trademark"></i>
                                        Brand Name <span class="required">*</span>
                                    </label>
                                    <select id="brand-select" name="brand" required disabled>
                                        <option value="">Select a category first</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="product-name-input">
                                        <i class="fas fa-cube"></i>
                                        Product Name <span class="required">*</span>
                                    </label>
                                    <input type="text" id="product-name-input" name="product-name" placeholder="Enter product name" required>
                                </div>

                                <div class="form-group">
                                    <label for="warranty">
                                        <i class="fas fa-shield-alt"></i>
                                        Warranty <span class="required">*</span>
                                    </label>
                                    <input type="text" id="warranty" name="warranty" placeholder="e.g., 5 years" value="5 years" required>
                                </div>

                                <div class="form-row-group">
                                    <div class="form-group">
                                        <label for="price-input">
                                            <i class="fas fa-peso-sign"></i>
                                            Price <span class="required">*</span>
                                        </label>
                                        <div class="input-wrapper">
                                            <input type="number" id="price-input" name="price" placeholder="0.00" step="0.01" required>
                                            <span class="input-icon">PHP</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="stock-quantity-input">
                                            <i class="fas fa-boxes"></i>
                                            Stock Quantity <span class="required">*</span>
                                        </label>
                                        <input type="number" id="stock-quantity-input" name="stock-quantity" placeholder="0" min="0" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description-input">
                                        <i class="fas fa-align-left"></i>
                                        Description <span class="required">*</span>
                                    </label>
                                    <textarea id="description-input" name="description" placeholder="Describe your product features, specifications, and benefits..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-image"></i>
                                        Product Images <span class="required">*</span>
                                    </label>

                                    <div class="file-upload-box">
                                        <input
                                            type="file"
                                            id="product-images"
                                            name="product-images[]"
                                            accept="image/*"
                                            multiple
                                            max="15">
                                        <div class="file-upload-content">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Upload up to 15 images</p>
                                            <span>PNG, JPG, WEBP (max 5MB each)</span>
                                        </div>
                                    </div>

                                    <div class="image-preview-grid" id="imagePreviewGrid"></div>
                                </div>

                                <div class="form-actions">
                                    <button type="reset" class="btn-action btn-reset">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn-action btn-publish">
                                        <i class="fas fa-rocket"></i> Publish Product
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <div class="preview-card">
                                <div class="preview-header">
                                    <h3><i class="fas fa-eye"></i> Live Preview</h3>
                                    <p>See how your product will appear</p>
                                </div>

                                <div class="preview-carousel">
                                    <img id="carousel-image" src="../../assets/img/placeholder.png" alt="Preview">
                                    <button type="button" class="carousel-btn prev" onclick="prevSlide()">‹</button>
                                    <button type="button" class="carousel-btn next" onclick="nextSlide()">›</button>
                                </div>

                                <div class="preview-info">
                                    <div class="preview-product-name" id="preview-name">Product Name</div>
                                    <div class="preview-category-tag" id="preview-category">
                                        <i class="fas fa-tag"></i> Category
                                    </div>
                                    <div class="preview-price" id="preview-price">₱0.00</div>
                                    <div class="preview-stock" id="preview-stock">
                                        <i class="fas fa-box"></i> Stock: 0 units
                                    </div>
                                    <div class="preview-actions">
                                        <button type="button" class="preview-cart-btn">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <button type="button" class="preview-buy-btn">
                                            Buy Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div id="tracking" class="page-content">
                <div class="tracking-page-container">
                    <!-- Header with Stats -->
                    <div class="tracking-stats-grid">
                        <div class="tracking-stat-card stat-all">
                            <div class="stat-icon-wrapper">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-label">Total Orders</span>
                                <span class="stat-value" id="trackingTotalOrders">0</span>
                            </div>
                        </div>

                        <div class="tracking-stat-card stat-transit">
                            <div class="stat-icon-wrapper">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-label">In Transit</span>
                                <span class="stat-value" id="trackingInTransit">0</span>
                            </div>
                        </div>

                        <div class="tracking-stat-card stat-delivery">
                            <div class="stat-icon-wrapper">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-label">Out for Delivery</span>
                                <span class="stat-value" id="trackingOutForDelivery">0</span>
                            </div>
                        </div>

                        <div class="tracking-stat-card stat-delivered">
                            <div class="stat-icon-wrapper">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-details">
                                <span class="stat-label">Delivered Today</span>
                                <span class="stat-value" id="trackingDeliveredToday">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="tracking-controls">
                        <div class="tracking-search-box">
                            <i class="fas fa-search"></i>
                            <input type="text"
                                id="trackingSearchInput"
                                placeholder="Search by Order ID, Customer, or Tracking Number...">
                        </div>

                        <div class="tracking-filters">
                            <select id="trackingStatusFilter" class="tracking-filter-select">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready_to_ship">Ready to Ship</option>
                                <option value="in_transit">In Transit</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                                <option value="delivered">Delivered</option>
                            </select>

                            <button class="btn-refresh" onclick="TrackingModule.loadTracking()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Tracking Cards Grid -->
                    <div class="tracking-cards-container" id="trackingCardsContainer">
                        <!-- Loading State -->
                        <div class="tracking-loading" id="trackingLoading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading tracking information...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Tracking Modal -->
            <div id="updateTrackingModal" class="modalTracking">
                <div class="modal-content modal-large">
                    <span class="close" onclick="TrackingModule.closeUpdateModal()">&times;</span>
                    <h2><i class="fas fa-map-marked-alt"></i> Update Order Tracking</h2>

                    <form id="updateTrackingForm">
                        <input type="hidden" id="trackingOrderId">

                        <div class="tracking-form-grid">
                            <!-- Left Column -->
                            <div class="tracking-form-left">
                                <div class="form-group">
                                    <label><i class="fas fa-barcode"></i> Order Reference</label>
                                    <input type="text" id="trackingOrderRef" readonly class="readonly-input">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Customer Name</label>
                                    <input type="text" id="trackingCustomerName" readonly class="readonly-input">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label><i class="fas fa-box"></i> Order Status *</label>
                                        <select id="trackingOrderStatus" required>
                                            <option value="pending">Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="preparing">Preparing</option>
                                            <option value="ready_to_ship">Ready to Ship</option>
                                            <option value="in_transit">In Transit</option>
                                            <option value="out_for_delivery">Out for Delivery</option>
                                            <option value="delivered">Delivered</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label><i class="fas fa-credit-card"></i> Payment Status *</label>
                                        <select id="trackingPaymentStatus" required>
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="partial">Partial</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-map-marker-alt"></i> Current Location *</label>
                                    <select id="trackingCurrentLocation" required>
                                        <option value="">Select Location</option>
                                        <option value="Main Warehouse - Alabang">Main Warehouse - Alabang</option>
                                        <option value="Distribution Hub - Makati">Distribution Hub - Makati</option>
                                        <option value="Distribution Hub - Quezon City">Distribution Hub - Quezon City</option>
                                        <option value="Distribution Hub - Cavite">Distribution Hub - Cavite</option>
                                        <option value="Distribution Hub - Laguna">Distribution Hub - Laguna</option>
                                        <option value="In Transit">In Transit</option>
                                        <option value="Out for Delivery">Out for Delivery</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                    <input type="text" id="trackingCustomLocation"
                                        placeholder="Or type custom location..."
                                        class="mt-2">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-truck"></i> Tracking Number</label>
                                    <input type="text" id="trackingNumber"
                                        placeholder="e.g., TRK-2025-001234">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-calendar"></i> Estimated Delivery</label>
                                    <input type="date" id="trackingEstimatedDelivery">
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="tracking-form-right">
                                <div class="form-group">
                                    <label><i class="fas fa-sticky-note"></i> Update Description *</label>
                                    <textarea id="trackingDescription"
                                        rows="4"
                                        required
                                        placeholder="Describe this update for the customer...&#10;&#10;Example: Your order has been dispatched from our warehouse and is on its way to you."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" id="trackingSendNotification" checked>
                                        <i class="fas fa-envelope"></i> Send email notification to customer
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button type="button"
                                onclick="TrackingModule.closeUpdateModal()"
                                class="btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Update Tracking
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="quotation" class="page-content">
                <div class="quotation-container">
                    <div class="quotation-stats">
                        <div class="stat-box">
                            <h4>TOTAL QUOTATIONS</h4>
                            <div class="value" id="totalQuotations">0</div>
                        </div>
                        <div class="stat-box">
                            <h4>HYBRID SYSTEM</h4>
                            <div class="value" id="hybridCount">0</div>
                        </div>
                        <div class="stat-box">
                            <h4>SUPPLY ONLY</h4>
                            <div class="value" id="supplyCount">0</div>
                        </div>
                        <div class="stat-box">
                            <h4>GRID TIE HYBRID</h4>
                            <div class="value" id="gridTieCount">0</div>
                        </div>
                    </div>

                    <div class="quotation-filters">
                        <div class="filter-group">
                            <label>Search</label>
                            <input type="text" id="quotationSearch" placeholder="Search by name, location...">
                        </div>
                        <div class="filter-group">
                            <label>System Type</label>
                            <select id="systemTypeFilter">
                                <option value="">All Systems</option>
                                <option value="HYBRID">Hybrid</option>
                                <option value="SUPPLY-ONLY">Supply Only</option>
                                <option value="GRID-TIE-HYBRID">Grid Tie Hybrid</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Officer</label>
                            <select id="officerFilter">
                                <option value="">All Officers</option>
                                <option value="PRINCESS">Princess</option>
                                <option value="ANNE">Anne</option>
                                <option value="GAB">Gab</option>
                                <option value="JOY">Joy</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <select id="quotationStatusFilter">
                                <option value="">All</option>
                                <option value="SENT">Sent</option>
                                <option value="ONGOING">On Going</option>
                                <option value="APPROVED">Approved</option>
                                <option value="CLOSED">Closed</option>
                                <option value="LOSS">Loss</option>
                            </select>
                        </div>
                    </div>

                    <div class="quotation-header">
                        <button class="btn-primary" onclick="openQuotationModal()">
                            <i class="fas fa-plus"></i> New Quotation
                        </button>
                    </div>

                    <div class="quotation-table-wrapper">
                        <table class="quotation-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>System</th>
                                    <th>kW</th>
                                    <th>Officer</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="quotationTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="orders" class="page-content">
                <div class="orders-container">

                    <!-- HEADER -->
                    <div class="orders-header">
                        <div class="orders-title">
                            <h3><i class="fas fa-shopping-cart"></i> Order Check</h3>
                        </div>

                        <button class="btn-refresh" onclick="OrdersModule.loadOrders()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>

                    <!-- FILTERS (OPTIONAL BUT CONSISTENT) -->
                    <div class="orders-filters">
                        <div class="orders-filter-group">
                            <label>Search</label>
                            <input type="text" id="orderSearch" placeholder="Search by customer or order ID">
                        </div>

                        <div class="orders-filter-group">
                            <label>Status</label>
                            <select id="orderStatusFilter">
                                <option value="">All Status</option>
                                <option value="PAID">Paid</option>
                                <option value="PENDING">Pending</option>
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                        </div>

                        <div class="orders-filter-group">
                            <label>Payment</label>
                            <select id="paymentFilter">
                                <option value="">All Payments</option>
                                <option value="GCASH">GCash</option>
                                <option value="PAYMAYA">PayMaya</option>
                                <option value="CASH">Cash</option>
                            </select>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="orders-table-wrapper">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total Amount</th>
                                    <th>Date</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- display only -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Suppliers -->
            <div id="suppliers" class="page-content">
                <div class="suppliers-container">

                    <!-- STATS -->
                    <div class="suppliers-stats">
                        <div class="supplier-stat-box">
                            <h4>TOTAL SUPPLIERS</h4>
                            <div class="value" id="totalSuppliers">0</div>
                        </div>
                        <div class="supplier-stat-box">
                            <h4>ACTIVE THIS MONTH</h4>
                            <div class="value" id="activeSuppliers">0</div>
                        </div>
                        <div class="supplier-stat-box">
                            <h4>NEW SUPPLIERS</h4>
                            <div class="value" id="newSuppliers">0</div>
                        </div>
                        <div class="supplier-stat-box">
                            <h4>TOTAL CITIES</h4>
                            <div class="value" id="totalCities">0</div>
                        </div>
                    </div>

                    <!-- FILTERS -->
                    <div class="suppliers-filters">
                        <div class="supplier-filter-group">
                            <label>Search</label>
                            <input type="text" id="supplierSearch" placeholder="Search by      name, city...">
                        </div>
                        <div class="supplier-filter-group">
                            <label>City</label>
                            <select id="cityFilter">
                                <option value="">All Cities</option>
                            </select>
                        </div>
                        <div class="supplier-filter-group">
                            <label>Status</label>
                            <select id="supplierStatusFilter">
                                <option value="">All</option>
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- HEADER ACTION -->
                    <div class="suppliers-header">
                        <button class="btn-primary" onclick="openSupplierModal()">
                            <i class="fas fa-plus"></i> Add Supplier
                        </button>
                    </div>

                    <!-- TABLE -->
                    <div class="suppliers-table-wrapper">
                        <table class="suppliers-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Supplier Name</th>
                                    <th>Contact Person</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="suppliersTableBody">
                                <!-- dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- ARCHIVE -->
            <div id="archive" class="page-content" style="display: none;">
            
                <!-- Product Details Section -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin-top: 30px;">
                    <div style="display: flex; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                        <i class="bi bi-box-seam" style="color: #f39c12; font-size: 24px; margin-right: 12px;"></i>
                        <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">Product Details</h4>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Display Name</label>
                            <input type="text" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter display name">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Brand Name</label>
                            <input type="text" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter brand name">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Price</label>
                            <div style="display: flex;">
                                <span style="padding: 10px 14px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-right: none; border-radius: 8px 0 0 8px; color: #6c757d; font-size: 14px;">₱</span>
                                <input type="number" style="flex: 1; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 0 8px 8px 0; font-size: 14px;" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Category</label>
                            <select style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; background-color: white;">
                                <option selected>Select category</option>
                                <option value="panels">Panels</option>
                                <option value="inverters">Inverters</option>
                                <option value="batteries">Batteries</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Stock Quantity</label>
                            <div style="display: flex;">
                                <input type="number" style="flex: 1; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px 0 0 8px; font-size: 14px;" placeholder="0">
                                <span style="padding: 10px 14px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: none; border-radius: 0 8px 8px 0; color: #6c757d; font-size: 14px;">units</span>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Warranty</label>
                            <div style="display: flex;">
                                <input type="number" style="flex: 1; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px 0 0 8px; font-size: 14px;" placeholder="0">
                                <span style="padding: 10px 14px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: none; border-radius: 0 8px 8px 0; color: #6c757d; font-size: 14px;">Years</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Description</label>
                        <textarea style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; resize: vertical; min-height: 100px;" rows="4" placeholder="Enter product description"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                        <button type="button" style="padding: 10px 24px; background-color: transparent; color: #6c757d; border: 2px solid #6c757d; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Cancel</button>
                        <button type="button" style="padding: 10px 24px; background-color: #27ae60; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Save Product</button>
                    </div>
                </div>


                <!--Archived quotation  -->
                <!-- Quotation Details Section -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin-top: 30px;">
                    <div style="display: flex; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                        <i class="bi bi-file-text" style="color: #f39c12; font-size: 24px; margin-right: 12px;"></i>
                        <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">Quotation Details</h4>
                    </div>

                    <form>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Client Name</label>
                                <input type="text" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter client name">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Email</label>
                                <input type="email" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter email address">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Contact</label>
                                <input type="text" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter contact number">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Location</label>
                                <input type="text" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter location">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">System</label>
                                <select style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; background-color: white;">
                                    <option selected>Select system</option>
                                    <option value="on-grid">On-Grid</option>
                                    <option value="off-grid">Off-Grid</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">KW</label>
                                <div style="display: flex;">
                                    <input type="number" style="flex: 1; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px 0 0 8px; font-size: 14px;" placeholder="0.00" step="0.01">
                                    <span style="padding: 10px 14px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-left: none; border-radius: 0 8px 8px 0; color: #6c757d; font-size: 14px;">kW</span>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Officer</label>
                                <input type="text" style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px;" placeholder="Enter officer name">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Status</label>
                                <select style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; background-color: white;">
                                    <option selected>Select status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Remarks</label>
                            <textarea style="width: 100%; padding: 10px 14px; border: 1px solid #dee2e6; border-radius: 8px; font-size: 14px; resize: vertical; min-height: 100px;" rows="4" placeholder="Enter remarks"></textarea>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 600; color: #6c757d; margin-bottom: 0.5rem; font-size: 14px;">Actions</label>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button type="button" style="padding: 8px 16px; background-color: #3498db; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button type="button" style="padding: 8px 16px; background-color: #2ecc71; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" style="padding: 8px 16px; background-color: #f39c12; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                                <button type="button" style="padding: 8px 16px; background-color: #e74c3c; color: white; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                            <button type="button" style="padding: 10px 24px; background-color: transparent; color: #6c757d; border: 2px solid #6c757d; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Cancel</button>
                            <button type="button" style="padding: 10px 24px; background-color: #27ae60; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Save Quotation</button>
                        </div>
                    </form>
                </div>


                <!-- Archive Suppliers Section -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin-top: 30px;">
                    <div style="display: flex; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                        <i class="bi bi-archive" style="color: #f39c12; font-size: 24px; margin-right: 12px;"></i>
                        <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">Archive Suppliers</h4>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">ID</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Supplier Name</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Contact Person</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Email</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Phone</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Location</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Registered</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">001</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Solar Tech Solutions</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">John Doe</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">john@solartech.com</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">+63 912 345 6789</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Manila, Philippines</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Jan 15, 2024</td>
                                    <td style="padding: 12px;">
                                        <div style="display: flex; gap: 4px;">
                                            <button style="padding: 6px 12px; background-color: transparent; color: #007bff; border: 1px solid #007bff; border-radius: 6px; font-size: 14px; cursor: pointer;" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button style="padding: 6px 12px; background-color: transparent; color: #28a745; border: 1px solid #28a745; border-radius: 6px; font-size: 14px; cursor: pointer;" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button style="padding: 6px 12px; background-color: transparent; color: #dc3545; border: 1px solid #dc3545; border-radius: 6px; font-size: 14px; cursor: pointer;" title="Delete Permanently">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">002</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Green Energy Corp</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Jane Smith</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">jane@greenenergy.com</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">+63 923 456 7890</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Quezon City, Philippines</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Feb 20, 2024</td>
                                    <td style="padding: 12px;">
                                        <div style="display: flex; gap: 4px;">
                                            <button style="padding: 6px 12px; background-color: transparent; color: #007bff; border: 1px solid #007bff; border-radius: 6px; font-size: 14px; cursor: pointer;" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button style="padding: 6px 12px; background-color: transparent; color: #28a745; border: 1px solid #28a745; border-radius: 6px; font-size: 14px; cursor: pointer;" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button style="padding: 6px 12px; background-color: transparent; color: #dc3545; border: 1px solid #dc3545; border-radius: 6px; font-size: 14px; cursor: pointer;" title="Delete Permanently">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">003</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Power Systems Inc</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Mike Johnson</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">mike@powersystems.com</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">+63 934 567 8901</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Makati, Philippines</td>
                                    <td style="padding: 12px; font-size: 14px; color: #2c3e50;">Mar 10, 2024</td>
                                    <td style="padding: 12px;">
                                        <div style="display: flex; gap: 4px;">
                                            <button style="padding: 6px 12px; background-color: transparent; color: #007bff; border: 1px solid #007bff; border-radius: 6px; font-size: 14px; cursor: pointer;" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button style="padding: 6px 12px; background-color: transparent; color: #28a745; border: 1px solid #28a745; border-radius: 6px; font-size: 14px; cursor: pointer;" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                            <button style="padding: 6px 12px; background-color: transparent; color: #dc3545; border: 1px solid #dc3545; border-radius: 6px; font-size: 14px; cursor: pointer;" title="Delete Permanently">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>




            </div>

            <!-- Add/Edit Supplier Modal -->
            <div id="supplierModal" class="modal">
                <div class="modal-content">
                    <h2 id="supplierModalTitle">
                        <span><i class="fas fa-truck"></i> Add New Supplier</span>
                        <span class="close" onclick="closeSupplierModal()">&times;</span>
                    </h2>

                    <form id="supplierForm" onsubmit="handleSubmit(event)">
                        <div class="modal-body">
                            <input type="hidden" id="supplierId">

                            <div class="form-grid">
                                <div class="form-group">
                                    <label><i class="fas fa-building"></i> Supplier Name *</label>
                                    <input type="text" id="supplierName" required>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Contact Person</label>
                                    <input type="text" id="contactPerson">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" id="email">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-phone"></i> Phone</label>
                                    <input type="text" id="phone">
                                </div>

                                <div class="form-group full-width">
                                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                                    <textarea id="address" rows="2"></textarea>
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-city"></i> City</label>
                                    <input type="text" id="city">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-flag"></i> Country</label>
                                    <input type="text" id="country">
                                </div>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button type="button" onclick="closeSupplierModal()" class="btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Supplier Modal -->
            <div id="deleteSupplierModal" class="modal">
                <div class="modal-content" style="max-width: 500px;">
                    <h2>
                        <span><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</span>
                        <span class="close" onclick="closeDeleteSupplierModal()">&times;</span>
                    </h2>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this supplier?</p>
                        <p class="warning-text">This action cannot be undone.</p>
                        <input type="hidden" id="deleteSupplierId">
                    </div>
                    <div class="modal-actions">
                        <button type="button" onclick="closeDeleteSupplierModal()" class="btn-cancel">Cancel</button>
                        <button type="button" onclick="confirmDeleteSupplier()" class="btn-confirm-delete">
                            <i class="fas fa-trash"></i> Yes, Delete
                        </button>
                    </div>
                </div>
            </div>

            <div id="clients" class="page-content">
                <div class="clients-container">

                    <!-- HEADER -->
                    <div class="clients-header">
                        <div class="clients-title">
                            <h3><i class="fas fa-users"></i> Clients</h3>
                            <p>View registered clients and their order history</p>
                        </div>
                    </div>

                    <!-- FILTERS -->
                    <div class="clients-filters">
                        <div class="clients-filter-group">
                            <label>Search</label>
                            <input type="text" id="clientSearch" placeholder="Search by name        or email">
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="clients-table-wrapper">
                        <table class="clients-table">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email Address</th>
                                    <th>Contact Number</th>
                                    <th>Full Delivery Address</th>
                                    <th>Total Orders</th>
                                </tr>
                            </thead>
                            <tbody id="clientsTableBody">
                                <!-- data display only -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <div id="settings" class="page-content profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-header-content">
                        <div class="profile-avatar-large">
                            <?php echo $initials; ?>
                        </div>
                        <div class="profile-info">
                            <h1 class="profile-name"><?php echo htmlspecialchars($fullName); ?></h1>
                            <div class="profile-role">
                                <i class="fas fa-user-tie"></i>
                                <span>Staff Member</span>
                            </div>
                            <div class="profile-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>Joined <?php echo $joinDate; ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-envelope"></i>
                                    <span id="headerEmail"><?php echo htmlspecialchars($current_staff['email']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Cards Grid -->
                <div class="profile-grid">
                    <!-- Personal Information Card -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user"></i>
                                Personal Information
                            </h2>
                            <button class="btn-edit" onclick="openEditProfileModal()">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-label">First Name</div>
                                <div class="info-value" id="displayFirstName"><?php echo htmlspecialchars($current_staff['firstName']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Last Name</div>
                                <div class="info-value" id="displayLastName"><?php echo htmlspecialchars($current_staff['lastName']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-value" id="displayEmail"><?php echo htmlspecialchars($current_staff['email']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value" id="displayContact"><?php echo htmlspecialchars($current_staff['contact_number'] ?: 'Not set'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Security Card -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-shield-alt"></i>
                                Account Security
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-label">Password</div>
                                <div class="info-value">••••••••</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Account Created</div>
                                <div class="info-value"><?php echo date('F d, Y', strtotime($current_staff['created_at'])); ?></div>
                            </div>
                            <div class="security-actions">
                                <div class="security-btn" onclick="openChangePasswordModal()">
                                    <div class="security-icon">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="security-info">
                                        <h4>Change Password</h4>
                                        <p>Update your password</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Modal -->
            <div id="editProfileModal" class="staffModal">
                <div class="staffModal-content">
                    <div class="staffModal-header">
                        <h3>
                            <i class="fas fa-user-edit"></i>
                            <span>Edit Personal Information</span>
                        </h3>
                        <span class="close" onclick="closeEditProfileModal()">&times;</span>
                    </div>
                    <form id="editProfileForm" onsubmit="handleUpdateProfile(event)">
                        <div class="staffModal-body">
                            <div class="staffModal-group">
                                <label><i class="fas fa-user"></i> First Name</label>
                                <input type="text" id="profileFirstName" value="<?php echo htmlspecialchars($current_staff['firstName']); ?>" required>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-user"></i> Last Name</label>
                                <input type="text" id="profileLastName" value="<?php echo htmlspecialchars($current_staff['lastName']); ?>" required>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($current_staff['email']); ?>" required>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-phone"></i> Contact Number</label>
                                <input type="tel" id="profileContactNumber" value="<?php echo htmlspecialchars($current_staff['contact_number']); ?>">
                            </div>
                        </div>
                        <div class="staffModal-footer">
                            <button type="button" class="staffModal-btn-secondary" onclick="closeEditProfileModal()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="staffModal-btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Modal -->
            <div id="changePasswordModal" class="staffModal">
                <div class="staffModal-content">
                    <div class="staffModal-header">
                        <h3>
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </h3>
                        <span class="close" onclick="closeChangePasswordModal()">&times;</span>
                    </div>
                    <form id="changePasswordForm" onsubmit="handleChangePassword(event)">
                        <div class="staffModal-body">
                            <div class="staffModal-group">
                                <label><i class="fas fa-lock"></i> Current Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="currentPassword" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('currentPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-lock"></i> New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="newPassword" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('newPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-lock"></i> Confirm New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirmPassword" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('confirmPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="staffModal-footer">
                            <button type="button" class="staffModal-btn-secondary" onclick="closeChangePasswordModal()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="staffModal-btn-primary">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>


        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');

            // Optional: Save state to localStorage
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }

        // Optional: Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
            if (sidebarCollapsed === 'true') {
                document.querySelector('.sidebar').classList.add('collapsed');
            }
        });

        // Define the custom yellow marker icon
        var yellowIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // 1. Initialize the map (centered on Philippines)
        const map = L.map('freeMap').setView([12.8797, 121.7740], 6);

        // 2. Add the free OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // 3. Get your order data from PHP
        const orders = <?php
                        // Fetch orders with addresses
                        $conn = mysqli_connect($servername, $username, $password, $dbname);
                        $res = $conn->query("SELECT customer_name, customer_address, total_amount FROM orders WHERE customer_address IS NOT NULL LIMIT 10");
                        $data = [];
                        while ($row = $res->fetch_assoc()) {
                            $data[] = $row;
                        }
                        echo json_encode($data);
                        ?>;

        // 4. Function to convert address to Pin
        const usedLocations = {};

        async function addPin(order) {
            const addressParts = order.customer_address.split(',');
            const cleanLocation = addressParts.length > 1 ? addressParts.slice(-2).join(', ') : order.customer_address;
            const query = `${cleanLocation.trim()}, Philippines`;

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
                const results = await response.json();

                if (results.length > 0) {
                    let lat = parseFloat(results[0].lat);
                    let lon = parseFloat(results[0].lon);

                    // --- JITTER LOGIC START ---
                    // If we've already used this exact lat/lon, nudge it slightly
                    const key = `${lat.toFixed(4)}_${lon.toFixed(4)}`;
                    if (usedLocations[key]) {
                        // Add a tiny random offset (about 100-200 meters)
                        lat += (Math.random() - 0.5) * 0.005;
                        lon += (Math.random() - 0.5) * 0.005;
                    }
                    usedLocations[key] = true;
                    // --- JITTER LOGIC END ---

                    const marker = L.marker([lat, lon], {
                        icon: yellowIcon
                    }).addTo(map);

                    marker.bindPopup(`
                <b>${order.customer_name}</b><br>
                ${cleanLocation}<br>
                <strong>₱${parseFloat(order.total_amount).toLocaleString()}</strong>
            `);
                }
            } catch (error) {
                console.error("Geocoding failed:", error);
            }
        }

        // 5. Run the function for each order
        orders.forEach(order => addPin(order));

        function updateStatus(id, status) {
            fetch(window.location.href + '?ajax=1&action=update_inquiry_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Status updated!');
                        location.reload(); // Refresh to show new status
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        // ============================================
        // TOAST NOTIFICATION SYSTEM
        // ============================================
        const toastStyles = `
<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 16px 20px;
    min-width: 300px;
    max-width: 400px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease-out;
    border-left: 4px solid #4CAF50;
}

.toast.error {
    border-left-color: #f44336;
}

.toast.warning {
    border-left-color: #ff9800;
}

.toast.info {
    border-left-color: #2196F3;
}

.toast-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.toast.success .toast-icon {
    color: #4CAF50;
}

.toast.error .toast-icon {
    color: #f44336;
}

.toast.warning .toast-icon {
    color: #ff9800;
}

.toast.info .toast-icon {
    color: #2196F3;
}

.toast-message {
    flex: 1;
    color: #333;
    font-size: 14px;
}

.toast-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 18px;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.toast-close:hover {
    color: #333;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
</style>
`;

        // Add toast styles
        document.head.insertAdjacentHTML('beforeend', toastStyles);

        // Create toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        function showToast(message, type = 'success', duration = 3000) {
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
        <i class="${icons[type]} toast-icon"></i>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // ============================================
        // PROFILE MODAL FUNCTIONS
        // ============================================
        function openEditProfileModal() {
            document.getElementById('editProfileModal').classList.add('show');
        }

        function closeEditProfileModal() {
            document.getElementById('editProfileModal').classList.remove('show');
        }

        function openChangePasswordModal() {
            const form = document.getElementById('changePasswordForm');
            if (form) form.reset();
            document.getElementById('changePasswordModal').classList.add('show');
        }

        function closeChangePasswordModal() {
            const form = document.getElementById('changePasswordForm');
            if (form) form.reset();
            document.getElementById('changePasswordModal').classList.remove('show');
        }

        function togglePasswordField(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // ============================================
        // UPDATE PROFILE FUNCTION
        // ============================================
        async function handleUpdateProfile(event) {
            event.preventDefault();

            const firstName = document.getElementById('profileFirstName').value.trim();
            const lastName = document.getElementById('profileLastName').value.trim();
            const email = document.getElementById('profileEmail').value.trim();
            const contact = document.getElementById('profileContactNumber').value.trim();

            // Validation
            if (!firstName || !lastName || !email) {
                showToast('Please fill in all required fields', 'error');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showToast('Please enter a valid email address', 'error');
                return;
            }

            const data = {
                action: 'update_profile',
                firstName: firstName,
                lastName: lastName,
                email: email,
                contact_number: contact
            };

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            try {
                const response = await fetch('profile_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message || 'Profile updated successfully!', 'success');

                    // Update display values
                    document.getElementById('displayFirstName').textContent = firstName;
                    document.getElementById('displayLastName').textContent = lastName;
                    document.getElementById('displayEmail').textContent = email;
                    document.getElementById('headerEmail').textContent = email;
                    document.getElementById('displayContact').textContent = contact || 'Not set';

                    // Update header
                    const profileName = document.querySelector('.profile-name');
                    if (profileName) {
                        profileName.textContent = `${firstName} ${lastName}`;
                    }

                    // Update avatar
                    const profileAvatar = document.querySelector('.profile-avatar-large');
                    if (profileAvatar) {
                        const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                        profileAvatar.textContent = initials;
                    }

                    setTimeout(() => {
                        closeEditProfileModal();
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Connection error. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // ============================================
        // CHANGE PASSWORD FUNCTION
        // ============================================
        async function handleChangePassword(event) {
            event.preventDefault();

            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                showToast('Please fill in all password fields', 'error');
                return;
            }

            if (newPassword !== confirmPassword) {
                showToast('New passwords do not match!', 'error');
                return;
            }

            if (newPassword.length < 8) {
                showToast('Password must be at least 8 characters long!', 'error');
                return;
            }

            const data = {
                action: 'change_password',
                currentPassword: currentPassword,
                newPassword: newPassword
            };

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            try {
                const response = await fetch('profile_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                // IMPORTANT: Log the full response for debugging
                console.log('Password Update Response:', result);

                // If there's debug info, show it prominently
                if (result.debug) {
                    console.log('=== DEBUG INFO ===');
                    console.table(result.debug);
                    console.log('==================');
                }

                if (result.success) {
                    showToast(result.message || 'Password changed successfully!', 'success');

                    // Show debug info in success case too
                    if (result.debug) {
                        console.log('✅ Success! Check debug info above for details.');

                        // Alert if password was properly saved
                        if (result.debug.hash_properly_saved === false) {
                            console.error('⚠️ WARNING: Password hash was NOT properly saved!');
                            showToast('WARNING: Password may not have been saved correctly. Check console.', 'error');
                        }
                    }

                    setTimeout(() => {
                        closeChangePasswordModal();
                        document.getElementById('changePasswordForm').reset();
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to change password', 'error');

                    // Show detailed error in console
                    if (result.debug) {
                        console.error('❌ Password update failed. Debug info:');
                        console.table(result.debug);

                        // If there's a fix suggestion, show it
                        if (result.debug.fix_required) {
                            console.error('💡 FIX REQUIRED:');
                            console.error(result.debug.fix_required);
                        }
                    }
                }
            } catch (error) {
                console.error('Connection Error:', error);
                showToast('Connection error. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // Close modals on outside click
        window.addEventListener('click', function(event) {
            const editModal = document.getElementById('editProfileModal');
            const passwordModal = document.getElementById('changePasswordModal');

            if (event.target === editModal) {
                closeEditProfileModal();
            }
            if (event.target === passwordModal) {
                closeChangePasswordModal();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEditProfileModal();
                closeChangePasswordModal();
            }
        });

        console.log('✅ Profile management initialized');


        const SuppliersModule = {
            suppliers: [],
            filteredSuppliers: [],

            async init() {
                await this.loadSuppliers();
                this.setupEventListeners();
            },

            setupEventListeners() {
                const searchInput = document.getElementById('supplierSearch');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
                }
            },

            async loadSuppliers() {
                try {
                    const response = await fetch('?ajax=1&action=fetch');
                    const result = await response.json();

                    if (result.success) {
                        this.suppliers = result.data;
                        this.filteredSuppliers = [...this.suppliers];
                        this.renderTable();
                        this.updateStats();
                    } else {
                        this.showError('Failed to load suppliers');
                    }
                } catch (error) {
                    console.error('Error loading suppliers:', error);
                    this.showError('Error connecting to database');
                }
            },

            renderTable() {
                const tbody = document.getElementById('suppliersTableBody');
                if (!tbody) return;

                if (this.filteredSuppliers.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-inbox"></i><br>
                                No suppliers found
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = this.filteredSuppliers.map(supplier => `
                    <tr>
                        <td><strong>#${supplier.id}</strong></td>
                        <td><strong>${this.escapeHtml(supplier.supplierName)}</strong></td>
                        <td>${this.escapeHtml(supplier.contactPerson || '-')}</td>
                        <td>${this.escapeHtml(supplier.email || '-')}</td>
                        <td>${this.escapeHtml(supplier.phone || '-')}</td>
                        <td>
                            <div class="location-cell">
                                <span class="location-city">${this.escapeHtml(supplier.city || '-')}</span>
                                <span class="location-country">${this.escapeHtml(supplier.country || '-')}</span>
                            </div>
                        </td>
                        <td>${this.formatDate(supplier.registrationDate)}</td>
                        <td>
                            <div class="supplier-actions">
                                <button class="btn-table-action btn-edit" onclick="SuppliersModule.editSupplier(${supplier.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-table-action btn-delete" onclick="SuppliersModule.showDeleteModal(${supplier.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            },

            updateStats() {
                const total = this.suppliers.length;
                const uniqueCities = new Set(this.suppliers.map(s => s.city).filter(Boolean)).size;

                const now = new Date();
                const currentMonth = now.getMonth();
                const currentYear = now.getFullYear();

                const activeThisMonth = this.suppliers.filter(s => {
                    const date = new Date(s.registrationDate);
                    return date.getMonth() === currentMonth && date.getFullYear() === currentYear;
                }).length;

                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                const newSuppliers = this.suppliers.filter(s => {
                    return new Date(s.registrationDate) > thirtyDaysAgo;
                }).length;

                document.getElementById('totalSuppliers').textContent = total;
                document.getElementById('activeSuppliers').textContent = activeThisMonth;
                document.getElementById('newSuppliers').textContent = newSuppliers;
                document.getElementById('totalCities').textContent = uniqueCities;
            },

            handleSearch(searchTerm) {
                const term = searchTerm.toLowerCase();
                this.filteredSuppliers = this.suppliers.filter(supplier => {
                    return (supplier.supplierName || '').toLowerCase().includes(term) ||
                        (supplier.city || '').toLowerCase().includes(term) ||
                        (supplier.country || '').toLowerCase().includes(term) ||
                        (supplier.contactPerson || '').toLowerCase().includes(term);
                });
                this.renderTable();
            },

            editSupplier(id) {
                const supplier = this.suppliers.find(s => s.id === id);
                if (!supplier) return;

                document.getElementById('supplierModalTitle').innerHTML = '<span><i class="fas fa-edit"></i> Edit Supplier</span><span class="close" onclick="closeSupplierModal()">&times;</span>';
                document.getElementById('supplierId').value = supplier.id;
                document.getElementById('supplierName').value = supplier.supplierName || '';
                document.getElementById('contactPerson').value = supplier.contactPerson || '';
                document.getElementById('email').value = supplier.email || '';
                document.getElementById('phone').value = supplier.phone || '';
                document.getElementById('address').value = supplier.address || '';
                document.getElementById('city').value = supplier.city || '';
                document.getElementById('country').value = supplier.country || '';

                document.getElementById('supplierModal').style.display = 'block';
            },

            showDeleteModal(id) {
                document.getElementById('deleteSupplierId').value = id;
                document.getElementById('deleteSupplierModal').style.display = 'block';
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },

            showError(message) {
                this.showNotification(message, 'error');
            },

            showSuccess(message) {
                this.showNotification(message, 'success');
            },

            showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `notification ${type}-notification`;
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                `;

                document.body.appendChild(notification);

                setTimeout(() => notification.classList.add('show'), 10);

                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        };

        // Global functions
        function openSupplierModal() {
            document.getElementById('supplierModalTitle').innerHTML = '<span><i class="fas fa-truck"></i> Add New Supplier</span><span class="close" onclick="closeSupplierModal()">&times;</span>';
            document.getElementById('supplierForm').reset();
            document.getElementById('supplierId').value = '';
            document.getElementById('supplierModal').style.display = 'block';
        }

        function closeSupplierModal() {
            document.getElementById('supplierModal').style.display = 'none';
        }

        function closeDeleteSupplierModal() {
            document.getElementById('deleteSupplierModal').style.display = 'none';
        }

        async function handleSubmit(e) {
            e.preventDefault();

            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            const id = document.getElementById('supplierId').value;
            const formData = {
                supplierName: document.getElementById('supplierName').value,
                contactPerson: document.getElementById('contactPerson').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                city: document.getElementById('city').value,
                country: document.getElementById('country').value
            };

            if (id) {
                formData.id = id;
            }

            try {
                const action = id ? 'update' : 'create';
                const response = await fetch('?ajax=1', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ...formData,
                        action
                    })
                });

                const result = await response.json();

                if (result.success) {
                    SuppliersModule.showSuccess(result.message);
                    closeSupplierModal();
                    await SuppliersModule.loadSuppliers();
                } else {
                    SuppliersModule.showError(result.message);
                }
            } catch (error) {
                console.error('Error saving supplier:', error);
                SuppliersModule.showError('Failed to save supplier. Please try again.');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }

        async function confirmDeleteSupplier() {
            const id = document.getElementById('deleteSupplierId').value;

            if (!id) return;

            try {
                const response = await fetch('?ajax=1', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id,
                        action: 'delete'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    SuppliersModule.showSuccess(result.message);
                    closeDeleteSupplierModal();
                    await SuppliersModule.loadSuppliers();
                } else {
                    SuppliersModule.showError(result.message);
                }
            } catch (error) {
                console.error('Error deleting supplier:', error);
                SuppliersModule.showError('Failed to delete supplier. Please try again.');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const supplierModal = document.getElementById('supplierModal');
            const deleteModal = document.getElementById('deleteSupplierModal');

            if (event.target === supplierModal) {
                closeSupplierModal();
            }
            if (event.target === deleteModal) {
                closeDeleteSupplierModal();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            SuppliersModule.init();
        });


        // Dashboard Auto-Refresh Module
        const DashboardRefresh = {
            isRefreshing: false,

            async refreshDashboard() {
                if (this.isRefreshing) return;

                this.isRefreshing = true;

                try {
                    const response = await fetch('get_dashboard_stats.php');
                    const data = await response.json();

                    if (data.success) {
                        // Update stat cards with animation
                        this.updateStatsWithAnimation(data.stats);

                        // Update recent orders
                        if (data.recent_orders) {
                            this.updateRecentOrders(data.recent_orders);
                        }

                        // Update most sold product
                        if (data.most_sold_product !== undefined) {
                            this.updateMostSoldProduct(data.most_sold_product);
                        }
                    }
                } catch (error) {
                    console.error('Error refreshing dashboard:', error);
                } finally {
                    this.isRefreshing = false;
                }
            },

            updateStatsWithAnimation(stats) {
                const statCards = document.querySelectorAll('.stat-card .stat-value');

                if (statCards.length >= 4) {
                    this.animateNumber(statCards[0], stats.clients);
                    this.animateNumber(statCards[1], stats.products);
                    this.animateNumber(statCards[2], stats.orders);
                    this.animateNumber(statCards[3], stats.suppliers);
                }
            },

            animateNumber(element, targetValue) {
                const currentValue = parseInt(element.textContent) || 0;
                const target = parseInt(targetValue) || 0;

                if (currentValue === target) return;

                // Add pulse animation
                element.style.animation = 'pulse 0.3s ease';

                const duration = 500; // milliseconds
                const steps = 20;
                const increment = (target - currentValue) / steps;
                const stepDuration = duration / steps;

                let current = currentValue;
                let step = 0;

                const timer = setInterval(() => {
                    step++;
                    current += increment;

                    if (step >= steps) {
                        element.textContent = target;
                        clearInterval(timer);

                        // Remove animation after complete
                        setTimeout(() => {
                            element.style.animation = '';
                        }, 300);
                    } else {
                        element.textContent = Math.round(current);
                    }
                }, stepDuration);
            },

            updateRecentOrders(orders) {
                const tbody = document.querySelector('#dashboard table tbody');
                if (!tbody) return;

                if (orders.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3"><p class="empty-state" style="text-align: center; padding: 10px;">No recent orders found.</p></td></tr>';
                    return;
                }

                tbody.innerHTML = orders.map(order => {
                    const initials = this.getInitials(order.clientFName, order.clientLName);
                    const clientName = this.escapeHtml(`${order.clientFName} ${order.clientLName}`);
                    const productName = this.escapeHtml(order.productName);
                    const status = order.orderStatus.toLowerCase();

                    return `
                <tr>
                    <td>
                        <div class="client-cell">
                            <div class="client-avatar">${initials}</div>
                            <span>${clientName}</span>
                        </div>
                    </td>
                    <td>${productName}</td>
                    <td><span class="status-badge status-${status}">${this.escapeHtml(order.orderStatus)}</span></td>
                </tr>
            `;
                }).join('');
            },

            updateMostSoldProduct(product) {
                const section = document.querySelector('#dashboard .content-section:last-child');
                if (!section) return;

                // Remove old content
                const oldContent = section.querySelector('.most-sold-product-card, .empty-state');
                if (oldContent) {
                    oldContent.remove();
                }

                if (product) {
                    const html = `
                <div class="most-sold-product-card">
                    <strong>${this.escapeHtml(product.productName)}</strong> 
                    <span class="sales-count">${Number(product.totalSold).toLocaleString()} units sold</span>
                </div>
            `;
                    section.insertAdjacentHTML('beforeend', html);
                } else {
                    section.insertAdjacentHTML('beforeend', '<div class="empty-state">No sales data available</div>');
                }
            },

            getInitials(firstName, lastName) {
                const first = firstName ? String(firstName).charAt(0).toUpperCase() : '';
                const last = lastName ? String(lastName).charAt(0).toUpperCase() : '';
                return first + last;
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        // Add pulse animation CSS
        const pulseStyle = document.createElement('style');
        pulseStyle.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); color: #f39c12; }
        100% { transform: scale(1); }
    }
`;
        document.head.appendChild(pulseStyle);

        // Hook into the existing showPage function to refresh dashboard when viewed
        const originalShowPage = window.showPage;
        window.showPage = function(pageId, pageTitle) {
            // Call original function
            if (typeof originalShowPage === 'function') {
                originalShowPage(pageId, pageTitle);
            } else {
                PageNavigation.showPage(pageId, pageTitle);
            }

            // Refresh dashboard when navigating to it
            if (pageId === 'dashboard') {
                setTimeout(() => {
                    DashboardRefresh.refreshDashboard();
                }, 100);
            }

            // Initialize tracking when viewing tracking page
            if (pageId === 'tracking') {
                setTimeout(() => TrackingModule.init(), 100);
            }
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh dashboard if it's the active page
            const dashboardPage = document.getElementById('dashboard');
            if (dashboardPage && dashboardPage.classList.contains('active')) {
                DashboardRefresh.refreshDashboard();
            }

            console.log('✅ Dashboard Auto-Refresh initialized');
        });


        // Enhanced Product Form Handler with Auto-Update
        const ProductFormHandler = {
            init() {
                this.setupFormSubmission();
            },

            setupFormSubmission() {
                const productForm = document.querySelector('form[enctype="multipart/form-data"]');

                if (productForm) {
                    productForm.addEventListener('submit', async (e) => {
                        const actionInput = productForm.querySelector('input[name="action"]');

                        // Only intercept add_product submissions
                        if (actionInput && actionInput.value === 'add_product') {
                            e.preventDefault(); // Prevent default form submission

                            await this.handleProductSubmission(productForm);
                        }
                    });
                }
            },

            async handleProductSubmission(form) {
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                // Disable submit button and show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';

                // Create FormData from the form
                const formData = new FormData(form);

                try {
                    // Submit the form via AJAX
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });

                    const html = await response.text();

                    // Parse the response to check for success
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const successAlert = doc.querySelector('.alert.success');
                    const errorAlert = doc.querySelector('.alert.error');

                    if (successAlert) {
                        // Success! Show success message
                        this.showSuccessNotification(successAlert.textContent.trim());

                        // Reset the form
                        form.reset();
                        ProductPreview.reset();

                        // Reload the product list without page refresh
                        await this.reloadProductList();

                        // Navigate back to product page
                        setTimeout(() => {
                            showPage('product', 'Product');
                        }, 1500);

                    } else if (errorAlert) {
                        // Show error message
                        this.showErrorNotification(errorAlert.textContent.trim());
                    } else {
                        this.showErrorNotification('Unknown error occurred');
                    }

                } catch (error) {
                    console.error('Error submitting product:', error);
                    this.showErrorNotification('Failed to submit product. Please try again.');
                } finally {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            },

            async reloadProductList() {
                try {
                    // Fetch fresh product list from server
                    const response = await fetch('get_products_list.php');
                    const data = await response.json();

                    if (data.success && data.products) {
                        // Update the product list container
                        this.updateProductList(data.products);

                        // Update product count
                        const productCount = data.products.length;
                        const countElement = document.getElementById('displayedProductCount');
                        if (countElement) {
                            countElement.textContent = productCount;
                        }

                        // Update the total products stat card
                        const statValue = document.querySelector('.stat-card:nth-child(2) .stat-value');
                        if (statValue) {
                            statValue.textContent = productCount;
                        }
                    }
                } catch (error) {
                    console.error('Error reloading product list:', error);
                }
            },

            updateProductList(products) {
                const productList = document.querySelector('.product-list');
                if (!productList) return;

                if (products.length === 0) {
                    productList.innerHTML = '<p class="empty-state" style="text-align: center; padding: 20px;">No products found in the database.</p>';
                    return;
                }

                productList.innerHTML = products.map(product => {
                    const displayPrice = Number(product.price).toFixed(2);
                    const stockClass = product.stockQuantity <= 5 ? 'low-stock' : 'in-stock';

                    return `
                <div class="product-item" data-product-id="${product.id}">
                    <div class="product-checkbox">
                        <input type="checkbox" class="product-checkbox-input" data-product-id="${product.id}">
                    </div>
                    <div class="product-main-info">
                        <div class="product-info-section">
                            <span class="product-label">Display Name</span>
                            <div class="product-title">${this.escapeHtml(product.displayName)}</div>
                            <span class="product-label" style="margin-top: 6px;">Brand Name</span>
                            <div class="product-subtitle">${this.escapeHtml(product.brandName)}</div>
                        </div>
                        <div class="product-info-section">
                            <span class="product-label">Price</span>
                            <div class="product-price">₱${displayPrice}</div>
                            <span class="product-label" style="margin-top: 6px;">Category</span>
                            <div class="product-subtitle">${this.escapeHtml(product.category)}</div>
                        </div>
                        <div class="product-info-section">
                            <span class="product-label">Stock</span>
                            <div class="product-price ${stockClass}">${this.escapeHtml(product.stockQuantity)}</div>
                        </div>
                    </div>
                </div>
            `;
                }).join('');

                // Re-initialize bulk actions for new checkboxes
                BulkActions.init();
            },

            showSuccessNotification(message) {
                // Create and show success notification
                const notification = document.createElement('div');
                notification.className = 'notification success-notification';
                notification.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;

                document.body.appendChild(notification);

                // Animate in
                setTimeout(() => notification.classList.add('show'), 10);

                // Remove after 3 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            },

            showErrorNotification(message) {
                // Create and show error notification
                const notification = document.createElement('div');
                notification.className = 'notification error-notification';
                notification.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;

                document.body.appendChild(notification);

                // Animate in
                setTimeout(() => notification.classList.add('show'), 10);

                // Remove after 5 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        // Add notification styles
        const notificationStyles = `
<style>
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateX(400px);
    transition: transform 0.3s ease;
    z-index: 10000;
    max-width: 400px;
}

.notification.show {
    transform: translateX(0);
}

.success-notification {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error-notification {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.notification i {
    font-size: 20px;
}
</style>
`;

        document.head.insertAdjacentHTML('beforeend', notificationStyles);

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            ProductFormHandler.init();
            console.log('✅ Product Form Handler initialized with auto-update');
        });

        // Tracking Module
        const TrackingModule = {
            trackingData: [],
            filteredData: [],

            init() {
                this.loadTracking();
                this.setupEventListeners();
            },

            setupEventListeners() {
                const searchInput = document.getElementById('trackingSearchInput');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
                }

                const statusFilter = document.getElementById('trackingStatusFilter');
                if (statusFilter) {
                    statusFilter.addEventListener('change', () => this.applyFilters());
                }

                const form = document.getElementById('updateTrackingForm');
                if (form) {
                    form.addEventListener('submit', (e) => this.handleUpdateTracking(e));
                }

                const statusSelect = document.getElementById('trackingOrderStatus');
                const locationSelect = document.getElementById('trackingCurrentLocation');
                const customLocation = document.getElementById('trackingCustomLocation');
                const description = document.getElementById('trackingDescription');

                if (statusSelect) statusSelect.addEventListener('change', () => this.updatePreview());
                if (locationSelect) locationSelect.addEventListener('change', () => this.updatePreview());
                if (customLocation) customLocation.addEventListener('input', () => this.updatePreview());
                if (description) description.addEventListener('input', () => this.updatePreview());
            },

            async loadTracking() {
                const container = document.getElementById('trackingCardsContainer');
                const loading = document.getElementById('trackingLoading');

                if (loading) loading.style.display = 'block';

                try {
                    const response = await fetch('../../controllers/get_tracking.php');
                    const data = await response.json();

                    if (data.success) {
                        this.trackingData = data.tracking;
                        this.filteredData = [...this.trackingData];
                        this.renderTracking();
                        this.updateStats();
                    } else {
                        this.showEmptyState(data.message);
                    }
                } catch (error) {
                    console.error('Error loading tracking:', error);
                    this.showError();
                } finally {
                    if (loading) loading.style.display = 'none';
                }
            },

            renderTracking() {
                const container = document.getElementById('trackingCardsContainer');
                if (!container) return;

                if (this.filteredData.length === 0) {
                    this.showEmptyState('No tracking data found');
                    return;
                }

                container.innerHTML = this.filteredData.map(order => this.createTrackingCard(order)).join('');
            },

            createTrackingCard(order) {
                const statusClass = order.order_status.replace(/_/g, '-');
                const statusText = this.getStatusText(order.order_status);
                const date = new Date(order.created_at).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });

                return `
            <div class="tracking-card" style="border-left-color: ${this.getStatusColor(order.order_status)}">
                <div class="tracking-card-header">
                    <div class="tracking-card-info">
                        <h3>${this.escapeHtml(order.order_reference)}</h3>
                        <div class="tracking-meta">
                            <span><i class="fas fa-user"></i> ${this.escapeHtml(order.customer_name)}</span>
                            <span><i class="fas fa-calendar"></i> ${date}</span>
                            ${order.tracking_number ? `<span><i class="fas fa-truck"></i> ${this.escapeHtml(order.tracking_number)}</span>` : ''}
                        </div>
                    </div>
                    <span class="tracking-status-badge status-${statusClass}">
                        ${statusText}
                    </span>
                </div>
                
                <div class="tracking-card-body">
                    <div class="tracking-timeline">
                        ${this.createTimeline(order)}
                    </div>
                    
                    <div class="tracking-card-sidebar">
                        <div class="sidebar-section">
                            <h4>Customer Details</h4>
                            <p><i class="fas fa-envelope"></i> ${this.escapeHtml(order.customer_email)}</p>
                            <p><i class="fas fa-phone"></i> ${this.escapeHtml(order.customer_phone)}</p>
                        </div>
                        
                        ${order.current_location ? `
                            <div class="sidebar-section">
                                <h4>Current Location</h4>
                                <p><i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(order.current_location)}</p>
                            </div>
                        ` : ''}
                        
                        ${order.estimated_delivery ? `
                            <div class="sidebar-section">
                                <h4>Estimated Delivery</h4>
                                <p><i class="fas fa-calendar-check"></i> ${new Date(order.estimated_delivery).toLocaleDateString()}</p>
                            </div>
                        ` : ''}
                        
                        <div class="sidebar-section">
                            <h4>Total Amount</h4>
                            <p><strong>₱${parseFloat(order.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></p>
                        </div>
                    </div>
                </div>
                
                <div class="tracking-card-actions">
                    <button class="btn-track-action btn-update-tracking" onclick="TrackingModule.openUpdateModal(${order.id})">
                        <i class="fas fa-edit"></i> Update Tracking
                    </button>
                </div>
            </div>
        `;
            },

            createTimeline(order) {
                const statuses = ['pending', 'confirmed', 'preparing', 'ready_to_ship', 'in_transit', 'out_for_delivery', 'delivered'];
                const currentIndex = statuses.indexOf(order.order_status);

                return statuses.slice(0, currentIndex + 1).map((status, index) => {
                    const isActive = index === currentIndex;
                    const isCompleted = index < currentIndex;
                    const stepClass = isActive ? 'active' : (isCompleted ? 'completed' : '');

                    return `
                <div class="timeline-step ${stepClass}">
                    <div class="timeline-time">
                        ${new Date(order.created_at).toLocaleString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </div>
                    <div class="timeline-title">${this.getStatusText(status)}</div>
                    ${order.current_location && isActive ? `
                        <div class="timeline-location">
                            <i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(order.current_location)}
                        </div>
                    ` : ''}
                </div>
            `;
                }).join('');
            },

            getStatusText(status) {
                const statusMap = {
                    pending: 'Order Pending',
                    confirmed: 'Order Confirmed',
                    preparing: 'Preparing Order',
                    ready_to_ship: 'Ready to Ship',
                    in_transit: 'In Transit',
                    out_for_delivery: 'Out for Delivery',
                    delivered: 'Delivered',
                    cancelled: 'Cancelled'
                };
                return statusMap[status] || status;
            },

            getStatusColor(status) {
                const colorMap = {
                    pending: '#95a5a6',
                    confirmed: '#3498db',
                    preparing: '#f39c12',
                    ready_to_ship: '#9b59b6',
                    in_transit: '#9b59b6',
                    out_for_delivery: '#27ae60',
                    delivered: '#27ae60',
                    cancelled: '#e74c3c'
                };
                return colorMap[status] || '#95a5a6';
            },

            updateStats() {
                const total = this.trackingData.length;
                const inTransit = this.trackingData.filter(o => o.order_status === 'in_transit').length;
                const outForDelivery = this.trackingData.filter(o => o.order_status === 'out_for_delivery').length;

                const today = new Date().toDateString();
                const deliveredToday = this.trackingData.filter(o => {
                    return o.order_status === 'delivered' &&
                        o.delivered_at &&
                        new Date(o.delivered_at).toDateString() === today;
                }).length;

                document.getElementById('trackingTotalOrders').textContent = total;
                document.getElementById('trackingInTransit').textContent = inTransit;
                document.getElementById('trackingOutForDelivery').textContent = outForDelivery;
                document.getElementById('trackingDeliveredToday').textContent = deliveredToday;
            },

            handleSearch(searchTerm) {
                const term = searchTerm.toLowerCase();
                this.filteredData = this.trackingData.filter(order => {
                    return order.order_reference.toLowerCase().includes(term) ||
                        order.customer_name.toLowerCase().includes(term) ||
                        (order.tracking_number && order.tracking_number.toLowerCase().includes(term));
                });
                this.applyFilters();
            },

            applyFilters() {
                const statusFilter = document.getElementById('trackingStatusFilter').value;

                if (statusFilter) {
                    this.filteredData = this.filteredData.filter(o => o.order_status === statusFilter);
                }

                this.renderTracking();
            },

            openUpdateModal(orderId) {
                // 1. Ensure we find the order even if ID is a string or number
                const order = this.trackingData.find(o => o.id == orderId);
                if (!order) {
                    console.error("Order not found ID:", orderId);
                    return;
                }

                // 2. Populate fields
                document.getElementById('trackingOrderId').value = order.id;
                document.getElementById('trackingOrderRef').value = order.order_reference;
                document.getElementById('trackingCustomerName').value = order.customer_name;
                document.getElementById('trackingOrderStatus').value = order.order_status;
                document.getElementById('trackingPaymentStatus').value = order.payment_status;
                document.getElementById('trackingCurrentLocation').value = order.current_location || '';
                document.getElementById('trackingNumber').value = order.tracking_number || '';
                document.getElementById('trackingEstimatedDelivery').value = order.estimated_delivery || '';

                // 3. SHOW MODAL - Use the ID defined in your HTML
                const modal = document.getElementById('updateTrackingModal');
                if (modal) {
                    modal.style.display = 'flex';
                    // Add a class to body to prevent scrolling background if desired
                    document.body.style.overflow = 'hidden';
                }

                this.updatePreview();
            },

            closeUpdateModal() {
                const modal = document.getElementById('updateTrackingModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                document.getElementById('updateTrackingForm').reset();
            },

            updatePreview() {
                const status = document.getElementById('trackingOrderStatus').value;
                const location = document.getElementById('trackingCustomLocation').value ||
                    document.getElementById('trackingCurrentLocation').value;
                const description = document.getElementById('trackingDescription').value;

                const statusTitle = this.getStatusText(status);
                const statusColor = this.getStatusColor(status);

                document.getElementById('previewStatusTitle').textContent = statusTitle;
                document.getElementById('previewLocation').textContent = location || 'No location selected';
                document.getElementById('previewDescription').textContent = description || 'Enter a description above...';

                const previewIcon = document.querySelector('.preview-icon');
                if (previewIcon) {
                    previewIcon.style.background = statusColor;
                }
            },

            async handleUpdateTracking(e) {
                e.preventDefault();

                const formData = {
                    order_id: document.getElementById('trackingOrderId').value,
                    order_status: document.getElementById('trackingOrderStatus').value,
                    payment_status: document.getElementById('trackingPaymentStatus').value,
                    current_location: document.getElementById('trackingCustomLocation').value ||
                        document.getElementById('trackingCurrentLocation').value,
                    tracking_number: document.getElementById('trackingNumber').value,
                    estimated_delivery: document.getElementById('trackingEstimatedDelivery').value,
                    description: document.getElementById('trackingDescription').value,
                    send_notification: document.getElementById('trackingSendNotification').checked
                };

                const submitBtn = e.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('../../controllers/staff_update_tracking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Tracking updated successfully!');
                        this.closeUpdateModal();
                        this.loadTracking();
                    } else {
                        alert(result.message || 'Failed to update tracking');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating tracking');
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            },

            showEmptyState(message) {
                const container = document.getElementById('trackingCardsContainer');
                container.innerHTML = `
            <div class="tracking-empty">
                <i class="fas fa-map-marked-alt"></i>
                <h3>No Tracking Information</h3>
                <p>${message || 'There are no orders to track yet.'}</p>
            </div>
        `;
            },

            showError() {
                const container = document.getElementById('trackingCardsContainer');
                container.innerHTML = `
            <div class="tracking-empty">
                <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                <h3>Error Loading Tracking</h3>
                <p>Failed to load tracking information. Please try again.</p>
            </div>
        `;
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        const ClientsModule = {
            init: function() {
                // Load data initially
                this.loadClients();

                // Add search functionality
                document.getElementById('clientSearch')?.addEventListener('input', (e) => {
                    this.filterClients(e.target.value);
                });
            },

            loadClients: function() {
                const tbody = document.getElementById('clientsTableBody');
                tbody.innerHTML = '<tr><td colspan="5">Loading clients...</td></tr>';

                fetch('dashboard.php?ajax=1&action=fetch_clients')
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            this.allClients = res.data; // Store for filtering
                            this.renderTable(this.allClients);
                        }
                    })
                    .catch(err => console.error('Error:', err));
            },

            renderTable: function(clients) {
                const tbody = document.getElementById('clientsTableBody');
                if (!clients || clients.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5">No clients found.</td></tr>';
                    return;
                }

                tbody.innerHTML = clients.map(client => `
            <tr>
                <td><strong>${client.customer_name}</strong></td>
                <td>${client.customer_email}</td>
                <td>${client.customer_phone}</td>
                <td>${client.customer_address}</td>
                <td><span class="badge">${client.total_orders} Orders</span></td>
            </tr>
        `).join('');
            },

            filterClients: function(searchTerm) {
                const term = searchTerm.toLowerCase();
                const filtered = this.allClients.filter(c =>
                    c.customer_name.toLowerCase().includes(term) ||
                    c.customer_email.toLowerCase().includes(term)
                );
                this.renderTable(filtered);
            }
        };

        // Initialize when the script loads
        document.addEventListener('DOMContentLoaded', () => ClientsModule.init());

        const OrdersModule = {
            loadOrders: function() {
                const tbody = document.querySelector('.orders-table tbody');
                tbody.innerHTML = '<tr><td colspan="7">Loading orders...</td></tr>';

                fetch('dashboard.php?ajax=1&action=fetch_orders')
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            this.renderOrders(res.data);
                        } else {
                            tbody.innerHTML = '<tr><td colspan="7">Error loading data.</td></tr>';
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        tbody.innerHTML = '<tr><td colspan="7">Server connection failed.</td></tr>';
                    });
            },

            renderOrders: function(orders) {
                const tbody = document.querySelector('.orders-table tbody');
                if (orders.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7">No orders found.</td></tr>';
                    return;
                }

                tbody.innerHTML = orders.map(order => `
            <tr>
                <td>#${order.order_reference}</td>
                <td>${order.customer_name}</td>
                <td>₱${parseFloat(order.total_amount).toLocaleString()}</td>
                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                <td><span class="badge payment">${order.payment_method}</span></td>
                <td><span class="status-tag ${order.order_status.toLowerCase()}">${order.order_status}</span></td>
            </tr>
        `).join('');
            }
        };

        // Initial load when page is ready
        document.addEventListener('DOMContentLoaded', () => OrdersModule.loadOrders());

        // Initialize tracking when page is shown
        const originalShowPageFunc = window.showPage;
        window.showPage = function(pageId, pageTitle) {
            if (typeof originalShowPageFunc === 'function') {
                originalShowPageFunc(pageId, pageTitle);
            } else {
                PageNavigation.showPage(pageId, pageTitle);
            }

            if (pageId === 'tracking') {
                setTimeout(() => TrackingModule.init(), 100);
            }
        };

        // USER DROPDOWN MODULE
        const UserDropdown = {
            init() {
                const userAvatar = document.querySelector('.user-avatar');
                const dropdown = document.getElementById('userDropdown');

                if (userAvatar && dropdown) {
                    userAvatar.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.classList.toggle('active');
                    });

                    document.addEventListener('click', (e) => {
                        if (!userAvatar.contains(e.target) && !dropdown.contains(e.target)) {
                            dropdown.classList.remove('active');
                        }
                    });
                }
            }
        };

        function openEditInfoModal() {
            document.getElementById('editInfoModal').classList.add('show');
        }

        function closeEditInfoModal() {
            document.getElementById('editInfoModal').classList.remove('show');
        }

        function openChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.add('show');
        }

        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').classList.remove('show');
        }

        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function handleUpdateInfo(event) {
            event.preventDefault();

            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const contact = document.getElementById('contactNumber').value;

            // Update display
            document.getElementById('displayFirstName').textContent = firstName;
            document.getElementById('displayLastName').textContent = lastName;
            document.getElementById('displayEmail').textContent = email;
            document.getElementById('displayContact').textContent = contact;

            // Update header
            document.querySelector('.profile-name').textContent = `${firstName} ${lastName}`;
            document.querySelector('.profile-avatar-large').textContent =
                `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();

            // Here you would send to your backend
            console.log('Updating info:', {
                firstName,
                lastName,
                email,
                contact
            });

            alert('Information updated successfully!');
            closeEditInfoModal();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editInfoModal');
            const passwordModal = document.getElementById('changePasswordModal');

            if (event.target === editModal) {
                closeEditInfoModal();
            }
            if (event.target === passwordModal) {
                closeChangePasswordModal();
            }
        }

        // PAGE NAVIGATION MODULE
        const PageNavigation = {
            showPage(pageId, pageTitle) {
                document.querySelectorAll('.page-content').forEach(page => {
                    page.classList.remove('active');
                });

                document.querySelectorAll('.menu-item').forEach(item => {
                    item.classList.remove('active');
                });

                const targetPage = document.getElementById(pageId);
                if (targetPage) {
                    targetPage.classList.add('active');
                }

                const pageTitle_elem = document.getElementById('page-title');
                if (pageTitle_elem) {
                    pageTitle_elem.textContent = pageTitle;
                }

                const menuItem = Array.from(document.querySelectorAll('.menu-item')).find(item => {
                    const match = item.getAttribute('onclick')?.match(/showPage\('([^']+)'/);
                    return match && match[1] === pageId;
                });

                if (menuItem) {
                    menuItem.classList.add('active');
                }
            }
        };

        function showPage(pageId, pageTitle) {
            PageNavigation.showPage(pageId, pageTitle);
        }

        // PRODUCT MODAL FUNCTIONS
        // Product Filtering Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const productCards = document.querySelectorAll('.product-card');
            const productCountElement = document.getElementById('displayedProductCount');
            const searchInput = document.getElementById('productSearchInput');

            let currentCategory = 'all';
            let currentSearchTerm = '';

            // Make product cards clickable to edit
            productCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't open modal if clicking checkbox
                    if (e.target.type === 'checkbox') {
                        return;
                    }
                    const productId = this.getAttribute('data-product-id');
                    openEditModal(productId);
                });
                card.style.cursor = 'pointer';
            });

            // Load first image for each product
            loadProductImages();

            // Filter button click handler
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Get selected category
                    currentCategory = this.getAttribute('data-category');

                    // Apply filters
                    applyFilters();
                });
            });

            // Search input handler
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentSearchTerm = this.value.toLowerCase().trim();
                    applyFilters();
                });
            }

            // Main filter function
            function applyFilters() {
                let visibleCount = 0;

                productCards.forEach(card => {
                    const productContent = card.querySelector('.product-content');
                    const productTitle = productContent.querySelector('.product-title').textContent.toLowerCase();
                    const productBrand = productContent.querySelector('.product-brand').textContent.toLowerCase();
                    const productCategory = productContent.querySelector('.product-category').textContent.trim();

                    // Check category match
                    let categoryMatch = false;

                    if (currentCategory === 'all') {
                        categoryMatch = true;
                    } else if (currentCategory === 'Package Setup') {
                        // For Package Setup, only show products with "Hybrid" or "Grid-tie" brand
                        categoryMatch = productBrand.includes('hybrid') || productBrand.includes('grid-tie');
                    } else {
                        categoryMatch = productCategory === currentCategory;
                    }

                    // Check search match
                    const searchMatch = currentSearchTerm === '' ||
                        productTitle.includes(currentSearchTerm) ||
                        productBrand.includes(currentSearchTerm);

                    // Show/hide card based on both filters
                    if (categoryMatch && searchMatch) {
                        card.style.display = 'grid';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Update product count
                updateProductCount(visibleCount);
            }

            // Update product count display
            function updateProductCount(count) {
                if (productCountElement) {
                    productCountElement.textContent = count;

                    // Get the parent paragraph element
                    const countContainer = productCountElement.parentElement;

                    // Get all text nodes
                    const textNodes = Array.from(countContainer.childNodes).filter(node => node.nodeType === 3);

                    // Update the plural text (the text node after the span)
                    if (textNodes.length > 0) {
                        const pluralText = count === 1 ? ' product' : ' products';
                        textNodes[textNodes.length - 1].textContent = pluralText;
                    }
                }
            }

            // Optional: Filter icon click handler (can be used to toggle filter bar visibility)
            const filterIcon = document.querySelector('.filter-icon');
            const filterBar = document.querySelector('.filter-bar');

            if (filterIcon && filterBar) {
                filterIcon.addEventListener('click', function() {
                    filterBar.style.display = filterBar.style.display === 'none' ? 'flex' : 'none';
                });
            }
        });

        // Optional: Reset filters function
        function resetFilters() {
            const searchInput = document.getElementById('productSearchInput');
            const allButton = document.querySelector('.filter-btn[data-category="all"]');

            if (searchInput) {
                searchInput.value = '';
            }

            if (allButton) {
                allButton.click();
            }
        }

        // Open edit modal and fetch product data
        let imagesToDelete = [];

        async function openEditModal(productId) {
            imagesToDelete = []; // Reset deletion array

            try {
                const response = await fetch(`get.product.php?id=${productId}`);
                const data = await response.json();

                if (data.success) {
                    const product = data.product;

                    // Populate form fields
                    document.getElementById('editProductId').value = product.id;
                    document.getElementById('editDisplayName').value = product.displayName;
                    document.getElementById('editBrandName').value = product.brandName;
                    document.getElementById('editPrice').value = product.price;
                    document.getElementById('editCategory').value = product.category;
                    document.getElementById('editStockQuantity').value = product.stockQuantity;
                    document.getElementById('editWarranty').value = product.warranty || '';
                    document.getElementById('editDescription').value = product.description || '';

                    // Load product images
                    loadProductImages(product.images);

                    // Show modal
                    document.getElementById('editProductModal').style.display = 'block';
                } else {
                    alert('Error loading product details: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load product details. Please try again.');
            }
        }

        // Load product images into the modal
        function loadProductImages(images) {
            const container = document.getElementById('currentImagesContainer');
            container.innerHTML = '';

            if (!images || images.length === 0) {
                container.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">No images uploaded yet</p>';
                return;
            }

            images.forEach(image => {
                const imageItem = document.createElement('div');
                imageItem.className = 'image-item';
                imageItem.dataset.imageId = image.id;

                imageItem.innerHTML = `
            <img src="../../${image.image_path}" alt="Product image">
            <button type="button" class="delete-image-btn" onclick="markImageForDeletion(${image.id}, this)">
                <i class="fas fa-times"></i>
            </button>
        `;

                container.appendChild(imageItem);
            });
        }

        // Mark image for deletion
        function markImageForDeletion(imageId, button) {
            const imageItem = button.closest('.image-item');

            if (imagesToDelete.includes(imageId)) {
                // Unmark for deletion
                imagesToDelete = imagesToDelete.filter(id => id !== imageId);
                imageItem.classList.remove('marked-for-deletion');
                button.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                // Mark for deletion
                imagesToDelete.push(imageId);
                imageItem.classList.add('marked-for-deletion');
                button.innerHTML = '<i class="fas fa-undo"></i>';
            }

            // Update hidden input
            document.getElementById('deleteImagesInput').value = imagesToDelete.join(',');
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const editModal = document.getElementById('editProductModal');
            if (event.target === editModal) {
                closeEditModal();
            }
        });

        // Handle edit form submission
        const editForm = document.getElementById('editProductForm');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                try {
                    const response = await fetch('edit_product.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Product updated successfully!');
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Error updating product: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to update product. Please try again.');
                }
            });
        }

        // Load first image for each product from product_images table
        async function loadProductImages() {
            productCards.forEach(async (card) => {
                const productId = card.getAttribute('data-product-id');
                const imageElement = card.querySelector('.product-image img');

                if (productId && imageElement) {
                    try {
                        // Fetch the first image for this product
                        const response = await fetch(`get_product_image.php?product_id=${productId}`);
                        const data = await response.json();

                        if (data.success && data.image_path) {
                            imageElement.src = '../' + data.image_path;
                        } else {
                            // Keep the placeholder if no image found
                            imageElement.src = '../assets/img/product-placeholder.png';
                        }
                    } catch (error) {
                        console.error('Error loading image for product ' + productId, error);
                        imageElement.src = '../assets/img/product-placeholder.png';
                    }
                }
            });
        }

        function closeEditModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editProductModal');
            const deleteModal = document.getElementById('deleteProductModal');
            const bulkDeleteModal = document.getElementById('bulkDeleteModal');

            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
            if (event.target === bulkDeleteModal) {
                closeBulkDeleteModal();
            }
        }

        function markImageForDeletion(imageFilename, button) {
            if (confirm('Delete this image?')) {
                const imageItem = button.closest('.edit-image-item');
                imageItem.style.opacity = '0.3';

                // Remove the keep input and add delete input
                const keepInput = imageItem.querySelector('input[name="keep_images[]"]');
                if (keepInput) {
                    keepInput.remove();
                }

                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_images[]';
                deleteInput.value = imageFilename;
                imageItem.appendChild(deleteInput);

                button.innerHTML = '<i class="fas fa-undo"></i>';
                button.onclick = () => undoImageDeletion(imageFilename, button);
            }
        }

        function undoImageDeletion(imageFilename, button) {
            const imageItem = button.closest('.edit-image-item');
            imageItem.style.opacity = '1';

            // Remove delete input and restore keep input
            const deleteInput = imageItem.querySelector('input[name="delete_images[]"]');
            if (deleteInput) {
                deleteInput.remove();
            }

            const keepInput = document.createElement('input');
            keepInput.type = 'hidden';
            keepInput.name = 'keep_images[]';
            keepInput.value = imageFilename;
            imageItem.appendChild(keepInput);

            button.innerHTML = '<i class="fas fa-trash"></i>';
            button.onclick = () => markImageForDeletion(imageFilename, button);
        }

        // Handle new images preview
        document.addEventListener('DOMContentLoaded', function() {
            const newImagesInput = document.getElementById('editNewImages');
            if (newImagesInput) {
                newImagesInput.addEventListener('change', function(e) {
                    const previewDiv = document.getElementById('editNewImagesPreview');
                    previewDiv.innerHTML = '';

                    const files = e.target.files;
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            const preview = document.createElement('div');
                            preview.className = 'new-image-preview';
                            preview.innerHTML = `<img src="${e.target.result}" alt="New ${i + 1}">`;
                            previewDiv.appendChild(preview);
                        };

                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        function closeEditModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }

        function openDeleteModal(productId) {
            document.getElementById('deleteProductId').value = productId;
            document.getElementById('deleteProductModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteProductModal').style.display = 'none';
        }

        function closeBulkDeleteModal() {
            document.getElementById('bulkDeleteModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editProductModal');
            const deleteModal = document.getElementById('deleteProductModal');
            const bulkDeleteModal = document.getElementById('bulkDeleteModal');

            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
            if (event.target === bulkDeleteModal) {
                closeBulkDeleteModal();
            }
        }

        // BULK ACTIONS MODULE
        const BulkActions = {
            init() {
                this.checkboxes = document.querySelectorAll('.product-checkbox-input');
                this.bulkActionsBar = document.getElementById('bulkActionsBar');
                this.selectedCountSpan = document.getElementById('selectedCount');
                this.deselectAllBtn = document.getElementById('deselectAllBtn');
                this.bulkEditBtn = document.getElementById('bulkEditBtn');
                this.bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

                this.checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', () => this.updateBulkActions());
                });

                if (this.deselectAllBtn) {
                    this.deselectAllBtn.addEventListener('click', () => this.deselectAll());
                }

                if (this.bulkEditBtn) {
                    this.bulkEditBtn.addEventListener('click', () => this.handleBulkEdit());
                }

                if (this.bulkDeleteBtn) {
                    this.bulkDeleteBtn.addEventListener('click', () => this.showBulkDeleteModal());
                }
            },

            updateBulkActions() {
                const selectedCheckboxes = document.querySelectorAll('.product-checkbox-input:checked');
                const count = selectedCheckboxes.length;

                if (count > 0) {
                    this.bulkActionsBar.style.display = 'flex';
                    this.selectedCountSpan.textContent = count;
                    this.highlightSelectedProducts();
                } else {
                    this.bulkActionsBar.style.display = 'none';
                    this.removeAllHighlights();
                }
            },

            highlightSelectedProducts() {
                document.querySelectorAll('.product-item').forEach(item => {
                    const checkbox = item.querySelector('.product-checkbox-input');
                    if (checkbox && checkbox.checked) {
                        item.classList.add('selected');
                    } else {
                        item.classList.remove('selected');
                    }
                });
            },

            removeAllHighlights() {
                document.querySelectorAll('.product-item').forEach(item => {
                    item.classList.remove('selected');
                });
            },

            deselectAll() {
                this.checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                this.updateBulkActions();
            },

            handleBulkEdit() {
                const selectedCheckboxes = document.querySelectorAll('.product-checkbox-input:checked');
                const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.productId);

                if (selectedIds.length === 1) {
                    openEditModal(selectedIds[0]);
                } else {
                    alert(`Bulk editing ${selectedIds.length} products at once is not supported yet. Please select only one product to edit.`);
                }
            },

            showBulkDeleteModal() {
                const selectedCheckboxes = document.querySelectorAll('.product-checkbox-input:checked');
                const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.productId);

                document.getElementById('bulkDeleteCount').textContent = selectedIds.length;
                document.getElementById('bulkDeleteProductIds').value = selectedIds.join(',');
                document.getElementById('bulkDeleteModal').style.display = 'block';
            }
        };

        const QuotationModule = {
            quotations: [],
            filteredQuotations: [],
            deleteQuotationId: null,
            officers: [],

            async init() {
                await this.loadOfficers(); // Load officers first
                this.loadFromDatabase();
                this.attachEventListeners();
            },

            async loadFromDatabase() {
                try {
                    const response = await fetch('quotation_api.php?action=fetch');
                    const result = await response.json();

                    if (result.success) {
                        this.quotations = result.data;
                        this.filteredQuotations = [...this.quotations];
                        this.renderTable();
                    } else {
                        console.error('Failed to load quotations:', result.error);
                        this.showError('Failed to load quotations from database');
                    }
                } catch (error) {
                    console.error('Error loading quotations:', error);
                    this.showError('Error connecting to database');
                }
            },

            attachEventListeners() {
                // Search and filters
                document.getElementById('quotationSearch')?.addEventListener('input', () => this.filterTable());
                document.getElementById('systemTypeFilter')?.addEventListener('change', () => this.filterTable());
                document.getElementById('officerFilter')?.addEventListener('change', () => this.filterTable());
                document.getElementById('quotationStatusFilter')?.addEventListener('change', () => this.filterTable());

                // Form submission
                document.getElementById('quotationForm')?.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.saveQuotation();
                });
            },

            renderTable() {
                const tbody = document.getElementById('quotationTableBody');
                if (!tbody) return;

                tbody.innerHTML = '';

                if (this.filteredQuotations.length === 0) {
                    tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="empty-state">No quotations found</td>
                </tr>
            `;
                    this.updateStats();
                    return;
                }

                this.filteredQuotations.forEach(q => {
                    const row = document.createElement('tr');
                    // Use officer_display_name instead of just officer
                    const officerName = q.officer_display_name || q.officer || 'Unknown';

                    row.innerHTML = `
                <td><strong>${this.escapeHtml(q.quotation_number)}</strong></td>
                <td><strong>${this.escapeHtml(q.client_name)}</strong></td>
                <td><strong>${this.escapeHtml(q.email)}</strong></td>
                <td><strong>${this.escapeHtml(q.contact)}</strong></td>
                <td>${this.escapeHtml(q.location || '')}</td>
                <td><span class="quotation-badge badge-${q.system_type.toLowerCase().replace(/ /g, '-')}">${q.  system_type}</span></td>
                <td>${q.kw || '-'}</td>
                <td><span class="quotation-badge badge-${q.officer.toLowerCase()}">${this.escapeHtml(officerName)}  </span></td>
                <td><span class="quotation-badge badge-${q.status.toLowerCase()}">${q.status}</span></td>
                <td style="max-width: 200px; font-size: 11px;">${this.escapeHtml(q.remarks || '')}</td>
                <td class="quotation-actions">
                    <button class="btn-small-action btn-edit-quotation" onclick="QuotationModule.editQuotation(${q. id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn-small-action btn-delete-quotation" onclick="QuotationModule.showDeleteModal  (${q.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            `;
                    tbody.appendChild(row);
                });

                this.updateStats();
            },

            updateStats() {
                document.getElementById('totalQuotations').textContent = this.quotations.length;
                document.getElementById('hybridCount').textContent = this.quotations.filter(q => q.system_type === 'HYBRID').length;
                document.getElementById('supplyCount').textContent = this.quotations.filter(q => q.system_type === 'SUPPLY ONLY').length;
                document.getElementById('gridTieCount').textContent = this.quotations.filter(q => q.system_type === 'GRID TIE HYBRID').length;
            },

            filterTable() {
                const search = document.getElementById('quotationSearch')?.value.toLowerCase() || '';
                const systemType = document.getElementById('systemTypeFilter')?.value || '';
                const officer = document.getElementById('officerFilter')?.value || '';
                const status = document.getElementById('quotationStatusFilter')?.value || '';

                this.filteredQuotations = this.quotations.filter(q => {
                    const matchSearch = (q.client_name || '').toLowerCase().includes(search) ||
                        (q.location || '').toLowerCase().includes(search) ||
                        (q.remarks || '').toLowerCase().includes(search);
                    const matchSystem = !systemType || q.system_type === systemType;
                    const matchOfficer = !officer || q.officer === officer;
                    const matchStatus = !status || q.status === status;

                    return matchSearch && matchSystem && matchOfficer && matchStatus;
                });

                this.renderTable();
            },

            editQuotation(id) {
                const quotation = this.quotations.find(q => q.id === id);
                if (!quotation) {
                    this.showError('Quotation not found');
                    return;
                }

                console.log('Editing quotation:', quotation); // Debug log

                // Update modal title
                const modalTitle = document.getElementById('quotationModalTitle');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="fas fa-edit"></i> Edit Quotation';
                }

                // Set form values with null checks
                const quotationIdInput = document.getElementById('quotationId');
                const clientNameInput = document.getElementById('clientName');
                const emailInput = document.getElementById('email');
                const contactInput = document.getElementById('contact');
                const locationInput = document.getElementById('location');
                const systemTypeInput = document.getElementById('systemType');
                const kwInput = document.getElementById('kw');
                const officerInput = document.getElementById('officer');
                const statusInput = document.getElementById('status');
                const remarksInput = document.getElementById('remarks');

                if (quotationIdInput) quotationIdInput.value = quotation.id;
                if (clientNameInput) clientNameInput.value = quotation.client_name || '';
                if (emailInput) emailInput.value = quotation.email || '';
                if (contactInput) contactInput.value = quotation.contact || '';
                if (locationInput) locationInput.value = quotation.location || '';
                if (systemTypeInput) systemTypeInput.value = quotation.system_type || '';
                if (kwInput) kwInput.value = quotation.kw || '';
                if (officerInput) officerInput.value = quotation.officer || '';
                if (statusInput) statusInput.value = quotation.status || '';
                if (remarksInput) remarksInput.value = quotation.remarks || '';

                // Show the modal with explicit display
                const modal = document.getElementById('quotationModal');
                if (modal) {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    console.log('Modal should be visible now'); // Debug log
                } else {
                    console.error('Modal element not found!');
                }
            },

            closeQuotationModal() {
                const modal = document.getElementById('quotationModal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                }

                const form = document.getElementById('quotationForm');
                if (form) form.reset();

                const quotationId = document.getElementById('quotationId');
                if (quotationId) quotationId.value = '';

                const modalTitle = document.getElementById('quotationModalTitle');
                if (modalTitle) {
                    modalTitle.innerHTML = '<i class="fas fa-file-invoice"></i> New Quotation';
                }
            },

            async saveQuotation() {
                const id = document.getElementById('quotationId').value;
                const formData = new FormData();

                formData.append('action', id ? 'update' : 'create');
                if (id) formData.append('id', id);
                formData.append('clientName', document.getElementById('clientName').value);
                formData.append('email', document.getElementById('email').value);
                formData.append('contact', document.getElementById('contact').value);
                formData.append('location', document.getElementById('location').value);
                formData.append('systemType', document.getElementById('systemType').value);
                formData.append('kw', document.getElementById('kw').value);
                formData.append('officer', document.getElementById('officer').value);
                formData.append('status', document.getElementById('status').value);
                formData.append('remarks', document.getElementById('remarks').value);

                try {
                    const response = await fetch('quotation_api.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.closeQuotationModal();
                        this.loadFromDatabase();
                        this.showSuccess(id ? 'Quotation updated successfully' : 'Quotation created successfully');
                    } else {
                        this.showError(result.error || 'Failed to save quotation');
                    }
                } catch (error) {
                    console.error('Error saving quotation:', error);
                    this.showError('Error connecting to server');
                }
            },

            showDeleteModal(id) {
                this.deleteQuotationId = id;
                document.getElementById('deleteQuotationModal').style.display = 'block';
            },

            async init() {
                await this.loadOfficers(); // Load officers first
                this.loadFromDatabase();
                this.attachEventListeners();
            },

            async loadOfficers() {
                try {
                    const response = await fetch('quotation_api.php?action=fetch_officers');
                    const result = await response.json();

                    if (result.success) {
                        this.officers = result.data;
                        const officerSelect = document.getElementById('officer');
                        if (officerSelect) {
                            // Clear existing options except the first one
                            officerSelect.innerHTML = '<option value="">Select Officer</option>';

                            // Add officers from database
                            result.data.forEach(officer => {
                                const option = document.createElement('option');
                                option.value = officer.code;
                                option.textContent = officer.name;
                                officerSelect.appendChild(option);
                            });
                        }

                        // Also populate filter dropdown
                        const officerFilter = document.getElementById('officerFilter');
                        if (officerFilter) {
                            const currentValue = officerFilter.value;
                            officerFilter.innerHTML = '<option value="">All Officers</option>';

                            result.data.forEach(officer => {
                                const option = document.createElement('option');
                                option.value = officer.code;
                                option.textContent = officer.name;
                                officerFilter.appendChild(option);
                            });

                            officerFilter.value = currentValue;
                        }
                    }
                } catch (error) {
                    console.error('Error loading officers:', error);
                }
            },

            async confirmDeleteQuotation() {
                if (!this.deleteQuotationId) return;

                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', this.deleteQuotationId);

                try {
                    const response = await fetch('quotation_api.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.closeDeleteQuotationModal();
                        this.loadFromDatabase();
                        this.showSuccess('Quotation deleted successfully');
                    } else {
                        this.showError(result.error || 'Failed to delete quotation');
                    }
                } catch (error) {
                    console.error('Error deleting quotation:', error);
                    this.showError('Error connecting to server');
                }
            },

            closeQuotationModal() {
                document.getElementById('quotationModal').style.display = 'none';
                document.getElementById('quotationForm').reset();
                document.getElementById('quotationId').value = '';
            },

            closeDeleteQuotationModal() {
                document.getElementById('deleteQuotationModal').style.display = 'none';
                this.deleteQuotationId = null;
            },

            showSuccess(message) {
                alert(message); // Replace with better notification system
            },

            showError(message) {
                alert('Error: ' + message); // Replace with better notification system
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        // Global functions
        function openQuotationModal() {
            document.getElementById('quotationModalTitle').innerHTML = '<i class="fas fa-file-invoice"></i> New Quotation';
            document.getElementById('quotationForm').reset();
            document.getElementById('quotationId').value = '';
            document.getElementById('quotationModal').style.display = 'block';
        }

        function closeQuotationModal() {
            QuotationModule.closeQuotationModal();
        }

        function closeDeleteQuotationModal() {
            QuotationModule.closeDeleteQuotationModal();
        }

        function confirmDeleteQuotation() {
            QuotationModule.confirmDeleteQuotation();
        }

        // Initialize when quotation page becomes active
        document.addEventListener('DOMContentLoaded', function() {
            const quotationMenuItem = document.querySelector('.menu-item[onclick*="quotation"]');
            if (quotationMenuItem) {
                quotationMenuItem.addEventListener('click', function() {
                    setTimeout(() => {
                        if (QuotationModule.quotations.length === 0) {
                            QuotationModule.init();
                        }
                    }, 100);
                });
            }

            const quotationPage = document.getElementById('quotation');
            if (quotationPage && quotationPage.classList.contains('active')) {
                QuotationModule.init();
            }
        });

        // PRODUCT SEARCH MODULE
        const ProductSearch = {
            init() {
                const searchInput = document.getElementById('productSearchInput');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
                }
            },

            handleSearch(searchTerm) {
                const term = searchTerm.toLowerCase();
                const productItems = document.querySelectorAll('.product-item');
                let visibleCount = 0;

                productItems.forEach(item => {
                    const displayName = item.querySelector('.product-title')?.textContent.toLowerCase() || '';
                    const brandName = item.querySelector('.product-subtitle')?.textContent.toLowerCase() || '';
                    const category = item.querySelectorAll('.product-subtitle')[1]?.textContent.toLowerCase() || '';

                    if (displayName.includes(term) || brandName.includes(term) || category.includes(term)) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                this.updateProductCount(visibleCount);
            },

            updateProductCount(count) {
                const productCountSpan = document.getElementById('displayedProductCount');
                if (productCountSpan) {
                    productCountSpan.textContent = count;
                }
            }
        };

        // MAIN INITIALIZATION
        document.addEventListener('DOMContentLoaded', function() {
            UserDropdown.init();
            BulkActions.init();
            ProductSearch.init();

            console.log('✅ Staff Dashboard initialized successfully');
        });

        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category-select');
            const brandSelect = document.getElementById('brand-select');

            if (!categorySelect || !brandSelect) return;

            categorySelect.addEventListener('change', function() {
                const category = this.value;

                // Reset brand dropdown
                brandSelect.innerHTML = '<option value="">Loading brands...</option>';
                brandSelect.disabled = true;

                if (!category) {
                    brandSelect.innerHTML = '<option value="">Select a category first</option>';
                    return;
                }

                fetch(`../../controllers/brand_data.php?category=${encodeURIComponent(category)}`)
                    .then(response => response.json())
                    .then(brands => {
                        brandSelect.innerHTML = '<option value="">Select brand</option>';

                        if (brands.length === 0) {
                            brandSelect.innerHTML = '<option value="">No brands available</option>';
                        } else {
                            brands.forEach(brand => {
                                const option = document.createElement('option');
                                option.value = brand;
                                option.textContent = brand;
                                brandSelect.appendChild(option);
                            });
                        }

                        brandSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading brands:', error);
                        brandSelect.innerHTML = '<option value="">Failed to load brands</option>';
                    });
            });
        });

        // ENHANCED PRODUCT PREVIEW MODULE WITH PROPER MULTI-IMAGE HANDLING
        const ProductPreview = {
            previewImages: [],
            currentSlide: 0,
            fileObjects: [], // Store actual File objects

            init() {
                // Form input elements
                this.productNameInput = document.getElementById('product-name-input');
                this.categorySelect = document.getElementById('category-select');
                this.priceInput = document.getElementById('price-input');
                this.stockInput = document.getElementById('stock-quantity-input');
                this.imageInput = document.getElementById('product-images');

                // Preview elements
                this.previewName = document.getElementById('preview-name');
                this.previewCategory = document.getElementById('preview-category');
                this.previewPrice = document.getElementById('preview-price');
                this.previewStock = document.getElementById('preview-stock');
                this.carouselImage = document.getElementById('carousel-image');
                this.imagePreviewGrid = document.getElementById('imagePreviewGrid');

                // Attach event listeners
                if (this.productNameInput) {
                    this.productNameInput.addEventListener('input', () => this.updatePreview());
                }
                if (this.categorySelect) {
                    this.categorySelect.addEventListener('change', () => this.updatePreview());
                }
                if (this.priceInput) {
                    this.priceInput.addEventListener('input', () => this.updatePreview());
                }
                if (this.stockInput) {
                    this.stockInput.addEventListener('input', () => this.updatePreview());
                }
                if (this.imageInput) {
                    this.imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
                }

                // Add carousel indicator
                this.addCarouselIndicator();

                // Handle form submission to use our file objects
                this.setupFormSubmission();
            },

            setupFormSubmission() {
                const form = document.querySelector('form[enctype="multipart/form-data"]');
                if (!form) return;

                form.addEventListener('submit', (e) => {
                    // If we have custom file objects, we need to update the file input
                    if (this.fileObjects.length > 0) {
                        // Create a new DataTransfer object to hold our files
                        const dataTransfer = new DataTransfer();

                        this.fileObjects.forEach(file => {
                            dataTransfer.items.add(file);
                        });

                        // Update the file input with our custom files
                        this.imageInput.files = dataTransfer.files;
                    }
                });
            },

            updatePreview() {
                // Update product name
                const productName = this.productNameInput?.value || 'Product Name';
                if (this.previewName) {
                    this.previewName.textContent = productName;
                }

                // Update category with icon
                const category = this.categorySelect?.value || 'Category';
                const categoryText = this.categorySelect?.options[this.categorySelect.selectedIndex]?.text || 'Category';
                if (this.previewCategory) {
                    this.previewCategory.innerHTML = `<i class="fas fa-tag"></i> ${categoryText}`;
                }

                // Update price
                const price = parseFloat(this.priceInput?.value) || 0;
                if (this.previewPrice) {
                    this.previewPrice.textContent = `₱${price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
                }

                // Update stock
                const stock = parseInt(this.stockInput?.value) || 0;
                if (this.previewStock) {
                    this.previewStock.innerHTML = `<i class="fas fa-box"></i> Stock: ${stock} units`;
                }
            },

            handleImageUpload(event) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                // Calculate how many more images we can add
                const remainingSlots = 15 - this.previewImages.length;
                if (remainingSlots <= 0) {
                    alert('Maximum 15 images allowed!');
                    return;
                }

                // Process new files
                const filesToAdd = Math.min(files.length, remainingSlots);
                let loadedCount = 0;

                for (let i = 0; i < filesToAdd; i++) {
                    const file = files[i];

                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        console.warn(`File ${file.name} is not an image.`);
                        continue;
                    }

                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File ${file.name} is too large. Max size is 5MB.`);
                        continue;
                    }

                    // Store the actual file object
                    this.fileObjects.push(file);

                    const reader = new FileReader();

                    reader.onload = (e) => {
                        const imageUrl = e.target.result;
                        const currentIndex = this.previewImages.length;

                        this.previewImages.push(imageUrl);

                        // Add to preview grid with animation
                        this.addImageToPreviewGrid(imageUrl, currentIndex);

                        loadedCount++;

                        // Update carousel if this is the first image
                        if (this.previewImages.length === 1) {
                            this.currentSlide = 0;
                            this.updateCarousel();
                        }

                        // Update carousel indicator
                        this.updateCarouselIndicator();
                    };

                    reader.onerror = () => {
                        console.error(`Failed to read file: ${file.name}`);
                    };

                    reader.readAsDataURL(file);
                }

                // Show message if we hit the limit
                if (this.previewImages.length + filesToAdd >= 15) {
                    setTimeout(() => {
                        alert(`Maximum limit reached: 15 images`);
                    }, 100);
                }

                // Clear the input so the same files can be selected again if needed
                // Note: Don't clear if we're maintaining the file list
            },

            addImageToPreviewGrid(imageUrl, index) {
                if (!this.imagePreviewGrid) return;

                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item loading';
                previewItem.setAttribute('data-index', `#${index + 1}`);
                previewItem.setAttribute('data-image-index', index);
                previewItem.innerHTML = `
            <img src="${imageUrl}" alt="Preview ${index + 1}" loading="lazy">
            <button type="button" class="remove-image-btn" onclick="ProductPreview.removeImage(${index})" title="Remove image">
                <i class="fas fa-times"></i>
            </button>
        `;

                // Add click to view in carousel
                const imgElement = previewItem.querySelector('img');
                imgElement.addEventListener('click', () => {
                    this.currentSlide = index;
                    this.updateCarousel();
                    this.scrollCarouselIntoView();
                });

                this.imagePreviewGrid.appendChild(previewItem);
            },

            removeImage(index) {
                // Show confirmation for better UX
                if (!confirm('Are you sure you want to remove this image?')) {
                    return;
                }

                // Remove from both arrays
                this.previewImages.splice(index, 1);
                this.fileObjects.splice(index, 1);

                // Rebuild preview grid with updated indices
                if (this.imagePreviewGrid) {
                    this.imagePreviewGrid.innerHTML = '';
                    this.previewImages.forEach((url, i) => {
                        this.addImageToPreviewGrid(url, i);
                    });
                }

                // Update carousel
                if (this.currentSlide >= this.previewImages.length && this.previewImages.length > 0) {
                    this.currentSlide = this.previewImages.length - 1;
                } else if (this.previewImages.length === 0) {
                    this.currentSlide = 0;
                }

                this.updateCarousel();
                this.updateCarouselIndicator();

                // Update the file input with remaining files
                this.updateFileInput();
            },

            updateFileInput() {
                if (!this.imageInput) return;

                // Create a new DataTransfer object
                const dataTransfer = new DataTransfer();

                // Add all remaining files
                this.fileObjects.forEach(file => {
                    dataTransfer.items.add(file);
                });

                // Update the input
                this.imageInput.files = dataTransfer.files;
            },

            scrollCarouselIntoView() {
                const carousel = document.querySelector('.preview-carousel');
                if (carousel) {
                    carousel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            },

            addCarouselIndicator() {
                const carousel = document.querySelector('.preview-carousel');
                if (!carousel) return;

                // Check if indicator already exists
                if (document.getElementById('carouselIndicator')) return;

                const indicator = document.createElement('div');
                indicator.className = 'carousel-indicator';
                indicator.id = 'carouselIndicator';
                indicator.textContent = '0 / 0';
                carousel.appendChild(indicator);
            },

            updateCarousel() {
                if (!this.carouselImage) return;

                const prevBtn = document.querySelector('.carousel-btn.prev');
                const nextBtn = document.querySelector('.carousel-btn.next');

                if (this.previewImages.length > 0) {
                    this.carouselImage.src = this.previewImages[this.currentSlide];

                    // Enable/disable buttons based on position
                    if (prevBtn) {
                        prevBtn.disabled = this.currentSlide === 0;
                        prevBtn.style.opacity = this.currentSlide === 0 ? '0.3' : '1';
                    }
                    if (nextBtn) {
                        nextBtn.disabled = this.currentSlide === this.previewImages.length - 1;
                        nextBtn.style.opacity = this.currentSlide === this.previewImages.length - 1 ? '0.3' : '1';
                    }

                    // Highlight current image in grid
                    this.highlightCurrentImageInGrid();
                } else {
                    this.carouselImage.src = '../../assets/img/placeholder.png';
                    if (prevBtn) {
                        prevBtn.disabled = true;
                        prevBtn.style.opacity = '0.3';
                    }
                    if (nextBtn) {
                        nextBtn.disabled = true;
                        nextBtn.style.opacity = '0.3';
                    }
                }

                this.updateCarouselIndicator();
            },

            highlightCurrentImageInGrid() {
                // Remove all highlights
                document.querySelectorAll('.image-preview-item').forEach(item => {
                    item.classList.remove('active');
                });

                // Add highlight to current image
                const currentItem = document.querySelector(`.image-preview-item[data-image-index="${this.currentSlide}"]`);
                if (currentItem) {
                    currentItem.classList.add('active');
                }
            },

            updateCarouselIndicator() {
                const indicator = document.getElementById('carouselIndicator');
                if (!indicator) return;

                if (this.previewImages.length > 0) {
                    indicator.textContent = `${this.currentSlide + 1} / ${this.previewImages.length}`;
                    indicator.style.display = 'block';
                } else {
                    indicator.textContent = '0 / 0';
                    indicator.style.display = 'none';
                }
            },

            nextSlide() {
                if (this.previewImages.length === 0) return;
                if (this.currentSlide < this.previewImages.length - 1) {
                    this.currentSlide++;
                    this.updateCarousel();
                }
            },

            prevSlide() {
                if (this.previewImages.length === 0) return;
                if (this.currentSlide > 0) {
                    this.currentSlide--;
                    this.updateCarousel();
                }
            },

            reset() {
                this.previewImages = [];
                this.fileObjects = [];
                this.currentSlide = 0;
                if (this.imagePreviewGrid) {
                    this.imagePreviewGrid.innerHTML = '';
                }
                if (this.carouselImage) {
                    this.carouselImage.src = '../../assets/img/placeholder.png';
                }
                this.updateCarousel();
                this.updateCarouselIndicator();
            }
        };




        // Global functions for carousel navigation (called from HTML buttons)
        function nextSlide() {
            ProductPreview.nextSlide();
        }

        function prevSlide() {
            ProductPreview.prevSlide();
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            ProductPreview.init();

            // Handle form reset
            const form = document.querySelector('form[enctype="multipart/form-data"]');
            if (form) {
                form.addEventListener('reset', () => {
                    setTimeout(() => {
                        ProductPreview.reset();
                    }, 10);
                });
            }

            console.log('✅ Product Preview initialized with multi-image support');
        });

        // Form Submit Handler with Animation
        document.addEventListener('DOMContentLoaded', function() {
            const productForm = document.querySelector('form[enctype="multipart/form-data"]');

            if (productForm) {
                productForm.addEventListener('submit', function(e) {
                    // Check if this is the add product form
                    const actionInput = this.querySelector('input[name="action"]');
                    if (actionInput && actionInput.value === 'add_product') {
                        // Start the upload animation
                        UploadAnimation.start();

                        // Let the form submit naturally
                        // The animation will play while PHP processes the upload
                    }
                });
            }

            // Check if there's a success message from PHP
            const alertSuccess = document.querySelector('.alert.success');
            if (alertSuccess) {
                // Hide the PHP alert
                alertSuccess.style.display = 'none';

                // Show our animated success modal instead
                setTimeout(() => {
                    UploadAnimation.complete();
                }, 500);
            }

            // Check if there's an error message
            const alertError = document.querySelector('.alert.error');
            if (alertError) {
                // Hide the upload overlay if it's showing
                const overlay = document.getElementById('uploadOverlay');
                if (overlay) {
                    overlay.classList.remove('active');
                }

                // Keep the error message visible but style it better
                alertError.style.animation = 'shake 0.5s';
            }
        });

        // Add shake animation for errors
        const style = document.createElement('style');
        style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
        20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
`;
        document.head.appendChild(style);
    </script>
<script>
function showPage(pageId, pageTitle) {
    // Hide all page content sections
    document.querySelectorAll('.page-content').forEach(page => {
        page.style.display = 'none';
    });
    // Show the selected page
    document.getElementById(pageId).style.display = 'block';
    // Optional: Update page title if you have a title element
    const titleElement = document.getElementById('page-title');
    if (titleElement) {
        titleElement.textContent = pageTitle;
    }
}
</script>
</body>

</html>