<?php
session_start();

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

// Dashboard Statistics Functions
function get_stats($conn) {
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

function get_recent_orders($conn) {
    return [];
}

function get_most_sold_product($conn) {
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

// Fetch data
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
    <title>Solar Power - Staff</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../../assets/img/logo_no_background.png" alt="Solar Power Logo">
            </a>    
        </div>

        <div class="menu-label">MARKETING</div>
        <div class="menu-item active" onclick="showPage('dashboard', 'Dashboard')">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </div>
        <div class="menu-item" onclick="showPage('product', 'Product')">
            <i class="fas fa-box"></i>
            <span>Product</span>
        </div>
        <div class="menu-item" onclick="showPage('tracking', 'Tracking')">
            <i class="fas fa-map-marker-alt"></i>
            <span>Tracking</span>
        </div>
        <div class="menu-item" onclick="showPage('quotation', 'Quotation')">
            <i class="fas fa-file-invoice"></i>
            <span>Quotation</span>
        </div>
        <div class="menu-item" onclick="showPage('orders', 'Orders')">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
        </div>
        <div class="menu-item" onclick="showPage('suppliers', 'Suppliers')">
            <i class="fas fa-truck"></i>
            <span>Suppliers</span>
        </div>
        <div class="menu-item" onclick="showPage('clients', 'Clients')">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </div>

        <div class="menu-label">REPORTS</div>
        <div class="menu-item" onclick="showPage('records', 'Records')">
            <i class="fas fa-file-alt"></i>
            <span>Records</span>
        </div>

        <div class="menu-label">SYSTEM</div>
        <div class="menu-item" onclick="showPage('settings', 'Settings')">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </div>
    </aside>

    <main class="main-content">
        <div class="header">
            <div class="header-left">
                <h1 id="page-title">Dashboard</h1>
                <p>Good day, <strong><?php echo htmlspecialchars($fullName); ?></strong>! Hope you're doing well.</p>
            </div>
            <div class="user-menu">
                <div class="user-avatar">
                    <span><?php echo $initials; ?></span>
                </div>
                <div class="dropdown-menu" id="userDropdown">
                    <div class="dropdown-header"><?php echo htmlspecialchars($fullName); ?></div>
                    <ul>
                        <li><i class="fas fa-user-circle"></i> View Profile</li>
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

            <div class="content-section">
                <div class="section-title">Recent Orders</div>
                <table>
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Product Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_orders)): ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>
                                        <div class="client-cell">
                                            <div class="client-avatar"><?php echo strtoupper(substr($order['clientFName'], 0, 1) . substr($order['clientLName'], 0, 1)); ?></div>
                                            <span><?php echo htmlspecialchars($order['clientFName'] . ' ' . $order['clientLName']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['productName']); ?></td>
                                    <td><span class="status-badge status-<?php echo strtolower($order['orderStatus']); ?>"><?php echo htmlspecialchars($order['orderStatus']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><p class="empty-state" style="text-align: center; padding: 10px;">No recent orders found.</p></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <div class="section-title">Most Sale Product</div>
                <?php if ($most_sold_product): ?>
                    <div class="most-sold-product-card">
                        <strong><?php echo htmlspecialchars($most_sold_product['productName']); ?></strong> 
                        <span class="sales-count"><?php echo htmlspecialchars(number_format($most_sold_product['totalSold'])); ?> units sold</span>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        No sales data available
                    </div>
                <?php endif; ?>
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
            <div class="quotation-container"> <div class="quotation-header">
                    <h3><i class="fas fa-shopping-cart"></i> Order Management</h3>
                    <button class="btn-refresh" onclick="OrdersModule.loadOrders()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                
                <div class="quotation-table-wrapper">
                    <table class="quotation-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>System Type</th>
                                <th>Total Amount</th>
                                <th>Date</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div id="suppliers" class="suppliers-stats">
            <div class="stat-box">
                <h4>Total Suppliers</h4>
                <div class="value" id="totalSuppliers">0</div>
            </div>
            <div class="stat-box">
                <h4>Active This Month</h4>
                <div class="value" id="activeSuppliers">0</div>
            </div>
            <div class="stat-box">
                <h4>New Suppliers</h4>
                <div class="value" id="newSuppliers">0</div>
            </div>
            <div class="stat-box">
                <h4>Total Cities</h4>
                <div class="value" id="totalCities">0</div>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="suppliers-header">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="supplierSearch" placeholder="Search suppliers by name, city, or country...">
            </div>
            <button class="btn-primary" onclick="openSupplierModal()">
                <i class="fas fa-plus"></i> Add Supplier
            </button>
        </div>

        <!-- Suppliers Table -->
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
                    <tr>
                        <td colspan="8" class="loading-state">
                            <i class="fas fa-spinner fa-spin"></i> Loading suppliers...
                        </td>
                    </tr>
                </tbody>
            </table>
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

<script>
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
                headers: { 'Content-Type': 'application/json' },
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

const OrdersModule = {
    ordersData: [],

    async loadOrders() {
        const tableBody = document.getElementById('ordersTableBody');
        tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Loading orders...</td></tr>';

        try {
            // Replace with your actual API endpoint
            const response = await fetch('../controllers/get_orders.php');
            this.ordersData = await response.json();
            
            this.renderOrdersTable();
            // Optional: Update the TrackingModule if it shares the same data
            if (window.TrackingModule) {
                TrackingModule.trackingData = this.ordersData;
                TrackingModule.renderTrackingCards();
            }
        } catch (error) {
            console.error("Error loading orders:", error);
            tableBody.innerHTML = '<tr><td colspan="8" style="color:red; text-align:center;">Failed to load orders.</td></tr>';
        }
    },

    renderOrdersTable() {
        const tableBody = document.getElementById('ordersTableBody');
        
        if (this.ordersData.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No orders found.</td></tr>';
            return;
        }

        tableBody.innerHTML = this.ordersData.map(order => `
            <tr>
                <td><strong>#${order.order_reference}</strong></td>
                <td>${order.customer_name}</td>
                <td><span class="badge-system">${order.system_type}</span></td>
                <td>₱${Number(order.total_amount).toLocaleString()}</td>
                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                <td>
                    <span class="status-pill status-${order.payment_status.toLowerCase()}">
                        ${order.payment_status}
                    </span>
                </td>
                <td>
                    <span class="status-pill status-${order.order_status.toLowerCase()}">
                        ${order.order_status.replace('_', ' ')}
                    </span>
                </td>
                <td>
                    <button class="btn-action view" onclick="OrdersModule.viewOrderDetails(${order.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action edit" onclick="TrackingModule.openUpdateModal(${order.id})">
                        <i class="fas fa-truck"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.updateStats();
    },

    updateStats() {
        // Logic to update the counts in your Tracking stats grid
        document.getElementById('trackingTotalOrders').textContent = this.ordersData.length;
        document.getElementById('trackingInTransit').textContent = 
            this.ordersData.filter(o => o.order_status === 'in_transit').length;
        document.getElementById('trackingOutForDelivery').textContent = 
            this.ordersData.filter(o => o.order_status === 'out_for_delivery').length;
    }
};

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
function openEditModal(productId) {
    const modal = document.getElementById('editProductModal');
    const loader = document.getElementById('editLoading');

    modal.style.display = 'flex';
    loader.style.display = 'block';

    fetch(`get_product.php?id=${productId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);

            document.getElementById('editProductId').value = data.id;
            document.getElementById('editDisplayName').value = data.displayName;
            document.getElementById('editBrandName').value = data.brandName;
            document.getElementById('editPrice').value = data.price;
            document.getElementById('editCategory').value = data.category;
            document.getElementById('editStockQuantity').value = data.stockQuantity;
            document.getElementById('editWarranty').value = data.warranty;
            document.getElementById('editDescription').value = data.description;

            loader.style.display = 'none';
        })
        .catch(err => {
            loader.innerHTML = `<p style="color:red">${err.message}</p>`;
        });
}

document.addEventListener('DOMContentLoaded', () => {

const editForm = document.getElementById('editProductForm');
if (!editForm) return;

editForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    // 🔍 DEBUG — KEEP THIS FOR NOW
    console.log('Submitting:', [...formData.entries()]);

    fetch('edit_product.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log('Response:', data);

        if (data.success) {
            closeEditModal();
            location.reload(); // TEMP: easiest way to confirm success
        } else {
            alert(data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Request failed');
    });
});

});


function refreshProductList() {
    fetch('product-list.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('productTable').innerHTML = html;
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

document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');

    if (!categorySelect || !brandSelect) return;

    categorySelect.addEventListener('change', function () {
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
            carousel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
</body>
</html>