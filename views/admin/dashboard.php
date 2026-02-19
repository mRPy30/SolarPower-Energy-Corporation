<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include "../../config/dbconn.php";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Fetch current admin data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_admin = $result->fetch_assoc();

// Prepare display variables
$firstName = $current_admin['firstName'] ?? 'Admin';
$lastName = $current_admin['lastName'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
$joinDate = date('F Y', strtotime($current_admin['created_at']));

// Dashboard Statistics Functions
function get_stats($conn) {
    $stats = [
        'clients' => 0,
        'products' => 0,
        'orders' => 0,
        'suppliers' => 0,
        'staff' => 0,
        'revenue' => 0,
        'pending_orders' => 0,
        'inquiries' => 0
    ];
    
    $result = $conn->query("SELECT COUNT(DISTINCT customer_email) FROM orders WHERE order_status != 'archived'");
    if ($result) {
        $stats['clients'] = $result->fetch_row()[0];
        $result->close();
    }

    // Get products count
    $result = $conn->query("SELECT COUNT(*) FROM product");
    if ($result) {
        $stats['products'] = $result->fetch_row()[0];
        $result->close();
    }

    // Get total orders (excluding archived if necessary)
    $result = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status != 'archived'");
    if ($result) {
        $stats['orders'] = $result->fetch_row()[0];
        $result->close();
    }

    // Get products count
    $result = $conn->query("SELECT COUNT(*) FROM product");
    if ($result) {
        $stats['products'] = $result->fetch_row()[0];
        $result->close();
    }

    // Get orders count
    $result = $conn->query("SELECT COUNT(*) FROM orders");
    if ($result) {
        $stats['orders'] = $result->fetch_row()[0];
        $result->close();
    }
    
    // Get suppliers count
    $result = $conn->query("SELECT COUNT(*) FROM supplier");
    if ($result) {
        $stats['suppliers'] = $result->fetch_row()[0];
        $result->close();
    }

    // Get total revenue (assuming orders table has total_amount field)
    $result = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'");
    if ($result) {
        $row = $result->fetch_row();
        $stats['revenue'] = $row[0] ?? 0;
        $result->close();
    }

    // Get pending orders
    $result = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('pending', 'confirmed')");
    if ($result) {
        $stats['pending_orders'] = $result->fetch_row()[0];
        $result->close();
    }

    // Get unread inquiries
    $result = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    if ($result) {
        $stats['inquiries'] = $result->fetch_row()[0];
        $result->close();
    }

    return $stats;
}


function get_most_sold_product($conn) {
    $query = "SELECT p.displayName as productName, COUNT(o.id) as totalSold 
              FROM orders o 
              JOIN product p ON o.id = p.id 
              WHERE o.order_status = 'delivered' 
              GROUP BY o.id 
              ORDER BY totalSold DESC 
              LIMIT 1";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function get_all_products($conn) {
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

function get_all_suppliers($conn) {
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

function get_all_staff($conn) {
    $staff = [];
    
    $query = "SELECT id, firstName, lastName, email, created_at FROM staff ORDER BY id DESC";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }
        $result->close();
    }
    
    return $staff;
}

function get_contact_inquiries($conn) {
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

// Get the top 5 most recent orders (stays same)
function get_recent_orders($conn) {
    $orders = [];
    $query = "SELECT id, customer_name, total_amount, order_status, created_at 
              FROM orders 
              WHERE order_status != 'archived' 
              ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    return $orders;
}

// FIXED: Get the best seller by joining orders and order_items
function get_best_seller($conn) {
    $query = "SELECT oi.product_name,
              COALESCE(
                  NULLIF(oi.product_id, 0),
                  (SELECT p.id FROM product p WHERE p.displayName = oi.product_name LIMIT 1)
              ) AS product_id,
              SUM(oi.quantity) as total_qty, COUNT(oi.id) as order_frequency,
              COALESCE(
                  (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = oi.product_id AND oi.product_id > 0 ORDER BY pi.id ASC LIMIT 1),
                  (SELECT pi.image_path FROM product_images pi JOIN product p ON pi.product_id = p.id WHERE p.displayName = oi.product_name ORDER BY pi.id ASC LIMIT 1)
              ) as image_path
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              WHERE o.order_status != 'archived'
              GROUP BY oi.product_name, oi.product_id
              ORDER BY total_qty DESC
              LIMIT 1";
              
    $result = $conn->query($query);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
}

// Call functions at the top of your file
$recent_orders = get_recent_orders($conn);
$best_seller = get_best_seller($conn);


function get_unique_clients($conn) {
    $clients = [];
    // Group by email to treat multiple orders from the same person as one "Client"
    // We use MAX() for name/contact to get the most recent/available info
    $query = "SELECT 
                customer_name, 
                customer_email , 
                customer_phone, 
                customer_address, 
                COUNT(id) as total_orders 
              FROM orders 
              GROUP BY customer_email 
              ORDER BY total_orders DESC";
    
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
    }
    return $clients;
}

// Fetch the data
$all_unique_clients = get_unique_clients($conn);

function get_dashboard_analytics($conn) {
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

function get_sales_by_city($conn) {
    // This groups your orders by the address to see where most sales come from
    $sql = "SELECT customer_address, COUNT(*) as total_orders, SUM(total_amount) as revenue 
            FROM orders 
            GROUP BY customer_address 
            ORDER BY revenue DESC LIMIT 5";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}
$city_data = get_sales_by_city($conn);

function get_solar_metrics($conn) {
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

function get_order_status($conn) {
    $statuses = ['pending', 'confirmed', 'preparing', 'ready to ship' , 'In Transit', 'out for delivery', 'delivered', 'cancelled'];
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

function get_all_inquiries($conn) {
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


// Fetch all data for initial page load
$dashboard_analytics = get_dashboard_analytics($conn);
$lowStock   = $dashboard_analytics['low_stock'];
$outOfStock = $dashboard_analytics['out_of_stock'];
$solar_stats = get_solar_metrics($conn);
$order_status = get_order_status($conn);
$best_seller = get_best_seller($conn);
$stats = get_stats($conn);
$most_sold_product = get_most_sold_product($conn);
$all_products = get_all_products($conn);
$all_suppliers = get_all_suppliers($conn);
$all_staff = get_all_staff($conn);
$inquiries = get_contact_inquiries($conn);
$product_count = count($all_products);
$all_inquiries = get_all_inquiries($conn);
$total_inquiries = count($all_inquiries);

/* ===========================================================
   FIXED AJAX HANDLER (Handles both Staff and Suppliers)
   =========================================================== */
if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    // Determine what we are working with
    $entity = $_GET['entity'] ?? ($_POST['entity'] ?? '');
    $action = $_GET['action'] ?? ($_POST['action'] ?? 'fetch');
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        /* ---------- SUPPLIER LOGIC ---------- */
        if ($entity === 'supplier') {
            switch ($action) {
                case 'create':
                    if (empty($data['supplierName'])) {
                        echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
                        exit;
                    }
                    $stmt = $conn->prepare("INSERT INTO supplier (supplierName, contactPerson, email, phone, address, city, country, registrationDate) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $stmt->bind_param("sssssss", $data['supplierName'], $data['contactPerson'], $data['email'], $data['phone'], $data['address'], $data['city'], $data['country']);
                    echo json_encode(['success' => $stmt->execute(), 'message' => $stmt->error ?: 'Supplier created']);
                    break;

                case 'update':
                    $stmt = $conn->prepare("UPDATE supplier SET supplierName=?, contactPerson=?, email=?, phone=?, address=?, city=?, country=? WHERE id=?");
                    $stmt->bind_param("sssssssi", $data['supplierName'], $data['contactPerson'], $data['email'], $data['phone'], $data['address'], $data['city'], $data['country'], $data['id']);
                    echo json_encode(['success' => $stmt->execute(), 'message' => 'Supplier updated']);
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM supplier WHERE id = ?");
                    $stmt->bind_param("i", $data['id']);
                    echo json_encode(['success' => $stmt->execute(), 'message' => 'Supplier deleted']);
                    break;
            }
            exit; // Stop here for supplier requests
        }

        /* ---------- STAFF LOGIC ---------- */
        if ($entity === 'staff') {
            switch ($action) {
                case 'create':
                    if (empty($data['firstName']) || empty($data['email']) || empty($data['password'])) {
                        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
                        exit;
                    }
                    $password = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO staff (firstName, lastName, email, password, contact_number) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $data['firstName'], $data['lastName'], $data['email'], $password, $data['contact_number']);
                    echo json_encode(['success' => $stmt->execute(), 'message' => 'Staff added successfully']);
                    break;

                case 'update':
                    if (!empty($data['password'])) {
                        $password = password_hash($data['password'], PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE staff SET firstName=?, lastName=?, email=?, password=?, contact_number=? WHERE id=?");
                        $stmt->bind_param("sssssi", $data['firstName'], $data['lastName'], $data['email'], $password, $data['contact_number'], $data['id']);
                    } else {
                        $stmt = $conn->prepare("UPDATE staff SET firstName=?, lastName=?, email=?, contact_number=? WHERE id=?");
                        $stmt->bind_param("ssssi", $data['firstName'], $data['lastName'], $data['email'], $data['contact_number'], $data['id']);
                    }
                    echo json_encode(['success' => $stmt->execute(), 'message' => 'Staff updated']);
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
                    $stmt->bind_param("i", $data['id']);
                    echo json_encode(['success' => $stmt->execute(), 'message' => 'Staff deleted']);
                    break;
            }
            exit; // Stop here for staff requests
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Power - Admin</title>
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

        <div class="menu-item active" onclick="showPage('dashboard', 'Dashboard')">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </div>
        <div class="menu-label">SALES & CUSTOMERS</div>
        <div class="menu-item" onclick="showPage('inquiries', 'Inquiries')">
            <i class="fas fa-envelope-open-text"></i>
            <span>Inquiries</span>
        </div>
        <div class="menu-item" onclick="showPage('orders', 'Orders')">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
        </div>
        <div class="menu-item" onclick="showPage('tracking', 'Tracking')">
            <i class="fas fa-map-marker-alt"></i>
            <span>Order Tracking</span>
        </div>
        <div class="menu-item" onclick="showPage('quotation', 'Quotation')">
            <i class="fas fa-file-invoice"></i>
            <span>Quotation</span>
        </div>
        <div class="menu-item" onclick="showPage('clients', 'Clients')">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </div>
        <!--<div class="menu-label">SERVICES</div>-->

        <div class="menu-label">PRODUCTS & PARTNERS</div>
        <div class="menu-item" onclick="showPage('product', 'Product')">
            <i class="fas fa-box"></i>
            <span>Product</span>
        </div>
        <div class="menu-item" onclick="showPage('suppliers', 'Suppliers')">
            <i class="fas fa-truck"></i>
            <span>Suppliers</span>
        </div>
        
        <!--<div class="menu-label">MARKETING</div>-->

        
        <div class="menu-label">ADMIN</div>
        <div class="menu-item" onclick="showPage('staff', 'Staff Management')">
            <i class="fas fa-user-tie"></i>
            <span>Staff</span>
        </div>
        <div class="menu-label">SYSTEM</div>
        <div class="menu-item" onclick="showPage('settings', 'Settings')">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </div>
        
        <!--<div class="menu-label">REPORTS</div>-->

    </aside>



    <main class="main-content">
        <div class="header">
            <div class="header-left">
                <h1 id="page-title">Dashboard</h1>
                <p>Good day, Ma'am/Sir <strong><?php echo htmlspecialchars($fullName); ?></strong>! Hope you're doing well.</p>
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



                <!-- Dashboard Page -->
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

            <!-- Admin-specific stats row -->
            

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
                        <tr><td colspan="4" style="text-align: center; padding: 20px;">No recent orders.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    
        <?php include '../../includes/top_seller_widget.php'; ?>
        
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
                }
                 else {
                            $addProductMsg = "<div class='alert error'>Error adding product: " . $stmt->error . "</div>";
                        }
                        $stmt->close();
                    }
                    $conn_post->close();
                }
            }
            ?>
            <style>
                .alert { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
                .alert.error { background-color: #fdd; color: #a00; border: 1px solid #f99; }
                .alert.success { background-color: #dfd; color: #0a0; border: 1px solid #9f9; }
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
                                        max="15"
                                    >
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
    
         <!-- Staff Management Page (ADMIN EXCLUSIVE) -->
        <div id="staff" class="page-content">
            <div class="staff-container">
                <div class="staff-stats">
                    <div class="staff-stat-box">
                        <h4>TOTAL STAFF</h4>
                        <div class="value" id="totalStaff"><?php echo count($all_staff); ?></div>
                    </div>
                    <div class="staff-stat-box">
                        <h4>ACTIVE TODAY</h4>
                        <div class="value" id="activeStaff">0</div>
                    </div>
                    <div class="staff-stat-box">
                        <h4>NEW THIS MONTH</h4>
                        <div class="value" id="newStaff">0</div>
                    </div>

                </div>

                <div class="staff-filters">
                    <div class="staff-filter-group">
                        <label>Search</label>
                        <input type="text" id="staffSearch" placeholder="Search by name or email">
                    </div>
                    <div class="staff-filter-group">
                        <label>Department</label>
                        <select id="departmentFilter">
                            <option value="">All Departments</option>
                            <option value="Sales">Sales</option>
                            <option value="Operations">Operations</option>
                            <option value="Support">Support</option>
                        </select>
                    </div>
                </div>

                <div class="staff-header">
                    <button class="btn-primary" onclick="openStaffModal()">
                        <i class="fas fa-plus"></i> Add Staff Member
                    </button>
                </div>

                <div class="staff-table-wrapper">
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody">
                            <?php if (!empty($all_staff)): ?>
                                <?php foreach ($all_staff as $staff): ?>
                                    <tr>
                                        <td><strong>#<?php echo $staff['id']; ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($staff['firstName'] . ' ' . $staff['lastName']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                        <td><?php echo date("M d, Y", strtotime($staff['created_at'])); ?></td>
                                        <td><span class="status-badge status-success">Active</span></td>
                                        <td>
                                            <div class="staff-actions">
                                                <button class="btn-table-action btn-edit" onclick="StaffModule.editStaff(<?php echo $staff['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn-table-action btn-delete" onclick="StaffModule.showDeleteModal(<?php echo $staff['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;">No staff members found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

     <!-- Staff Modal (ADMIN EXCLUSIVE) -->
    <div id="staffModal" class="staffModal">
        <div class="staffModal-content">
            <div class="staffModal-header">
                <h3 id="staffModalTitle">
                    <i class="fas fa-user-tie"></i>
                    <span>Add Staff Member</span>
                </h3>
                <span class="close" onclick="closeStaffModal()">&times;</span>
            </div>
            <form id="staffForm" onsubmit="handleStaffSubmit(event)">
                <div class="staffModal-body">
                    <input type="hidden" name="id" id="staffId">
                    
                    <div class="staffModal-row">
                        <div class="staffModal-group">
                            <label>
                                <i class="fas fa-user"></i>
                                First Name
                            </label>
                            <input type="text" name="firstName" id="firstName" required placeholder="Enter first name">
                        </div>
                        <div class="staffModal-group">
                            <label>
                                <i class="fas fa-user"></i>
                                Last Name
                            </label>
                            <input type="text" name="lastName" id="lastName" required placeholder="Enter last name">
                        </div>
                    </div>

                    <div class="staffModal-group">
                        <label>
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input type="email" name="email" id="email" required placeholder="email@example.com">
                    </div>

                    <div class="staffModal-group">
                        <label>
                            <i class="fas fa-phone"></i>
                            Contact Number
                        </label>
                        <input type="tel" name="contact_number" id="contact_number" placeholder="+63 912 345 6789">
                    </div>

                    <div class="staffModal-group password-group">
                        <label>
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" name="password" id="password" placeholder="Enter password">
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-actions">
                            <button type="button" class="generate-password-btn" onclick="generatePassword()">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                Generate
                            </button>
                            <button type="button" class="copy-password-btn" onclick="copyPassword()">
                                <i class="fas fa-copy"></i>
                                Copy
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar"></div>
                        </div>
                        <div class="password-strength-text" id="strengthText"></div>
                        <div class="helper-text" id="passwordHelper">
                            <i class="fas fa-info-circle"></i>
                            Leave blank when editing existing staff
                        </div>
                    </div>
                </div>

                <div class="staffModal-footer">
                    <button type="button" class="staffModal-btn-secondary" onclick="closeStaffModal()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="staffModal-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Staff
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span class="toast-message" id="toastMessage">Success!</span>
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
            <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-avatar-large">
                    <?php echo $initials; ?>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($fullName); ?></h1>
                    <div class="profile-role">
                        <i class="fas fa-user-tie"></i>
                        <span>Manager</span>
                    </div>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Joined <?php echo $joinDate; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($current_admin['email']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-grid">
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
                        <div class="info-value" id="displayFirstName"><?php echo htmlspecialchars($current_admin['firstName']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Last Name</div>
                        <div class="info-value" id="displayLastName"><?php echo htmlspecialchars($current_admin['lastName']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value" id="displayEmail"><?php echo htmlspecialchars($current_admin['email']); ?></div>
                    </div>
                </div>
            </div>

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
                        <div class="info-value"><?php echo date('F d, Y', strtotime($current_admin['created_at'])); ?></div>
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
                    <input type="text" id="profileFirstName" value="<?php echo htmlspecialchars($current_admin['firstName']); ?>" required>
                </div>
                <div class="staffModal-group">
                    <label><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" id="profileLastName" value="<?php echo htmlspecialchars($current_admin['lastName']); ?>" required>
                </div>
                <div class="staffModal-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($current_admin['email']); ?>" required>
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
    while($row = $res->fetch_assoc()) { $data[] = $row; }
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

            const marker = L.marker([lat, lon], { icon: yellowIcon }).addTo(map);
            
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
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Status updated!');
            location.reload(); // Refresh to show new status
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// ==========================================
// PROFILE MANAGEMENT FUNCTIONS
// ==========================================

function openEditProfileModal() {
    document.getElementById('editProfileModal').classList.add('show');
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').classList.remove('show');
}

function openChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.add('show');
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('show');
    document.getElementById('changePasswordForm').reset();
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

async function handleUpdateProfile(event) {
    event.preventDefault();
    
    const data = {
        action: 'update_profile',
        firstName: document.getElementById('profileFirstName').value,
        lastName: document.getElementById('profileLastName').value,
        email: document.getElementById('profileEmail').value,
        contact_number: document.getElementById('profileContactNumber').value
    };
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        const response = await fetch('profile_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || 'Profile updated successfully!');
            setTimeout(() => {
                closeEditProfileModal();
                location.reload();
            }, 1500);
        } else {
            showToast(result.message || 'Failed to update profile', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Connection error. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

async function handleChangePassword(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validation
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
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || 'Password changed successfully!');
            setTimeout(() => {
                closeChangePasswordModal();
                document.getElementById('changePasswordForm').reset();
            }, 1500);
        } else {
            showToast(result.message || 'Failed to change password', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Connection error. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Close modals when clicking outside
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

// Modal Toggle Functions
function openEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'block';
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}

function openChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'block';
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'none';
}

function togglePasswordField(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Handle Profile Update via AJAX
async function handleUpdateProfile(event) {
    event.preventDefault();
    
    const formData = {
        firstName: document.getElementById('profileFirstName').value,
        lastName: document.getElementById('profileLastName').value,
        email: document.getElementById('profileEmail').value,
        contact: document.getElementById('profileContactNumber').value,
        action: 'update_profile'
    };

    try {
        const response = await fetch('../../controllers/admin/update_account.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        if (result.success) {
            alert('Profile updated successfully!');
            location.reload(); // Refresh to show new data
        } else {
            alert(result.message || 'Update failed');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

//============Staff Management========================
function openStaffModal(staffData = null) {
            const modal = document.getElementById('staffModal');
            const form = document.getElementById('staffForm');
            const title = document.querySelector('#staffModalTitle span');
            
            console.log('Opening modal...', modal); // Debug log
            
            if (!modal) {
                console.error('Modal not found!');
                return;
            }
            
            if (staffData) {
                title.textContent = 'Edit Staff Member';
                document.getElementById('staffId').value = staffData.id;
                document.getElementById('firstName').value = staffData.firstName;
                document.getElementById('lastName').value = staffData.lastName;
                document.getElementById('email').value = staffData.email;
                document.getElementById('contact_number').value = staffData.contact_number || '';
                document.getElementById('password').value = '';
                document.getElementById('passwordHelper').style.display = 'flex';
            } else {
                title.textContent = 'Add Staff Member';
                form.reset();
                document.getElementById('staffId').value = '';
                document.getElementById('passwordHelper').style.display = 'none';
                resetPasswordStrength();
            }
            
            modal.classList.add('show');
            console.log('Modal classes:', modal.className); // Debug log
        }

        function closeStaffModal() {
            const modal = document.getElementById('staffModal');
            modal.classList.remove('show');
            document.getElementById('staffForm').reset();
            resetPasswordStrength();
        }

        function generatePassword() {
            const length = 16;
            const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
            const lowercase = 'abcdefghijklmnopqrstuvwxyz';
            const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const numbers = '0123456789';
            const special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
            
            let password = '';
            password += lowercase[Math.floor(Math.random() * lowercase.length)];
            password += uppercase[Math.floor(Math.random() * uppercase.length)];
            password += numbers[Math.floor(Math.random() * numbers.length)];
            password += special[Math.floor(Math.random() * special.length)];
            
            for (let i = password.length; i < length; i++) {
                password += charset[Math.floor(Math.random() * charset.length)];
            }
            
            password = password.split('').sort(() => Math.random() - 0.5).join('');
            
            const passwordInput = document.getElementById('password');
            passwordInput.value = password;
            passwordInput.type = 'text';
            document.getElementById('toggleIcon').className = 'fas fa-eye-slash';
            
            checkPasswordStrength(password);
            showToast('Strong password generated!');
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        function copyPassword() {
            const passwordInput = document.getElementById('password');
            const copyBtn = document.querySelector('.copy-password-btn');
            
            if (!passwordInput.value) {
                showToast('No password to copy!', 'error');
                return;
            }
            
            passwordInput.select();
            passwordInput.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(passwordInput.value).then(() => {
                copyBtn.classList.add('copied');
                copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                showToast('Password copied to clipboard!');
                
                setTimeout(() => {
                    copyBtn.classList.remove('copied');
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                }, 2000);
            });
        }

        function checkPasswordStrength(password) {
            const strengthContainer = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            if (!password) {
                strengthContainer.className = 'password-strength';
                strengthText.textContent = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthContainer.classList.add('show');
            
            if (strength <= 2) {
                strengthContainer.className = 'password-strength show strength-weak';
                strengthText.textContent = 'Weak password';
            } else if (strength <= 4) {
                strengthContainer.className = 'password-strength show strength-medium';
                strengthText.textContent = 'Medium strength';
            } else {
                strengthContainer.className = 'password-strength show strength-strong';
                strengthText.textContent = 'Strong password';
            }
        }

        function resetPasswordStrength() {
            const strengthContainer = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            strengthContainer.className = 'password-strength';
            strengthText.textContent = '';
        }

        // Add event listener when DOM is ready
        window.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function(e) {
                    checkPasswordStrength(e.target.value);
                });
            }
        });

        async function handleStaffSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Get form values
    const staffData = {
        id: formData.get('id') || null,
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        email: formData.get('email'),
        contact_number: formData.get('contact_number'),
        password: formData.get('password')
    };
    
    // Determine if this is create or update
    const action = staffData.id ? 'update' : 'create';
    staffData.action = action;
    
    // Validate required fields
    if (!staffData.firstName || !staffData.lastName || !staffData.email) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // For new staff, password is required
    if (action === 'create' && !staffData.password) {
        showToast('Password is required for new staff members', 'error');
        return;
    }
    
    // If editing and password is empty, remove it from data
    if (action === 'update' && !staffData.password) {
        delete staffData.password;
    }
    
    // Get submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        // Send to PHP backend
        const response = await fetch('staff_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(staffData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || 'Staff member saved successfully!', 'success');
            
            // Close modal after short delay
            setTimeout(() => {
                closeStaffModal();
                
                // Reload the page to show updated staff list
                location.reload();
            }, 1500);
        } else {
            showToast(result.message || 'Failed to save staff member', 'error');
            
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        
    } catch (error) {
        console.error('Error saving staff:', error);
        showToast('An error occurred. Please try again.', 'error');
        
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// ==========================================
// EDIT STAFF FUNCTION
// Add this function to populate the form when editing
// ==========================================

function editStaff(staffId) {
    // Find staff data from the table row
    const row = document.querySelector(`button[onclick*="editStaff(${staffId})"]`)?.closest('tr');
    
    if (!row) {
        showToast('Staff member not found', 'error');
        return;
    }
    
    // Extract data from the table row
    const cells = row.querySelectorAll('td');
    const fullName = cells[1]?.textContent.trim().split(' ');
    const firstName = fullName[0] || '';
    const lastName = fullName.slice(1).join(' ') || '';
    const email = cells[2]?.textContent.trim() || '';
    
    // Create staff data object
    const staffData = {
        id: staffId,
        firstName: firstName,
        lastName: lastName,
        email: email,
        contact_number: '' // You'll need to add this to your table if you want to edit it
    };
    
    // Open modal with staff data
    openStaffModal(staffData);
}

// ==========================================
// DELETE STAFF FUNCTION
// Add this function to handle staff deletion
// ==========================================

let deleteStaffId = null;

function showDeleteStaffModal(staffId) {
    deleteStaffId = staffId;
    
    // Create delete modal if it doesn't exist
    if (!document.getElementById('deleteStaffModal')) {
        const modalHTML = `
            <div id="deleteStaffModal" class="staffModal">
                <div class="staffModal-content" style="max-width: 500px;">
                    <div class="staffModal-header">
                        <h3>
                            <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                            <span>Confirm Deletion</span>
                        </h3>
                        <span class="close" onclick="closeDeleteStaffModal()">&times;</span>
                    </div>
                    <div class="staffModal-body">
                        <p style="font-size: 16px; color: var(--clr-dark); margin-bottom: 16px;">
                            Are you sure you want to delete this staff member?
                        </p>
                        <p style="font-size: 14px; color: #e74c3c; font-weight: 600;">
                            <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                        </p>
                    </div>
                    <div class="staffModal-footer">
                        <button type="button" class="staffModal-btn-secondary" onclick="closeDeleteStaffModal()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="button" class="staffModal-btn-primary" style="background: linear-gradient(135deg, #e74c3c, #c0392b);" onclick="confirmDeleteStaff()">
                            <i class="fas fa-trash"></i>
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    document.getElementById('deleteStaffModal').classList.add('show');
}

function closeDeleteStaffModal() {
    document.getElementById('deleteStaffModal')?.classList.remove('show');
    deleteStaffId = null;
}

async function confirmDeleteStaff() {
    if (!deleteStaffId) return;
    
    try {
        const response = await fetch('staff_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete',
                id: deleteStaffId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Staff member deleted successfully', 'success');
            closeDeleteStaffModal();
            
            // Reload page after short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(result.message || 'Failed to delete staff member', 'error');
        }
        
    } catch (error) {
        console.error('Error deleting staff:', error);
        showToast('An error occurred. Please try again.', 'error');
    }
}

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('i');
            
            toastMessage.textContent = message;
            
            if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle';
                icon.style.color = '#e74c3c';
            } else {
                icon.className = 'fas fa-check-circle';
                icon.style.color = 'var(--clr-secondary)';
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Close modal if user clicks outside the modal content area
        window.onclick = function(event) {
            const modal = document.getElementById('staffModal');
            if (event.target === modal) {
                closeStaffModal();
            }
        };

// ==================== USER DROPDOWN MODULE ====================
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

// ==================== PAGE NAVIGATION MODULE ====================
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

        // Initialize modules when pages are shown
        if (pageId === 'tracking') {
            setTimeout(() => TrackingModule.init(), 100);
        } else if (pageId === 'quotation') {
            setTimeout(() => QuotationModule.init(), 100);
        } else if (pageId === 'suppliers') {
            setTimeout(() => SuppliersModule.init(), 100);
        } else if (pageId === 'staff') {
            setTimeout(() => StaffModule.init(), 100);
        } else if (pageId === 'orders') {
            setTimeout(() => OrdersModule.loadOrders(), 100);
        }
    }
};

function showPage(pageId, pageTitle) {
    PageNavigation.showPage(pageId, pageTitle);
}

// ============= Client Management =====================
function editClient(email) {
    // Logic to open a modal and edit client details based on email
    console.log("Editing client with email:", email);
    // You can implement a modal similar to your Staff modal here
}

async function archiveClient(email) {
    if (confirm("Are you sure you want to archive all orders for " + email + "?")) {
        try {
            const res = await fetch('?ajax=1&entity=client&action=archive', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            });
            const json = await res.json();
            alert(json.message);
            if (json.success) location.reload();
        } catch (err) {
            console.error("Archive failed", err);
        }
    }
}

// ==================== SUPPLIERS MODULE ====================

const SuppliersModule = {
    suppliers: [],
    filteredSuppliers: [],
    currentSearchTerm: '',
    
    /**
     * Initialize the suppliers module
     */
    async init() {
        console.log('Initializing Suppliers Module...');
        await this.loadSuppliers();
        this.setupEventListeners();
    },
    
    /**
     * Set up all event listeners
     */
    setupEventListeners() {
        // Search input
        const searchInput = document.getElementById('supplierSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentSearchTerm = e.target.value;
                this.handleSearch(e.target.value);
            });
        }
        
        // City filter
        const cityFilter = document.getElementById('cityFilter');
        if (cityFilter) {
            cityFilter.addEventListener('change', () => this.applyFilters());
        }
        
        // Form submission
        const form = document.getElementById('supplierForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    },
    
    /**
     * Load suppliers from server
     */
    async loadSuppliers() {
        try {
            const response = await fetch('?ajax=1&entity=supplier&action=fetch');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.suppliers = result.data || [];
                this.filteredSuppliers = [...this.suppliers];
                this.renderTable();
                this.updateStats();
                this.populateCityFilter();
            } else {
                this.showError(result.message || 'Failed to load suppliers');
                this.renderEmptyState('Failed to load suppliers');
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
            this.showError('Error connecting to database');
            this.renderEmptyState('Error loading data');
        }
    },
    
    /**
     * Render suppliers table
     */
    renderTable() {
        const tbody = document.getElementById('suppliersTableBody');
        if (!tbody) return;
        
        if (this.filteredSuppliers.length === 0) {
            this.renderEmptyState(
                this.currentSearchTerm 
                    ? 'No suppliers match your search criteria' 
                    : 'No suppliers found'
            );
            return;
        }
        
        tbody.innerHTML = this.filteredSuppliers.map(supplier => `
            <tr data-supplier-id="${supplier.id}">
                <td><strong>#${supplier.id}</strong></td>
                <td>
                    <div class="supplier-name-cell">
                        <i class="fas fa-building"></i>
                        <strong>${this.escapeHtml(supplier.supplierName)}</strong>
                    </div>
                </td>
                <td>${this.escapeHtml(supplier.contactPerson || '-')}</td>
                <td>
                    ${supplier.email ? `
                        <a href="mailto:${this.escapeHtml(supplier.email)}" class="email-link">
                            <i class="fas fa-envelope"></i> ${this.escapeHtml(supplier.email)}
                        </a>
                    ` : '-'}
                </td>
                <td>
                    ${supplier.phone ? `
                        <a href="tel:${this.escapeHtml(supplier.phone)}" class="phone-link">
                            <i class="fas fa-phone"></i> ${this.escapeHtml(supplier.phone)}
                        </a>
                    ` : '-'}
                </td>
                <td>
                    <div class="location-cell">
                        ${supplier.city ? `<span class="location-city"><i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(supplier.city)}</span>` : ''}
                        ${supplier.country ? `<span class="location-country">${this.escapeHtml(supplier.country)}</span>` : ''}
                        ${!supplier.city && !supplier.country ? '-' : ''}
                    </div>
                </td>
                <td>
                    <div class="date-cell">
                        <i class="fas fa-calendar"></i>
                        ${this.formatDate(supplier.registrationDate)}
                    </div>
                </td>
                <td>
                    <div class="supplier-actions">
                        <button class="btn-table-action btn-edit" 
                                onclick="SuppliersModule.editSupplier(${supplier.id})"
                                title="Edit Supplier">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-table-action btn-delete" 
                                onclick="SuppliersModule.showDeleteModal(${supplier.id})"
                                title="Delete Supplier">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },
    
    /**
     * Render empty state
     */
    renderEmptyState(message) {
        const tbody = document.getElementById('suppliersTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state">
                    <div class="empty-state-content">
                        <i class="fas fa-inbox"></i>
                        <h3>No Suppliers Found</h3>
                        <p>${this.escapeHtml(message)}</p>
                        ${this.currentSearchTerm ? `
                            <button onclick="SuppliersModule.clearSearch()" class="btn-clear-search">
                                <i class="fas fa-times"></i> Clear Search
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    },
    
    /**
     * Update statistics display
     */
    updateStats() {
        const total = this.suppliers.length;
        const uniqueCities = new Set(
            this.suppliers.map(s => s.city).filter(Boolean)
        ).size;
        
        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();
        
        // Active this month (registered this month)
        const activeThisMonth = this.suppliers.filter(s => {
            if (!s.registrationDate) return false;
            const date = new Date(s.registrationDate);
            return date.getMonth() === currentMonth && 
                   date.getFullYear() === currentYear;
        }).length;
        
        // New suppliers (last 30 days)
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        const newSuppliers = this.suppliers.filter(s => {
            if (!s.registrationDate) return false;
            return new Date(s.registrationDate) > thirtyDaysAgo;
        }).length;
        
        this.updateStatElement('totalSuppliers', total);
        this.updateStatElement('activeSuppliers', activeThisMonth);
        this.updateStatElement('newSuppliers', newSuppliers);
        this.updateStatElement('totalCities', uniqueCities);
    },
    
    /**
     * Safely update stat element
     */
    updateStatElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    },
    
    /**
     * Populate city filter dropdown
     */
    populateCityFilter() {
        const cityFilter = document.getElementById('cityFilter');
        if (!cityFilter) return;
        
        const cities = [...new Set(
            this.suppliers.map(s => s.city).filter(Boolean)
        )].sort();
        
        cityFilter.innerHTML = '<option value="">All Cities</option>' +
            cities.map(city => `
                <option value="${this.escapeHtml(city)}">
                    ${this.escapeHtml(city)}
                </option>
            `).join('');
    },
    
    /**
     * Handle search input
     */
    handleSearch(searchTerm) {
        this.applyFilters();
    },
    
    /**
     * Apply all filters (search + city)
     */
    applyFilters() {
        let filtered = [...this.suppliers];
        
        // Apply search filter
        if (this.currentSearchTerm) {
            const term = this.currentSearchTerm.toLowerCase();
            filtered = filtered.filter(supplier => {
                return (supplier.supplierName || '').toLowerCase().includes(term) ||
                       (supplier.city || '').toLowerCase().includes(term) ||
                       (supplier.country || '').toLowerCase().includes(term) ||
                       (supplier.contactPerson || '').toLowerCase().includes(term) ||
                       (supplier.email || '').toLowerCase().includes(term);
            });
        }
        
        // Apply city filter
        const cityFilter = document.getElementById('cityFilter');
        if (cityFilter && cityFilter.value) {
            filtered = filtered.filter(s => s.city === cityFilter.value);
        }
        
        this.filteredSuppliers = filtered;
        this.renderTable();
    },
    
    /**
     * Clear search
     */
    clearSearch() {
        const searchInput = document.getElementById('supplierSearch');
        if (searchInput) {
            searchInput.value = '';
        }
        this.currentSearchTerm = '';
        this.applyFilters();
    },
    
    /**
     * Open modal to edit supplier
     */
    editSupplier(id) {
        const supplier = this.suppliers.find(s => s.id === id);
        if (!supplier) {
            console.error('Supplier not found:', id);
            this.showError('Supplier not found');
            return;
        }
        
        // Update modal title
        document.getElementById('supplierModalTitle').innerHTML = 
            '<span><i class="fas fa-edit"></i> Edit Supplier</span>' +
            '<span class="close" onclick="closeSupplierModal()">&times;</span>';
        
        // Populate form fields
        document.getElementById('supplierId').value = supplier.id;
        document.getElementById('supplierName').value = supplier.supplierName || '';
        document.getElementById('contactPerson').value = supplier.contactPerson || '';
        document.getElementById('supplierEmail').value = supplier.email || '';
        document.getElementById('supplierPhone').value = supplier.phone || '';
        document.getElementById('supplierAddress').value = supplier.address || '';
        document.getElementById('supplierCity').value = supplier.city || '';
        document.getElementById('supplierCountry').value = supplier.country || '';
        
        // Show modal
        document.getElementById('supplierModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    },
    
    /**
     * Show delete confirmation modal
     */
    showDeleteModal(id) {
        const supplier = this.suppliers.find(s => s.id === id);
        if (!supplier) return;
        
        document.getElementById('deleteSupplierId').value = id;
        
        // Optional: Show supplier name in delete modal
        const modalBody = document.querySelector('#deleteSupplierModal .modal-body');
        if (modalBody) {
            const warningText = modalBody.querySelector('.warning-text');
            if (warningText) {
                warningText.textContent = `Are you sure you want to delete "${supplier.supplierName}"? This action cannot be undone.`;
            }
        }
        
        document.getElementById('deleteSupplierModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    },
    
    /**
     * Handle form submission
     */
    async handleSubmit(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        const id = document.getElementById('supplierId').value;
        const formData = {
            supplierName: document.getElementById('supplierName').value.trim(),
            contactPerson: document.getElementById('contactPerson').value.trim(),
            email: document.getElementById('supplierEmail').value.trim(),
            phone: document.getElementById('supplierPhone').value.trim(),
            address: document.getElementById('supplierAddress').value.trim(),
            city: document.getElementById('supplierCity').value.trim(),
            country: document.getElementById('supplierCountry').value.trim()
        };
        
        // Validation
        if (!formData.supplierName) {
            this.showError('Supplier name is required');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }
        
        if (id) {
            formData.id = id;
        }
        
        try {
            const action = id ? 'update' : 'create';
            const response = await fetch(`?ajax=1&entity=supplier&action=${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(
                    id ? 'Supplier updated successfully!' : 'Supplier added successfully!'
                );
                closeSupplierModal();
                await this.loadSuppliers();
            } else {
                this.showError(result.message || 'Failed to save supplier');
            }
        } catch (error) {
            console.error('Error saving supplier:', error);
            this.showError('Failed to save supplier. Please try again.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    },
    
    /**
     * Delete supplier
     */
    async deleteSupplier(id) {
        if (!id) return;
        
        try {
            const response = await fetch('?ajax=1&entity=supplier&action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Supplier deleted successfully!');
                closeDeleteSupplierModal();
                await this.loadSuppliers();
            } else {
                this.showError(result.message || 'Failed to delete supplier');
            }
        } catch (error) {
            console.error('Error deleting supplier:', error);
            this.showError('Failed to delete supplier. Please try again.');
        }
    },
    
    // ===== UTILITY FUNCTIONS =====
    
    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return '-';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        } catch (e) {
            return 'Invalid Date';
        }
    },
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    /**
     * Show error notification
     */
    showError(message) {
        this.showNotification(message, 'error');
    },
    
    /**
     * Show success notification
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    },
    
    /**
     * Show notification toast
     */
    showNotification(message, type = 'success') {
        // Remove any existing notifications
        const existing = document.querySelectorAll('.notification');
        existing.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}-notification`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${this.escapeHtml(message)}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
};

// ===== GLOBAL HELPER FUNCTIONS =====

/**
 * Open supplier modal for adding new supplier
 */
function openSupplierModal() {
    document.getElementById('supplierModalTitle').innerHTML = 
        '<span><i class="fas fa-truck"></i> Add New Supplier</span>' +
        '<span class="close" onclick="closeSupplierModal()">&times;</span>';
    
    document.getElementById('supplierForm').reset();
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('supplierName')?.focus();
    }, 100);
}

/**
 * Close supplier modal
 */
function closeSupplierModal() {
    document.getElementById('supplierModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

/**
 * Close delete supplier modal
 */
function closeDeleteSupplierModal() {
    document.getElementById('deleteSupplierModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

/**
 * Handle supplier form submission (called from form onsubmit)
 */
async function handleSupplierSubmit(e) {
    e.preventDefault();
    await SuppliersModule.handleSubmit(e);
}

/**
 * Confirm and delete supplier
 */
async function confirmDeleteSupplier() {
    const id = document.getElementById('deleteSupplierId').value;
    
    if (!id) {
        SuppliersModule.showError('No supplier selected');
        return;
    }
    
    await SuppliersModule.deleteSupplier(id);
}

// ===== EVENT LISTENERS =====

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const supplierModal = document.getElementById('supplierModal');
    const deleteModal = document.getElementById('deleteSupplierModal');
    
    if (event.target === supplierModal) {
        closeSupplierModal();
    }
    if (event.target === deleteModal) {
        closeDeleteSupplierModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const supplierModal = document.getElementById('supplierModal');
        const deleteModal = document.getElementById('deleteSupplierModal');
        
        if (supplierModal && supplierModal.style.display === 'block') {
            closeSupplierModal();
        }
        if (deleteModal && deleteModal.style.display === 'block') {
            closeDeleteSupplierModal();
        }
    }
});

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Suppliers Module ready for initialization');
});

// Export for use in other modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SuppliersModule;
}


// ==================== TRACKING MODULE ====================
/**
 * TRACKING MODULE - FIXED & OPTIMIZED VERSION
 * Handles order tracking, updates, and visualization
 */

const TrackingModule = {
    trackingData: [],
    filteredData: [],
    currentSearchTerm: '',
    currentStatusFilter: '',
    
    /**
     * Initialize the tracking module
     */
    init() {
        console.log('Initializing Tracking Module...');
        this.loadTracking();
        this.setupEventListeners();
    },
    
    /**
     * Set up all event listeners
     */
    setupEventListeners() {
        // Search input
        const searchInput = document.getElementById('trackingSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentSearchTerm = e.target.value;
                this.applyFilters();
            });
        }
        
        // Status filter
        const statusFilter = document.getElementById('trackingStatusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.currentStatusFilter = e.target.value;
                this.applyFilters();
            });
        }
        
        // Update tracking form
        const form = document.getElementById('updateTrackingForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleUpdateTracking(e));
        }
        
        // Modal backdrop click to close
        const modal = document.getElementById('updateTrackingModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeUpdateModal();
                }
            });
        }
        
        // Location select - auto-fill custom input
        const locationSelect = document.getElementById('trackingCurrentLocation');
        const customLocation = document.getElementById('trackingCustomLocation');
        if (locationSelect && customLocation) {
            locationSelect.addEventListener('change', (e) => {
                if (e.target.value) {
                    customLocation.value = e.target.value;
                }
            });
        }
    },
    
    /**
     * Load tracking data from server
     */
    async loadTracking() {
        const container = document.getElementById('trackingCardsContainer');
        const loading = document.getElementById('trackingLoading');
        
        if (loading) loading.style.display = 'block';
        
        try {
            const response = await fetch('../../controllers/get_tracking.php');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.trackingData = data.tracking || [];
                this.filteredData = [...this.trackingData];
                this.renderTracking();
                this.updateStats();
            } else {
                this.showEmptyState(data.message || 'No tracking data available');
            }
        } catch (error) {
            console.error('Error loading tracking:', error);
            this.showError('Failed to load tracking information. Please try refreshing the page.');
        } finally {
            if (loading) loading.style.display = 'none';
        }
    },
    
    /**
     * Apply search and status filters
     */
    applyFilters() {
        let filtered = [...this.trackingData];
        
        // Apply search filter
        if (this.currentSearchTerm) {
            const term = this.currentSearchTerm.toLowerCase();
            filtered = filtered.filter(order => {
                return (order.order_reference || '').toLowerCase().includes(term) ||
                       (order.customer_name || '').toLowerCase().includes(term) ||
                       (order.tracking_number || '').toLowerCase().includes(term) ||
                       (order.customer_email || '').toLowerCase().includes(term);
            });
        }
        
        // Apply status filter
        if (this.currentStatusFilter) {
            filtered = filtered.filter(order => order.order_status === this.currentStatusFilter);
        }
        
        this.filteredData = filtered;
        this.renderTracking();
    },
    
    /**
     * Render tracking cards
     */
    renderTracking() {
        const container = document.getElementById('trackingCardsContainer');
        if (!container) return;
        
        if (this.filteredData.length === 0) {
            const message = this.currentSearchTerm || this.currentStatusFilter 
                ? 'No orders match your search criteria' 
                : 'No tracking data found';
            this.showEmptyState(message);
            return;
        }
        
        container.innerHTML = this.filteredData
            .map(order => this.createTrackingCard(order))
            .join('');
    },
    
    /**
     * Create a single tracking card HTML
     */
    createTrackingCard(order) {
        const statusClass = (order.order_status || 'pending').replace(/_/g, '-');
        const statusText = this.getStatusText(order.order_status);
        const statusColor = this.getStatusColor(order.order_status);
        const date = this.formatDate(order.created_at);
        
        return `
            <div class="tracking-card" style="border-left: 4px solid ${statusColor}">
                <div class="tracking-card-header">
                    <div class="tracking-card-info">
                        <h3>${this.escapeHtml(order.order_reference || 'N/A')}</h3>
                        <div class="tracking-meta">
                            <span><i class="fas fa-user"></i> ${this.escapeHtml(order.customer_name || 'Unknown')}</span>
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
                            <h4><i class="fas fa-user-circle"></i> Customer Details</h4>
                            <p><i class="fas fa-envelope"></i> ${this.escapeHtml(order.customer_email || 'N/A')}</p>
                            <p><i class="fas fa-phone"></i> ${this.escapeHtml(order.customer_phone || 'N/A')}</p>
                        </div>
                        
                        ${order.current_location ? `
                            <div class="sidebar-section">
                                <h4><i class="fas fa-map-marker-alt"></i> Current Location</h4>
                                <p>${this.escapeHtml(order.current_location)}</p>
                            </div>
                        ` : ''}
                        
                        ${order.estimated_delivery ? `
                            <div class="sidebar-section">
                                <h4><i class="fas fa-calendar-check"></i> Estimated Delivery</h4>
                                <p>${this.formatDate(order.estimated_delivery)}</p>
                            </div>
                        ` : ''}
                        
                        <div class="sidebar-section">
                            <h4><i class="fas fa-money-bill-wave"></i> Total Amount</h4>
                            <p><strong>₱${this.formatCurrency(order.total_amount)}</strong></p>
                        </div>
                        
                        <div class="sidebar-section">
                            <h4><i class="fas fa-credit-card"></i> Payment Status</h4>
                            <p><span class="status-badge status-${order.payment_status || 'pending'}">${this.capitalizeFirst(order.payment_status || 'Pending')}</span></p>
                        </div>
                    </div>
                </div>
                
                <div class="tracking-card-actions">
                    <button class="btn-track-action btn-update-tracking" onclick="TrackingModule.openUpdateModal(${order.id})">
                        <i class="fas fa-edit"></i> Update Tracking
                    </button>
                    ${order.customer_email ? `
                        <button class="btn-track-action btn-notify-customer" onclick="TrackingModule.notifyCustomer(${order.id})">
                            <i class="fas fa-envelope"></i> Notify Customer
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    },
    
    /**
     * Create timeline visualization
     */
    createTimeline(order) {
        const statuses = [
            'pending', 
            'confirmed', 
            'preparing', 
            'ready_to_ship', 
            'in_transit', 
            'out_for_delivery', 
            'delivered'
        ];
        
        const currentIndex = statuses.indexOf(order.order_status);
        
        if (currentIndex === -1) {
            return '<div class="timeline-step"><div class="timeline-title">Status Unknown</div></div>';
        }
        
        return statuses.slice(0, currentIndex + 1).map((status, index) => {
            const isActive = index === currentIndex;
            const isCompleted = index < currentIndex;
            const stepClass = isActive ? 'active' : (isCompleted ? 'completed' : '');
            
            return `
                <div class="timeline-step ${stepClass}">
                    <div class="timeline-icon">
                        <i class="fas ${this.getStatusIcon(status)}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">${this.getStatusText(status)}</div>
                        ${isActive && order.current_location ? `
                            <div class="timeline-location">
                                <i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(order.current_location)}
                            </div>
                        ` : ''}
                        ${isActive && order.updated_at ? `
                            <div class="timeline-time">
                                ${this.formatDateTime(order.updated_at)}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');
    },
    
    /**
     * Get icon for status
     */
    getStatusIcon(status) {
        const iconMap = {
            pending: 'fa-clock',
            confirmed: 'fa-check-circle',
            preparing: 'fa-boxes',
            ready_to_ship: 'fa-dolly',
            in_transit: 'fa-shipping-fast',
            out_for_delivery: 'fa-truck',
            delivered: 'fa-check-double',
            cancelled: 'fa-times-circle'
        };
        return iconMap[status] || 'fa-question-circle';
    },
    
    /**
     * Get human-readable status text
     */
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
        return statusMap[status] || this.capitalizeFirst(status);
    },
    
    /**
     * Get color for status
     */
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
    
    /**
     * Update statistics display
     */
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
        
        this.updateStatElement('trackingTotalOrders', total);
        this.updateStatElement('trackingInTransit', inTransit);
        this.updateStatElement('trackingOutForDelivery', outForDelivery);
        this.updateStatElement('trackingDeliveredToday', deliveredToday);
    },
    
    /**
     * Safely update stat element
     */
    updateStatElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    },
    
    /**
     * Open update tracking modal
     */
    openUpdateModal(orderId) {
        const order = this.trackingData.find(o => o.id == orderId);
        
        if (!order) {
            console.error('Order not found:', orderId);
            alert('Order not found. Please refresh and try again.');
            return;
        }
        
        // Populate form fields
        this.setInputValue('trackingOrderId', order.id);
        this.setInputValue('trackingOrderRef', order.order_reference);
        this.setInputValue('trackingCustomerName', order.customer_name);
        this.setInputValue('trackingOrderStatus', order.order_status);
        this.setInputValue('trackingPaymentStatus', order.payment_status);
        this.setInputValue('trackingCurrentLocation', order.current_location || '');
        this.setInputValue('trackingCustomLocation', order.current_location || '');
        this.setInputValue('trackingNumber', order.tracking_number || '');
        this.setInputValue('trackingEstimatedDelivery', order.estimated_delivery || '');
        this.setInputValue('trackingDescription', '');
        
        // Set notification checkbox
        const notificationCheckbox = document.getElementById('trackingSendNotification');
        if (notificationCheckbox) {
            notificationCheckbox.checked = true;
        }
        
        // Show modal
        const modal = document.getElementById('updateTrackingModal');
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    },
    
    /**
     * Close update modal
     */
    closeUpdateModal() {
        const modal = document.getElementById('updateTrackingModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        const form = document.getElementById('updateTrackingForm');
        if (form) {
            form.reset();
        }
    },
    
    /**
     * Handle tracking update form submission
     */
    async handleUpdateTracking(e) {
        e.preventDefault();
        
        const formData = {
            order_id: this.getInputValue('trackingOrderId'),
            order_status: this.getInputValue('trackingOrderStatus'),
            payment_status: this.getInputValue('trackingPaymentStatus'),
            current_location: this.getInputValue('trackingCustomLocation') || 
                            this.getInputValue('trackingCurrentLocation'),
            tracking_number: this.getInputValue('trackingNumber'),
            estimated_delivery: this.getInputValue('trackingEstimatedDelivery'),
            description: this.getInputValue('trackingDescription'),
            send_notification: document.getElementById('trackingSendNotification')?.checked || false
        };
        
        // Validate required fields
        if (!formData.order_id || !formData.order_status || !formData.payment_status) {
            alert('Please fill in all required fields');
            return;
        }
        
        if (!formData.description) {
            alert('Please provide an update description');
            return;
        }
        
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
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                alert('Tracking updated successfully!');
                this.closeUpdateModal();
                await this.loadTracking();
            } else {
                alert(result.message || 'Failed to update tracking. Please try again.');
            }
        } catch (error) {
            console.error('Error updating tracking:', error);
            alert('An error occurred while updating tracking. Please try again.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    },
    
    /**
     * Notify customer about order status
     */
    async notifyCustomer(orderId) {
        const order = this.trackingData.find(o => o.id == orderId);
        
        if (!order) {
            alert('Order not found');
            return;
        }
        
        if (!confirm(`Send notification to ${order.customer_name} about order ${order.order_reference}?`)) {
            return;
        }
        
        try {
            const response = await fetch('../../controllers/notify_customer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Customer notification sent successfully!');
            } else {
                alert(result.message || 'Failed to send notification');
            }
        } catch (error) {
            console.error('Error sending notification:', error);
            alert('Failed to send notification. Please try again.');
        }
    },
    
    /**
     * Show empty state message
     */
    showEmptyState(message) {
        const container = document.getElementById('trackingCardsContainer');
        if (!container) return;
        
        container.innerHTML = `
            <div class="tracking-empty">
                <i class="fas fa-map-marked-alt"></i>
                <h3>No Tracking Information</h3>
                <p>${this.escapeHtml(message)}</p>
            </div>
        `;
    },
    
    /**
     * Show error message
     */
    showError(message) {
        const container = document.getElementById('trackingCardsContainer');
        if (!container) return;
        
        container.innerHTML = `
            <div class="tracking-empty">
                <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                <h3>Error</h3>
                <p>${this.escapeHtml(message)}</p>
                <button onclick="TrackingModule.loadTracking()" class="btn-refresh" style="margin-top: 20px;">
                    <i class="fas fa-sync-alt"></i> Retry
                </button>
            </div>
        `;
    },
    
    // ===== UTILITY FUNCTIONS =====
    
    setInputValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
        }
    },
    
    getInputValue(id) {
        const element = document.getElementById(id);
        return element ? element.value : '';
    },
    
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        } catch (e) {
            return 'Invalid Date';
        }
    },
    
    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return 'Invalid Date';
        }
    },
    
    formatCurrency(amount) {
        if (!amount) return '0.00';
        return parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    
    capitalizeFirst(text) {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - ready to initialize Tracking Module');
});

// ==================== QUOTATION MODULE ====================
const QuotationModule = {
    quotations: [],
    
    async init() {
        await this.loadOfficers();
        await this.loadFromDatabase();
    },
    
    async loadOfficers() {
        try {
            const response = await fetch('quotation_api.php?action=fetch_officers');
            const result = await response.json();
            if (result.success) {
                const officerSelect = document.getElementById('officer');
                if (officerSelect) {
                    officerSelect.innerHTML = '<option value="">Select Officer</option>';
                    result.data.forEach(officer => {
                        const option = document.createElement('option');
                        option.value = officer.code;
                        option.textContent = officer.name;
                        officerSelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error loading officers:', error);
        }
    },
    
    async loadFromDatabase() {
        try {
            const response = await fetch('quotation_api.php?action=fetch');
            const result = await response.json();
            if (result.success) {
                this.quotations = result.data;
                this.renderTable();
            }
        } catch (error) {
            console.error('Error loading quotations:', error);
        }
    },
    
    renderTable() {
        const tbody = document.getElementById('quotationTableBody');
        if (!tbody) return;
        tbody.innerHTML = this.quotations.length > 0 
            ? this.quotations.map(q => `<tr><td colspan="11">Quotation #${q.id}</td></tr>`).join('')
            : '<tr><td colspan="11">No quotations found</td></tr>';
    }
};

function openQuotationModal() {
    document.getElementById('quotationModal').style.display = 'block';
}

function closeQuotationModal() {
    document.getElementById('quotationModal').style.display = 'none';
}

function closeDeleteQuotationModal() {
    document.getElementById('deleteQuotationModal').style.display = 'none';
}

function confirmDeleteQuotation() {
    alert('Delete quotation');
    closeDeleteQuotationModal();
}

// ==================== ORDERS MODULE ====================
const OrdersModule = {
    async loadOrders() {
        console.log('✅ Orders loaded');
    }
};

// ==================== PRODUCT MODULES ====================
const BulkActions = {
    init() {
        this.checkboxes = document.querySelectorAll('.product-checkbox-input');
        this.bulkActionsBar = document.getElementById('bulkActionsBar');
        this.checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateBulkActions());
        });
    },
    updateBulkActions() {
        const selectedCount = document.querySelectorAll('.product-checkbox-input:checked').length;
        if (selectedCount > 0) {
            this.bulkActionsBar.style.display = 'flex';
            document.getElementById('selectedCount').textContent = selectedCount;
        } else {
            this.bulkActionsBar.style.display = 'none';
        }
    }
};

const ProductSearch = {
    init() {
        const searchInput = document.getElementById('productSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }
    },
    handleSearch(term) {
        const productItems = document.querySelectorAll('.product-item');
        productItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(term.toLowerCase()) ? '' : 'none';
        });
    }
};

const ProductPreview = {
    previewImages: [],
    currentSlide: 0,
    init() {
        const imageInput = document.getElementById('product-images');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }
    },
    handleImageUpload(event) {
        console.log('Images uploaded');
    },
    nextSlide() {
        if (this.currentSlide < this.previewImages.length - 1) this.currentSlide++;
    },
    prevSlide() {
        if (this.currentSlide > 0) this.currentSlide--;
    }
};

function nextSlide() { ProductPreview.nextSlide(); }
function prevSlide() { ProductPreview.prevSlide(); }

// Function to open edit modal and fetch product data
function openEditModal(productId) {
    // Show the modal
    document.getElementById('editProductModal').style.display = 'block';
    
    // Fetch product data via AJAX
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate the form fields
                document.getElementById('editProductId').value = data.product.id;
                document.getElementById('editDisplayName').value = data.product.displayName;
                document.getElementById('editBrandName').value = data.product.brandName;
                document.getElementById('editPrice').value = data.product.price;
                document.getElementById('editCategory').value = data.product.category;
                document.getElementById('editStockQuantity').value = data.product.stockQuantity;
                document.getElementById('editWarranty').value = data.product.warranty;
                document.getElementById('editDescription').value = data.product.description;
            } else {
                alert('Error loading product data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load product data');
        });
}

function closeEditModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('editProductModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
function closeDeleteModal() { document.getElementById('deleteProductModal').style.display = 'none'; }
function closeBulkDeleteModal() { document.getElementById('bulkDeleteModal').style.display = 'none'; }

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    UserDropdown.init();
    BulkActions.init();
    ProductSearch.init();
    ProductPreview.init();
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        const modals = ['editProductModal', 'deleteProductModal', 'bulkDeleteModal', 'supplierModal', 
                        'deleteSupplierModal', 'staffModal', 'deleteStaffModal', 'quotationModal', 
                        'deleteQuotationModal', 'updateTrackingModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) modal.style.display = 'none';
        });
    };
    
    console.log('✅ Admin Dashboard initialized');
});

// Dynamic brand loading
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');

    if (categorySelect && brandSelect) {
        categorySelect.addEventListener('change', function() {
            const category = this.value;
            brandSelect.innerHTML = '<option value="">Loading...</option>';
            brandSelect.disabled = true;

            if (!category) {
                brandSelect.innerHTML = '<option value="">Select a category first</option>';
                return;
            }

            fetch(`../../controllers/brand_data.php?category=${encodeURIComponent(category)}`)
                .then(response => response.json())
                .then(brands => {
                    brandSelect.innerHTML = '<option value="">Select brand</option>';
                    brands.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand;
                        option.textContent = brand;
                        brandSelect.appendChild(option);
                    });
                    brandSelect.disabled = false;
                })
                .catch(() => {
                    brandSelect.innerHTML = '<option value="">Failed to load</option>';
                });
        });
    }
});



</script>

</body>
</html>