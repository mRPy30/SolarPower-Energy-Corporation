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
    'clients' => $conn->query("SELECT COUNT(*) FROM client")->fetch_row()[0],
    'products' => $conn->query("SELECT COUNT(*) FROM product")->fetch_row()[0],
    'orders' => $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0],
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
            if (!isset($row['staffFName']))
                $row['staffFName'] = '';
            if (!isset($row['staffLName']))
                $row['staffLName'] = '';
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


// Fetch archived products
include_once __DIR__ . "/../../controllers/includes/fetch_archived_products.php";

// Fetch archived quotations
include_once __DIR__ . "/../../controllers/includes/fetch_archived_quotations.php";

// Fetch archived suppliers
include_once __DIR__ . "/../../controllers/includes/fetch_archived_suppliers.php";


// Fetch data
$dashboard_analytics = get_dashboard_analytics($conn);
$lowStock = $dashboard_analytics['low_stock'];
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
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $status = isset($_GET['status']) ? trim($_GET['status']) : '';
                $payment = isset($_GET['payment']) ? trim($_GET['payment']) : '';

                $orderQuery = "SELECT id, order_reference, customer_name, customer_email,
                               total_amount, created_at, payment_method, payment_status, order_status
                               FROM orders WHERE order_status != 'archived'";
                $params = [];
                $types = '';

                if ($search !== '') {
                    $orderQuery .= " AND (customer_name LIKE ? OR order_reference LIKE ? OR customer_email LIKE ?)";
                    $sp = '%' . $search . '%';
                    $params[] = $sp;
                    $params[] = $sp;
                    $params[] = $sp;
                    $types .= 'sss';
                }
                if ($status !== '') {
                    $orderQuery .= " AND order_status = ?";
                    $params[] = $status;
                    $types .= 's';
                }
                if ($payment !== '') {
                    $orderQuery .= " AND payment_method = ?";
                    $params[] = $payment;
                    $types .= 's';
                }
                $orderQuery .= " ORDER BY created_at DESC";

                $stmt = $conn->prepare($orderQuery);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $orders = [];
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
                $stmt->close();

                echo json_encode(['success' => true, 'data' => $orders]);
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

                // Fetch full supplier row for archiving
                $checkStmt = $conn->prepare("SELECT * FROM supplier WHERE id = ?");
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

                // Create archived_suppliers table if it doesn't exist
                $conn->query("CREATE TABLE IF NOT EXISTS `archived_suppliers` (
                    `archive_id` int(11) NOT NULL AUTO_INCREMENT,
                    `original_id` int(11) NOT NULL,
                    `supplierName` varchar(255) NOT NULL,
                    `contactPerson` varchar(255) DEFAULT NULL,
                    `email` varchar(100) DEFAULT NULL,
                    `phone` varchar(20) DEFAULT NULL,
                    `address` text DEFAULT NULL,
                    `city` varchar(100) DEFAULT NULL,
                    `country` varchar(100) DEFAULT NULL,
                    `registrationDate` timestamp NULL DEFAULT NULL,
                    `deleted_by` int(11) DEFAULT NULL,
                    `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`archive_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

                // Copy supplier to archived_suppliers
                $archStmt = $conn->prepare("INSERT INTO archived_suppliers (original_id, supplierName, contactPerson, email, phone, address, city, country, registrationDate, deleted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $deleted_by = $_SESSION['user_id'] ?? null;
                $archStmt->bind_param(
                    "issssssssi",
                    $supplier['id'],
                    $supplier['supplierName'],
                    $supplier['contactPerson'],
                    $supplier['email'],
                    $supplier['phone'],
                    $supplier['address'],
                    $supplier['city'],
                    $supplier['country'],
                    $supplier['registrationDate'],
                    $deleted_by
                );
                $archStmt->execute();
                $archStmt->close();

                // Now delete from supplier table
                $stmt = $conn->prepare("DELETE FROM supplier WHERE id = ?");
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Supplier archived and deleted successfully',
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
                $sId = $_SESSION['staff_id'];

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

// ── PRG: Handle add_product POST before any HTML output ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $conn_post = mysqli_connect($servername, $username, $password, $dbname);
    if ($conn_post->connect_error) {
        $_SESSION['add_product_msg'] = 'Connection failed: ' . $conn_post->connect_error;
        $_SESSION['add_product_msg_type'] = 'error';
    } else {
        $category = $conn_post->real_escape_string($_POST['category'] ?? '');
        $brand = $conn_post->real_escape_string($_POST['brand'] ?? '');
        $productName = $conn_post->real_escape_string($_POST['product-name'] ?? '');
        $warranty = $conn_post->real_escape_string($_POST['warranty'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $stockQuantity = (int) ($_POST['stock-quantity'] ?? 0);
        $description = $conn_post->real_escape_string($_POST['description'] ?? '');
        $imagePath = 'path/to/uploaded/image.jpg';

        if (empty($category) || empty($brand) || empty($productName) || $price <= 0 || $stockQuantity < 0) {
            $_SESSION['add_product_msg'] = 'Please fill all required fields correctly.';
            $_SESSION['add_product_msg_type'] = 'error';
        } else {
            $stmt = $conn_post->prepare("INSERT INTO product (displayName, brandName, price, category, stockQuantity, warranty, description, imagePath, postedByStaffId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $_SESSION['add_product_msg'] = 'Database error: ' . $conn_post->error;
                $_SESSION['add_product_msg_type'] = 'error';
            } else {
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
                            if ($count >= $maxImages)
                                break;
                            if ($_FILES['product-images']['error'][$key] !== 0)
                                continue;

                            $ext = strtolower(pathinfo($_FILES['product-images']['name'][$key], PATHINFO_EXTENSION));
                            if (!in_array($ext, $allowedTypes))
                                continue;

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

                    $_SESSION['add_product_msg'] = "Product '{$productName}' added successfully with {$count} image(s)!";
                    $_SESSION['add_product_msg_type'] = 'success';
                } else {
                    $_SESSION['add_product_msg'] = 'Error adding product: ' . $stmt->error;
                    $_SESSION['add_product_msg_type'] = 'error';
                }
                $stmt->close();
            }
        }
        $conn_post->close();
    }
    // PRG redirect — converts POST to GET, prevents duplicate on reload
    header("Location: dashboard(2).php");
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

    <style>
        /* ======= INQUIRIES PAGE STYLES ======= */
        .inq-wrap {
            padding: 28px 24px
        }

        .inq-topbar {
            margin-bottom: 22px
        }

        .inq-topbar h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 4px
        }

        .inq-topbar h2 i {
            color: #FFC107;
            margin-right: 8px
        }

        .inq-topbar p {
            color: #888;
            font-size: 13px;
            margin: 0
        }

        /* Stats */
        .inq-stats {
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            flex-wrap: wrap
        }

        .inq-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border-radius: 12px;
            padding: 14px 20px;
            flex: 1;
            min-width: 130px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, .06);
            border: 1px solid #f0f0f0
        }

        .inq-stat i {
            font-size: 22px
        }

        .inq-stat-num {
            display: block;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.1
        }

        .inq-stat-lbl {
            display: block;
            font-size: 11px;
            color: #999;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .4px
        }

        .inq-s-total {
            border-left: 4px solid #6c757d
        }

        .inq-s-total i {
            color: #6c757d
        }

        .inq-s-new {
            border-left: 4px solid #FFC107
        }

        .inq-s-new i {
            color: #FFC107
        }

        .inq-s-read {
            border-left: 4px solid #17a2b8
        }

        .inq-s-read i {
            color: #17a2b8
        }

        .inq-s-replied {
            border-left: 4px solid #28a745
        }

        .inq-s-replied i {
            color: #28a745
        }

        /* Toolbar */
        .inq-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap
        }

        .inq-search {
            flex: 1;
            min-width: 200px;
            padding: 9px 14px 9px 36px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat 11px center
        }

        .inq-search:focus {
            border-color: #FFC107;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, .15)
        }

        .inq-filter-sel {
            padding: 9px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #fff;
            cursor: pointer
        }

        .inq-filter-sel:focus {
            border-color: #FFC107
        }

        /* Table */
        .inq-table-wrap {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
            border: 1px solid #f0f0f0;
            overflow: hidden
        }

        .inq-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px
        }

        .inq-table thead tr {
            background: #2a7a5b;
        }

        .inq-table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 700;
            color: #ffffff;
            font-size: 11px;
            letter-spacing: .5px;
            border-bottom: 2px solid #eee;
            white-space: nowrap
        }

        .inq-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle
        }

        .inq-table tbody tr:hover {
            background: #fffdf0
        }

        .inq-table tbody tr:last-child td {
            border-bottom: none
        }

        .inq-td-num {
            color: #bbb;
            font-weight: 600;
            font-size: 12px;
            width: 40px
        }

        .inq-empty-row td {
            text-align: center;
            padding: 52px 20px;
            color: #bbb
        }

        .inq-empty-row i {
            font-size: 40px;
            display: block;
            margin-bottom: 10px
        }

        /* Avatar */
        .inq-name-cell {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .inq-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFC107, #FF9800);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            flex-shrink: 0
        }

        .inq-fullname {
            font-weight: 600;
            color: #1a1a2e;
            font-size: 13px
        }

        .inq-new-pill {
            display: inline-block;
            background: #FFC107;
            color: #333;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 3px;
            letter-spacing: .5px
        }

        /* Contact cell */
        .inq-contact-cell {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #555
        }

        .inq-contact-cell span i {
            color: #FFC107;
            margin-right: 5px;
            width: 12px
        }

        /* Message preview */
        .inq-msg-preview {
            max-width: 240px;
            font-size: 12px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer
        }

        .inq-msg-preview:hover {
            color: #FFC107
        }

        /* Date */
        .inq-date-cell {
            font-size: 12px;
            color: #666;
            white-space: nowrap
        }

        .inq-date-cell small {
            display: block;
            color: #aaa
        }

        /* Status badges */
        .inq-badge {
            display: inline-block;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap
        }

        .inq-badge-new {
            background: #fff3cd;
            color: #856404
        }

        .inq-badge-read {
            background: #d1ecf1;
            color: #0c5460
        }

        .inq-badge-replied {
            background: #d4edda;
            color: #155724
        }

        /* Action buttons */
        .inq-actions {
            display: flex;
            gap: 5px
        }

        .inq-btn {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all .15s;
            flex-shrink: 0
        }

        .inq-btn-view {
            background: #e3f2fd;
            color: #1565c0
        }

        .inq-btn-view:hover {
            background: #1565c0;
            color: #fff
        }

        .inq-btn-read {
            background: #fff3cd;
            color: #856404
        }

        .inq-btn-read:hover {
            background: #e6ac00;
            color: #fff
        }

        .inq-btn-reply {
            background: #d4edda;
            color: #155724
        }

        .inq-btn-reply:hover {
            background: #155724;
            color: #fff
        }

        /* Modal overlay */
        .inq-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, .55);
            align-items: center;
            justify-content: center
        }

        .inq-modal-box {
            background: #fff;
            border-radius: 16px;
            width: 95%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25)
        }

        .inq-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            border-radius: 16px 16px 0 0
        }

        .inq-modal-head h3 {
            margin: 0;
            font-size: 16px;
            color: #1a1a2e
        }

        .inq-modal-head h3 i {
            color: #FFC107;
            margin-right: 8px
        }

        .inq-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            line-height: 1;
            padding: 0
        }

        .inq-modal-close:hover {
            color: #333
        }

        .inq-modal-body {
            padding: 24px
        }

        .inq-modal-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 14px
        }

        .inq-modal-info span {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #444;
            padding: 4px 0
        }

        .inq-modal-info span i {
            color: #FFC107;
            width: 16px;
            text-align: center
        }

        .inq-modal-msg {
            background: #fffbf0;
            border: 1px solid #ffe082;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 18px
        }

        .inq-modal-msg label {
            font-size: 11px;
            font-weight: 700;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: .5px;
            display: block;
            margin-bottom: 8px
        }

        .inq-modal-msg p {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
            color: #333
        }

        .inq-modal-footer {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .inq-modal-act {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .15s
        }

        .inq-modal-act-read {
            background: #fff3cd;
            color: #856404
        }

        .inq-modal-act-read:hover {
            background: #e6ac00;
            color: #fff
        }

        .inq-modal-act-reply {
            background: #d4edda;
            color: #155724
        }

        .inq-modal-act-reply:hover {
            background: #155724;
            color: #fff
        }

        @media(max-width:768px) {

            .inq-table th:nth-child(4),
            .inq-table td:nth-child(4) {
                display: none
            }

            .inq-stats {
                gap: 8px
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <aside class="sidebar">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="logo">
                <a href="dashboard.php">
                    <img src="../../assets/img/new_logo.png" alt="Solar Power Logo">
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
            <div class="menu-item" onclick="showPage('brands', 'Brands')" data-tooltip="Brands">
                <i class="fas fa-trademark"></i>
                <span>Brands</span>
            </div>
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

            <div class="menu-label">ARCHIVE</div>
            <div class="menu-item" onclick="showPage('archive', 'Archive')" data-tooltip="Archive">
                <i class="fas fa-map-marker-alt"></i>
                <span>Archived</span>
            </div>

            <div class="menu-label">SUPPLY MANAGEMENT</div>
            <div class="menu-item" onclick="showPage('suppliers', 'Suppliers')" data-tooltip="Suppliers">
                <i class="fas fa-truck"></i>
                <span>Suppliers</span>
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
                    <div class="user-avatar staff-header-avatar staff-header-avatar-small">
                        <?php if (!empty($current_staff['profile_picture']) && file_exists('../../uploads/profiles/' . $current_staff['profile_picture'])): ?>
                            <img src="../../uploads/profiles/<?= htmlspecialchars($current_staff['profile_picture']) ?>" alt="Profile" class="staff-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="staff-avatar-initials" style="display:none;"><?php echo $initials; ?></span>
                        <?php else: ?>
                            <span class="staff-avatar-initials"><?php echo $initials; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="dropdown-menu" id="userDropdown">
                        <div class="dropdown-header"><?php echo htmlspecialchars($fullName); ?></div>
                        <ul>
                            <li><a href="../../controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </li>
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


                <div class="dashboard-details-container"
                    style="display: flex; gap: 20px; margin-top: 25px; flex-wrap: wrap;">

                    <div class="details-card"
                        style="flex: 2; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-clock" style="color: #ffc107;"></i> Recent Orders
                        </h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr
                                    style="text-align: left; border-bottom: 2px solid #f4f4f4; color: #888; font-size: 13px;">
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
                                                <span class="status-badge"
                                                    style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 20px; font-size: 11px; text-transform: uppercase; font-weight: 600;">
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

                    <div class="details-card"
                        style="flex: 1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; min-width: 300px;">
                        <h3 style="margin-bottom: 20px; color: #333;">Top Selling Product</h3>
                        <?php if ($best_seller): ?>
                            <div style="padding: 20px; border: 2px dashed #f1f1f1; border-radius: 10px;">
                                <div
                                    style="background: #fff9db; width: 60px; height: 60px; line-height: 60px; border-radius: 50%; margin: 0 auto 15px;">
                                    <i class="fas fa-medal" style="font-size: 28px; color: #f1c40f;"></i>
                                </div>
                                <h2 style="font-size: 22px; margin-bottom: 10px; color: #2c3e50;">
                                    <?php echo htmlspecialchars($best_seller['product_name']); ?>
                                </h2>
                                <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                                    <div>
                                        <p style="font-size: 20px; font-weight: bold; color: #27ae60;">
                                            <?php echo $best_seller['total_qty']; ?>
                                        </p>
                                        <p style="font-size: 12px; color: #999; text-transform: uppercase;">Sold</p>
                                    </div>
                                    <div>
                                        <p style="font-size: 20px; font-weight: bold; color: #3498db;">
                                            <?php echo $best_seller['order_frequency']; ?>
                                        </p>
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
                        <div id="freeMap"
                            style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px; border: 1px solid #ddd;">
                        </div>
                    </div>

                </div>
            </div>
            <div id="inquiries" class="page-content">


                <div class="inq-wrap">

                    <!-- Stats -->
                    <div class="inq-stats">
                        <div class="inq-stat inq-s-total">
                            <i class="fas fa-inbox"></i>
                            <div><span class="inq-stat-num"><?php echo $total_inquiries; ?></span><span
                                    class="inq-stat-lbl">Total</span></div>
                        </div>
                        <div class="inq-stat inq-s-new">
                            <i class="fas fa-star"></i>
                            <div><span
                                    class="inq-stat-num"><?php echo count(array_filter($all_inquiries, fn($i) => $i['status'] === 'new')); ?></span><span
                                    class="inq-stat-lbl">New</span></div>
                        </div>
                        <div class="inq-stat inq-s-read">
                            <i class="fas fa-eye"></i>
                            <div><span
                                    class="inq-stat-num"><?php echo count(array_filter($all_inquiries, fn($i) => $i['status'] === 'read')); ?></span><span
                                    class="inq-stat-lbl">Read</span></div>
                        </div>
                        <div class="inq-stat inq-s-replied">
                            <i class="fas fa-reply"></i>
                            <div><span
                                    class="inq-stat-num"><?php echo count(array_filter($all_inquiries, fn($i) => $i['status'] === 'replied')); ?></span><span
                                    class="inq-stat-lbl">Replied</span></div>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="inq-toolbar">
                        <input type="text" id="inqSearch" class="inq-search"
                            placeholder="Search by name, email or phone…">
                        <select id="inqStatusFilter" class="inq-filter-sel">
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                        </select>
                    </div>

                    <!-- Table -->
                    <div class="inq-table-wrap">
                        <table class="inq-table">
                            <thead>
                                <tr>
                                    <th class="inq-td-num">#</th>
                                    <th>Customer</th>
                                    <th>Contact Info</th>
                                    <th>Message Preview</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inqBody">
                                <?php if (empty($all_inquiries)): ?>
                                    <tr class="inq-empty-row">
                                        <td colspan="7">
                                            <i class="fas fa-inbox"></i>
                                            No inquiries yet. Messages from the contact form will appear here.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_inquiries as $idx => $msg): ?>
                                        <?php
                                        $safeMsg = htmlspecialchars($msg['message'], ENT_QUOTES);
                                        $safeName = htmlspecialchars($msg['name'], ENT_QUOTES);
                                        $safeEmail = htmlspecialchars($msg['email'], ENT_QUOTES);
                                        $safePhone = htmlspecialchars($msg['phone'] ?? '', ENT_QUOTES);
                                        $dt = new DateTime($msg['created_at']);
                                        $shortMsg = mb_strlen($msg['message']) > 70 ? mb_substr($msg['message'], 0, 70) . '…' : $msg['message'];
                                        $jsonData = htmlspecialchars(json_encode($msg), ENT_QUOTES);
                                        ?>
                                        <tr class="inq-row" data-name="<?php echo strtolower($safeName); ?>"
                                            data-email="<?php echo strtolower($safeEmail); ?>"
                                            data-phone="<?php echo strtolower($safePhone); ?>"
                                            data-status="<?php echo $msg['status']; ?>">
                                            <td class="inq-td-num"><?php echo $idx + 1; ?></td>
                                            <td>
                                                <div class="inq-name-cell">
                                                    <div class="inq-avatar">
                                                        <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="inq-fullname"><?php echo $safeName; ?></div>
                                                        <?php if ($msg['status'] === 'new'): ?><span
                                                                class="inq-new-pill">NEW</span><?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inq-contact-cell">
                                                    <span><i class="fas fa-envelope"></i><?php echo $safeEmail; ?></span>
                                                    <?php if (!empty($msg['phone'])): ?><span><i
                                                                class="fas fa-phone"></i><?php echo $safePhone; ?></span><?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inq-msg-preview" title="<?php echo $safeMsg; ?>"
                                                    onclick="inqOpen(<?php echo $jsonData; ?>)">
                                                    <?php echo htmlspecialchars($shortMsg); ?>
                                                </div>
                                            </td>
                                            <td class="inq-date-cell">
                                                <?php echo $dt->format('M j, Y'); ?><small><?php echo $dt->format('g:i A'); ?></small>
                                            </td>
                                            <td>
                                                <span class="inq-badge inq-badge-<?php echo $msg['status']; ?>"
                                                    id="inqBadge<?php echo $msg['id']; ?>">
                                                    <?php echo ucfirst($msg['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="inq-actions">
                                                    <button class="inq-btn inq-btn-view"
                                                        onclick="inqOpen(<?php echo $jsonData; ?>)" title="View message">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($msg['status'] === 'new'): ?>
                                                        <button class="inq-btn inq-btn-read"
                                                            id="inqReadBtn<?php echo $msg['id']; ?>"
                                                            onclick="inqSetStatus(<?php echo $msg['id']; ?>, 'read')"
                                                            title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($msg['status'] !== 'replied'): ?>
                                                        <button class="inq-btn inq-btn-reply"
                                                            id="inqReplyBtn<?php echo $msg['id']; ?>"
                                                            onclick="inqSetStatus(<?php echo $msg['id']; ?>, 'replied')"
                                                            title="Mark as Replied">
                                                            <i class="fas fa-reply"></i>
                                                        </button>
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

                <!-- Inquiry Detail Modal -->
                <div class="inq-overlay" id="inqOverlay">
                    <div class="inq-modal-box">
                        <div class="inq-modal-head">
                            <h3><i class="fas fa-envelope-open-text"></i>Inquiry Details</h3>
                            <button class="inq-modal-close" onclick="inqClose()">&times;</button>
                        </div>
                        <div class="inq-modal-body" id="inqModalBody"></div>
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
                            $image_src = '../../assets/img/product-placeholder.png';

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
                                    <input type="checkbox" class="product-checkbox-input"
                                        data-product-id="<?php echo $product['id']; ?>">
                                </div>

                                <div class="product-image">
                                    <img src="<?php echo $image_src; ?>"
                                        alt="<?php echo htmlspecialchars($product['displayName']); ?>"
                                        onerror="this.src='../../assets/img/product-placeholder.png'">
                                </div>

                                <div class="product-content">
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['displayName']); ?></h3>
                                    <p class="product-brand"><?php echo htmlspecialchars($product['brandName']); ?></p>

                                    <div class="product-meta">
                                        <span
                                            class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
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
                        <p class="empty-state" style="text-align: center; padding: 20px;">No products found in the database.
                        </p>
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

                            <!-- Image Carousel -->
                            <div class="edit-product-carousel" id="editProductCarousel">
                                <div class="carousel-main">
                                    <div id="carouselImageContainer" class="carousel-image-container">
                                        <!-- Main image will be loaded here -->
                                        <div class="no-images-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>No images uploaded yet</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="carousel-counter" id="carouselCounter"></div>
                                <div class="carousel-thumbnails-wrapper">
                                    <button type="button" class="carousel-nav carousel-prev" id="carouselPrevBtn"
                                        onclick="carouselPrev()" style="display: none;">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <div id="carouselThumbnails" class="carousel-thumbnails">
                                        <!-- Thumbnails will be loaded here -->
                                    </div>
                                    <button type="button" class="carousel-nav carousel-next" id="carouselNextBtn"
                                        onclick="carouselNext()" style="display: none;">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Add New Images -->
                            <div class="form-group">
                                <label><i class="fas fa-plus-circle"></i> Add New Images</label>
                                <input type="file" name="new_images[]" id="newImagesInput" accept="image/*" multiple>
                                <small>You can select multiple images at once</small>
                                <div id="newImagesPreview" class="new-images-preview-grid"></div>
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

                            <div class="form-row" id="editMoqWrapper" style="display:none;">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-layer-group"></i> Min. Order Qty (MOQ)
                                        <span
                                            title="Minimum units a customer must order. Applies to Solar Panels and Mounting &amp; Accessories."
                                            style="cursor:help; color:#888;">&#9432;</span>
                                    </label>
                                    <input type="number" name="moq" id="editMoq" min="1" value="1">
                                    <small id="editMoqHint" style="color:#888;">Solar Panels: recommended MOQ ≥
                                        2</small>
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
                        <button type="button" onclick="confirmDeleteQuotation()" class="btn-confirm-delete">Yes,
                            Delete</button>
                    </div>
                </div>
            </div>


            <!-- ═══════════════ BRANDS PAGE ═══════════════ -->
            <div id="brands" class="page-content">
                <style>
                    /* ── Brands page ── */
                    .brands-wrap {
                        padding: 24px;
                    }

                    .brands-stats {
                        display: flex;
                        gap: 14px;
                        margin-bottom: 22px;
                        flex-wrap: wrap;
                    }

                    .brands-stat-box {
                        background: #fff;
                        border-radius: 12px;
                        padding: 18px 24px;
                        flex: 1;
                        min-width: 150px;
                        text-align: center;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
                        border-top: 4px solid #f59e0b;
                    }

                    .brands-stat-box h4 {
                        font-size: 11px;
                        color: #888;
                        letter-spacing: .6px;
                        margin-bottom: 6px;
                    }

                    .brands-stat-box .value {
                        font-size: 30px;
                        font-weight: 800;
                        color: #1e293b;
                    }

                    .brands-layout {
                        display: grid;
                        grid-template-columns: 320px 1fr;
                        gap: 22px;
                    }

                    @media(max-width:800px) {
                        .brands-layout {
                            grid-template-columns: 1fr;
                        }
                    }

                    .brands-card {
                        background: #fff;
                        border-radius: 14px;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
                        overflow: hidden;
                    }

                    .brands-card-head {
                        padding: 16px 20px;
                        border-bottom: 1px solid #f0f3fa;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }

                    .brands-card-head h2 {
                        font-size: 15px;
                        font-weight: 700;
                        flex: 1;
                        color: #1e293b;
                    }

                    .brands-card-body {
                        padding: 20px;
                    }

                    /* form */
                    .bfg {
                        margin-bottom: 14px;
                    }

                    .bfg label {
                        display: block;
                        font-size: 12px;
                        font-weight: 600;
                        color: #555;
                        margin-bottom: 5px;
                    }

                    .bfg input,
                    .bfg select {
                        width: 100%;
                        padding: 9px 13px;
                        border: 1.5px solid #e2e8f0;
                        border-radius: 8px;
                        font-size: 14px;
                        background: #fafbff;
                        transition: border .2s;
                    }

                    .bfg input:focus,
                    .bfg select:focus {
                        outline: none;
                        border-color: #f59e0b;
                        background: #fff;
                    }

                    .btn-brand-add {
                        width: 100%;
                        padding: 10px;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        background: linear-gradient(135deg, #f59e0b, #ffc107);
                        color: #fff;
                        font-size: 14px;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        transition: opacity .2s;
                    }

                    .btn-brand-add:hover {
                        opacity: .88;
                    }

                    /* toolbar */
                    .brands-toolbar {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                        margin-bottom: 14px;
                    }

                    .brands-toolbar input,
                    .brands-toolbar select {
                        flex: 1;
                        min-width: 150px;
                        padding: 8px 13px;
                        border: 1.5px solid #e2e8f0;
                        border-radius: 8px;
                        font-size: 13px;
                        background: #fafbff;
                    }

                    .brands-toolbar input:focus,
                    .brands-toolbar select:focus {
                        outline: none;
                        border-color: #f59e0b;
                    }

                    /* table */
                    .brands-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 14px;
                    }

                    .brands-table thead th {
                        text-align: left;
                        padding: 10px 13px;
                        background: #f8fafc;
                        color: #64748b;
                        font-size: 11px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: .5px;
                        border-bottom: 2px solid #e9ecf3;
                    }

                    .brands-table tbody tr {
                        border-bottom: 1px solid #f1f3fa;
                        transition: background .15s;
                    }

                    .brands-table tbody tr:hover {
                        background: #f8fafd;
                    }

                    .brands-table td {
                        padding: 11px 13px;
                    }

                    .cat-badge {
                        display: inline-block;
                        padding: 3px 10px;
                        border-radius: 20px;
                        font-size: 11px;
                        font-weight: 600;
                        background: #e0f2fe;
                        color: #0369a1;
                    }

                    .cat-badge.panel {
                        background: #d1fae5;
                        color: #065f46;
                    }

                    .cat-badge.inverter {
                        background: #ede9fe;
                        color: #5b21b6;
                    }

                    .cat-badge.battery {
                        background: #fef9c3;
                        color: #92400e;
                    }

                    .cat-badge.mount {
                        background: #fee2e2;
                        color: #991b1b;
                    }

                    .cat-badge.package {
                        background: #f0fdf4;
                        color: #15803d;
                    }

                    .cat-badge.protect {
                        background: #fce7f3;
                        color: #9d174d;
                    }

                    .brand-action-btns {
                        display: flex;
                        gap: 6px;
                    }

                    .btn-brand-edit,
                    .btn-brand-del {
                        border: none;
                        cursor: pointer;
                        border-radius: 7px;
                        padding: 5px 10px;
                        font-size: 12px;
                        font-weight: 600;
                        transition: background .2s;
                    }

                    .btn-brand-edit {
                        background: #e0f2fe;
                        color: #0369a1;
                    }

                    .btn-brand-edit:hover {
                        background: #bae6fd;
                    }

                    .btn-brand-del {
                        background: #fee2e2;
                        color: #b91c1c;
                    }

                    .btn-brand-del:hover {
                        background: #fecaca;
                    }

                    .brands-empty td {
                        text-align: center;
                        padding: 32px;
                        color: #aaa;
                    }

                    /* modals */
                    .brand-overlay {
                        display: none;
                        position: fixed;
                        inset: 0;
                        background: rgba(0, 0, 0, .45);
                        z-index: 1000;
                        align-items: center;
                        justify-content: center;
                    }

                    .brand-overlay.open {
                        display: flex;
                    }

                    .brand-modal {
                        background: #fff;
                        border-radius: 14px;
                        padding: 26px;
                        width: 420px;
                        max-width: 94vw;
                        box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
                        animation: bpopIn .2s ease;
                    }

                    @keyframes bpopIn {
                        from {
                            transform: scale(.9);
                            opacity: 0
                        }

                        to {
                            transform: scale(1);
                            opacity: 1
                        }
                    }

                    .brand-modal h3 {
                        font-size: 17px;
                        font-weight: 700;
                        margin-bottom: 18px;
                    }

                    .brand-modal-actions {
                        display: flex;
                        gap: 10px;
                        justify-content: flex-end;
                        margin-top: 18px;
                    }

                    .btn-bcancel {
                        padding: 8px 18px;
                        border: 1.5px solid #e2e8f0;
                        border-radius: 8px;
                        background: #fff;
                        cursor: pointer;
                        font-size: 13px;
                    }

                    .btn-bsave {
                        padding: 8px 18px;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        background: linear-gradient(135deg, #f59e0b, #ef4444);
                        color: #fff;
                        font-size: 13px;
                        font-weight: 600;
                    }

                    .btn-bdelete {
                        padding: 8px 18px;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        background: #dc2626;
                        color: #fff;
                        font-size: 13px;
                        font-weight: 600;
                    }

                    .confirm-center {
                        text-align: center;
                    }

                    .confirm-center p {
                        color: #555;
                        margin: 10px 0 4px;
                    }

                    .confirm-center .warn-txt {
                        color: #ef4444;
                        font-size: 12px;
                    }

                    /* toast */
                    .brand-toast {
                        position: fixed;
                        bottom: 22px;
                        right: 22px;
                        z-index: 9999;
                        padding: 12px 18px;
                        border-radius: 10px;
                        font-size: 13px;
                        font-weight: 600;
                        color: #fff;
                        box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
                        transform: translateY(80px);
                        opacity: 0;
                        transition: transform .3s, opacity .3s;
                        pointer-events: none;
                    }

                    .brand-toast.show {
                        transform: translateY(0);
                        opacity: 1;
                    }

                    .brand-toast.success {
                        background: #16a34a;
                    }

                    .brand-toast.error {
                        background: #dc2626;
                    }
                </style>

                <div class="brands-wrap">

                    <!-- Stats -->
                    <div class="brands-stats">
                        <div class="brands-stat-box">
                            <h4>TOTAL BRANDS</h4>
                            <div class="value" id="brandStatTotal">–</div>
                        </div>
                        <div class="brands-stat-box" style="border-color:#3b82f6;">
                            <h4>CATEGORIES</h4>
                            <div class="value" id="brandStatCats">–</div>
                        </div>
                    </div>

                    <div class="brands-layout">

                        <!-- LEFT: Add brand form -->
                        <div>
                            <div class="brands-card">
                                <div class="brands-card-head">
                                    <i class="fas fa-plus-circle" style="color:#f59e0b;font-size:17px;"></i>
                                    <h2>Add New Brand</h2>
                                </div>
                                <div class="brands-card-body">
                                    <div class="bfg">
                                        <label><i class="fas fa-trademark"></i> Brand Name *</label>
                                        <input type="text" id="bNewName" placeholder="e.g. Jinko Solar">
                                    </div>
                                    <div class="bfg">
                                        <label><i class="fas fa-tag"></i> Category *</label>
                                        <select id="bNewCategory">
                                            <option value="">— Select Category —</option>
                                        </select>
                                    </div>
                                    <button class="btn-brand-add" onclick="BrandsModule.addBrand()">
                                        <i class="fas fa-plus"></i> Add Brand
                                    </button>
                                </div>
                            </div>

                            <!-- Tip card -->
                            <div class="brands-card" style="margin-top:18px;">
                                <div class="brands-card-head">
                                    <i class="fas fa-lightbulb" style="color:#f59e0b;font-size:17px;"></i>
                                    <h2>How it works</h2>
                                </div>
                                <div class="brands-card-body" style="font-size:13px;color:#666;line-height:1.9;">
                                    <p>Brands added here appear dynamically in the <strong>Add Product</strong> form
                                        when that category is selected.</p>
                                    <br>
                                    <p><i class="fas fa-check" style="color:#16a34a;"></i> Add brand → pick category →
                                        save</p>
                                    <p><i class="fas fa-check" style="color:#16a34a;"></i> Edit or remove brands anytime
                                    </p>
                                    <p><i class="fas fa-check" style="color:#16a34a;"></i> Product form updates
                                        immediately</p>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Brand table -->
                        <div class="brands-card">
                            <div class="brands-card-head">
                                <i class="fas fa-list" style="color:#2563eb;font-size:17px;"></i>
                                <h2>All Brands</h2>
                            </div>
                            <div class="brands-card-body">
                                <div class="brands-toolbar">
                                    <input type="text" id="bSearchInput" placeholder="🔍 Search brand…"
                                        oninput="BrandsModule.applyFilters()">
                                    <select id="bCatFilter" onchange="BrandsModule.applyFilters()">
                                        <option value="">All Categories</option>
                                    </select>
                                </div>
                                <table class="brands-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Brand Name</th>
                                            <th>Category</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="brandsTableBody">
                                        <tr class="brands-empty">
                                            <td colspan="4"><i class="fas fa-spinner fa-spin"></i> Loading…</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div><!-- end brands-layout -->
                </div><!-- end brands-wrap -->

                <!-- Edit Modal -->
                <div class="brand-overlay" id="bEditOverlay">
                    <div class="brand-modal">
                        <h3><i class="fas fa-edit" style="color:#f59e0b;"></i> Edit Brand</h3>
                        <input type="hidden" id="bEditId">
                        <div class="bfg">
                            <label>Brand Name *</label>
                            <input type="text" id="bEditName" placeholder="Brand name">
                        </div>
                        <div class="bfg">
                            <label>Category *</label>
                            <select id="bEditCategory"></select>
                        </div>
                        <div class="brand-modal-actions">
                            <button class="btn-bcancel" onclick="BrandsModule.closeEditModal()">Cancel</button>
                            <button class="btn-bsave" onclick="BrandsModule.saveEdit()"><i class="fas fa-save"></i>
                                Save</button>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirm Modal -->
                <div class="brand-overlay" id="bDeleteOverlay">
                    <div class="brand-modal confirm-center">
                        <h3 style="color:#dc2626;"><i class="fas fa-trash"></i> Delete Brand</h3>
                        <input type="hidden" id="bDeleteId">
                        <p id="bDeleteMsg">Are you sure?</p>
                        <p class="warn-txt">This action cannot be undone.</p>
                        <div class="brand-modal-actions" style="justify-content:center;">
                            <button class="btn-bcancel" onclick="BrandsModule.closeDeleteModal()">Cancel</button>
                            <button class="btn-bdelete" onclick="BrandsModule.confirmDelete()"><i
                                    class="fas fa-trash"></i> Yes, Delete</button>
                        </div>
                    </div>
                </div>

                <!-- Toast -->
                <div class="brand-toast" id="brandToast"></div>

            </div><!-- end #brands page-content -->
            <!-- ═══════════════ END BRANDS PAGE ═══════════════ -->

            <!-- Other page sections remain the same -->

            <div id="add-product" class="page-content add-product-page">
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

                <?php
                if (isset($_SESSION['add_product_msg'])) {
                    $msgType = $_SESSION['add_product_msg_type'] ?? 'success';
                    echo "<div class='alert {$msgType}'>" . htmlspecialchars($_SESSION['add_product_msg']) . "</div>";
                    unset($_SESSION['add_product_msg'], $_SESSION['add_product_msg_type']);
                }
                ?>

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
                                    <input type="text" id="product-name-input" name="product-name"
                                        placeholder="Enter product name" required>
                                </div>

                                <div class="form-group">
                                    <label for="warranty">
                                        <i class="fas fa-shield-alt"></i>
                                        Warranty <span class="required">*</span>
                                    </label>
                                    <input type="text" id="warranty" name="warranty" placeholder="e.g., 5 years"
                                        value="5 years" required>
                                </div>

                                <div class="form-row-group">
                                    <div class="form-group">
                                        <label for="price-input">
                                            <i class="fas fa-peso-sign"></i>
                                            Price <span class="required">*</span>
                                        </label>
                                        <div class="input-wrapper">
                                            <input type="number" id="price-input" name="price" placeholder="0.00"
                                                step="0.01" required>
                                            <span class="input-icon">PHP</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="stock-quantity-input">
                                            <i class="fas fa-boxes"></i>
                                            Stock Quantity <span class="required">*</span>
                                        </label>
                                        <input type="number" id="stock-quantity-input" name="stock-quantity"
                                            placeholder="0" min="0" required>
                                    </div>
                                </div>

                                <div class="form-row-group" id="moq-field-wrapper" style="display:none;">
                                    <div class="form-group">
                                        <label for="moq-input">
                                            <i class="fas fa-layer-group"></i>
                                            Min. Order Qty (MOQ)
                                            <span
                                                title="Minimum units a customer must order. Applies to Solar Panels and Mounting &amp; Accessories."
                                                style="cursor:help; color:#888;">&#9432;</span>
                                        </label>
                                        <input type="number" id="moq-input" name="moq" placeholder="1" min="1"
                                            value="1">
                                        <small id="moq-hint-text" style="color:#888;">Solar Panels default to MOQ of
                                            2</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description-input">
                                        <i class="fas fa-align-left"></i>
                                        Description <span class="required">*</span>
                                    </label>
                                    <textarea id="description-input" name="description"
                                        placeholder="Describe your product features, specifications, and benefits..."
                                        required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-image"></i>
                                        Product Images <span class="required">*</span>
                                    </label>

                                    <div class="file-upload-box">
                                        <input type="file" id="product-images" name="product-images[]" accept="image/*"
                                            multiple max="15">
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
                            <input type="text" id="trackingSearchInput"
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
                                        <option value="Distribution Hub - Quezon City">Distribution Hub - Quezon City
                                        </option>
                                        <option value="Distribution Hub - Cavite">Distribution Hub - Cavite</option>
                                        <option value="Distribution Hub - Laguna">Distribution Hub - Laguna</option>
                                        <option value="In Transit">In Transit</option>
                                        <option value="Out for Delivery">Out for Delivery</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                    <input type="text" id="trackingCustomLocation"
                                        placeholder="Or type custom location..." class="mt-2">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-truck"></i> Tracking Number</label>
                                    <input type="text" id="trackingNumber" placeholder="e.g., TRK-2025-001234">
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
                                    <textarea id="trackingDescription" rows="4" required
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
                            <button type="button" onclick="TrackingModule.closeUpdateModal()" class="btn-cancel">
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

                        <div style="display: flex; gap: 10px;">
                            <button class="btn-primary" style="background-color: #3498db;" onclick="exportOrdersPDF()">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                            <button class="btn-primary" style="background-color: #217346;"
                                onclick="exportOrdersExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            <button class="btn-refresh" onclick="OrdersModule.loadOrders()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- SUMMARY STATS -->
                    <div id="ordersSummary"
                        style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
                        <div
                            style="background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.08);border-left:4px solid #007bff;">
                            <div style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Total
                                Orders</div>
                            <div id="statTotal" style="font-size:26px;font-weight:800;color:#007bff;">—</div>
                        </div>
                        <div
                            style="background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.08);border-left:4px solid #ffc107;">
                            <div style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Pending
                            </div>
                            <div id="statPending" style="font-size:26px;font-weight:800;color:#ffc107;">—</div>
                        </div>
                        <div style="background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.08);border-left:4px solid #6f42c1;cursor:pointer;"
                            onclick="OrdersModule.filterByStatus('pending_verification')">
                            <div style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Needs
                                Verification</div>
                            <div id="statVerify" style="font-size:26px;font-weight:800;color:#6f42c1;">—</div>
                        </div>
                        <div
                            style="background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.08);border-left:4px solid #28a745;">
                            <div style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Paid</div>
                            <div id="statPaid" style="font-size:26px;font-weight:800;color:#28a745;">—</div>
                        </div>
                        <div
                            style="background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.08);border-left:4px solid #17a2b8;">
                            <div style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Total
                                Revenue</div>
                            <div id="statRevenue" style="font-size:20px;font-weight:800;color:#17a2b8;">—</div>
                        </div>
                    </div>

                    <!-- FILTERS -->
                    <div class="orders-filters">
                        <div class="orders-filter-group">
                            <label>Search</label>
                            <input type="text" id="orderSearch" placeholder="Name, email, order ID…">
                        </div>
                        <div class="orders-filter-group">
                            <label>Payment Status</label>
                            <select id="orderStatusFilter">
                                <option value="">All Status</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="pending_verification">Needs Verification</option>
                                <option value="failed">Failed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="orders-filter-group">
                            <label>Payment Method</label>
                            <select id="paymentFilter">
                                <option value="">All Methods</option>
                                <option value="instapay">InstaPay</option>
                                <option value="gcash">GCash</option>
                                <option value="maya">Maya</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div class="orders-filter-group">
                            <label>Receipt</label>
                            <select id="receiptFilter">
                                <option value="">All</option>
                                <option value="has_receipt">Has Receipt</option>
                                <option value="no_receipt">No Receipt</option>
                            </select>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="orders-table-wrapper">
                        <div id="ordersLoadingState" style="text-align:center;padding:40px;display:none;">
                            <i class="fas fa-spinner fa-spin" style="font-size:28px;color:#999;"></i>
                            <p style="color:#999;margin-top:10px;">Loading orders…</p>
                        </div>
                        <div id="ordersEmptyState" style="text-align:center;padding:40px;display:none;">
                            <i class="fas fa-inbox" style="font-size:40px;color:#ccc;"></i>
                            <p style="color:#aaa;margin-top:10px;">No orders found.</p>
                        </div>
                        <table class="orders-table" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th style="text-align:center;">Receipt</th>
                                    <th style="text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- ===== RECEIPT VIEWER MODAL ===== -->
            <div id="receiptModal"
                style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);align-items:center;justify-content:center;flex-direction:column;">
                <div
                    style="background:#fff;border-radius:14px;max-width:720px;width:95%;max-height:92vh;overflow:auto;position:relative;">
                    <div
                        style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eee;background:#f8f9fa;border-radius:14px 14px 0 0;">
                        <div>
                            <h3 style="margin:0;font-size:16px;"><i class="fas fa-receipt"
                                    style="color:#28a745;margin-right:8px;"></i>Payment Receipt</h3>
                            <small id="receiptOrderRef" style="color:#666;font-size:12px;"></small>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <a id="receiptDownloadBtn" href="#" download target="_blank"
                                style="padding:6px 14px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;font-size:13px;">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button onclick="closeReceiptModal()"
                                style="background:none;border:none;font-size:24px;cursor:pointer;color:#aaa;line-height:1;padding:0 4px;">&times;</button>
                        </div>
                    </div>
                    <div style="padding:24px;text-align:center;" id="receiptModalBody"></div>
                </div>
            </div>

            <!-- ===== ORDER DETAIL + VERIFY MODAL ===== -->
            <div id="orderDetailModal"
                style="display:none;position:fixed;z-index:9998;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);align-items:center;justify-content:center;">
                <div
                    style="background:#fff;border-radius:14px;max-width:660px;width:95%;max-height:92vh;overflow:auto;position:relative;">
                    <div
                        style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #eee;background:#f8f9fa;border-radius:14px 14px 0 0;">
                        <h3 style="margin:0;font-size:16px;"><i class="fas fa-file-invoice"
                                style="color:#007bff;margin-right:8px;"></i>Order Details</h3>
                        <button onclick="closeOrderDetailModal()"
                            style="background:none;border:none;font-size:24px;cursor:pointer;color:#aaa;line-height:1;padding:0 4px;">&times;</button>
                    </div>
                    <div style="padding:24px;" id="orderDetailBody"></div>
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
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-primary" style="background-color: #3498db;"
                                onclick="exportSuppliersPDF()">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                            <button class="btn-primary" style="background-color: #217346;"
                                onclick="exportSuppliersExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            <button class="btn-primary" onclick="openSupplierModal()">
                                <i class="fas fa-plus"></i> Add Supplier
                            </button>
                        </div>
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
            <div id="archive" class="page-content">

                <!-- Archive Product Details Section -->
                <div
                    style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin-top: 10px;">
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                        <div style="display: flex; align-items: center;">
                            <i class="bi bi-archive" style="color: #e74c3c; font-size: 24px; margin-right: 12px;"></i>
                            <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">Archive Product
                                Details</h4>
                        </div>
                        <span
                            style="background-color: #ffeaea; color: #e74c3c; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                            <?php echo $archived_product_count; ?> archived
                            product<?php echo $archived_product_count != 1 ? 's' : ''; ?>
                        </span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        #</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Product Name</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Brand</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Price</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Category</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Qty</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Warranty</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Date Archived</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($archived_products)): ?>
                                    <?php foreach ($archived_products as $index => $ap): ?>
                                        <tr style="border-bottom: 1px solid #dee2e6;"
                                            id="archive-row-<?php echo $ap['archive_id']; ?>">
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo $index + 1; ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50; font-weight: 500;">
                                                <?php echo htmlspecialchars($ap['displayName']); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($ap['brandName']); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                &#8369;<?php echo number_format($ap['price'], 2); ?></td>
                                            <td style="padding: 12px; font-size: 14px;"><span
                                                    style="background-color: #eaf4fe; color: #3498db; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;"><?php echo htmlspecialchars($ap['category']); ?></span>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo $ap['stockQuantity']; ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($ap['warranty'] ?? 'N/A'); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo date('M d, Y h:i A', strtotime($ap['deleted_at'])); ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <div style="display: flex; gap: 4px;">
                                                    <button onclick="restoreArchivedProduct(<?php echo $ap['archive_id']; ?>)"
                                                        class="green-action-btn" title="Restore Product">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button onclick="permanentDeleteProduct(<?php echo $ap['archive_id']; ?>)"
                                                        class="red-action-btn" title="Delete Permanently">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9"
                                            style="padding: 30px; text-align: center; color: #6c757d; font-size: 14px;">
                                            <i class="bi bi-inbox"
                                                style="font-size: 36px; display: block; margin-bottom: 10px; color: #dee2e6;"></i>
                                            No archived products found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Archive Quotation Details Section -->
                <div
                    style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin-top: 30px;">
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                        <div style="display: flex; align-items: center;">
                            <i class="bi bi-file-text" style="color: #f39c12; font-size: 24px; margin-right: 12px;"></i>
                            <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">Archived Quotation
                                Details</h4>
                        </div>
                        <span
                            style="background-color: #fff3e0; color: #f39c12; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                            <?php echo $archived_quotation_count; ?> archived
                            quotation<?php echo $archived_quotation_count != 1 ? 's' : ''; ?>
                        </span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        #</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Quotation No.</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Client Name</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Email</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Contact</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Location</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        System</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        kW</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Officer</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Status</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Date Archived</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($archived_quotations)): ?>
                                    <?php foreach ($archived_quotations as $qi => $aq): ?>
                                        <tr style="border-bottom: 1px solid #dee2e6;"
                                            id="archive-quotation-row-<?php echo $aq['archive_id']; ?>">
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;"><?php echo $qi + 1; ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50; font-weight: 500;">
                                                <?php echo htmlspecialchars($aq['quotation_number'] ?? 'N/A'); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($aq['client_name']); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($aq['email']); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($aq['contact'] ?? 'N/A'); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($aq['location'] ?? 'N/A'); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px;">
                                                <span
                                                    style="background-color: #e8f5e9; color: #2e7d32; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                                    <?php echo htmlspecialchars($aq['system_type'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo $aq['kw'] !== null ? number_format($aq['kw'], 2) : 'N/A'; ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo htmlspecialchars($aq['officer'] ?? 'N/A'); ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px;">
                                                <?php
                                                $statusColors = [
                                                    'SENT' => ['bg' => '#e3f2fd', 'text' => '#1565c0'],
                                                    'ONGOING' => ['bg' => '#fff3e0', 'text' => '#e65100'],
                                                    'APPROVED' => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
                                                    'CLOSED' => ['bg' => '#f3e5f5', 'text' => '#7b1fa2'],
                                                    'LOSS' => ['bg' => '#ffeaea', 'text' => '#c62828'],
                                                ];
                                                $st = strtoupper($aq['status'] ?? '');
                                                $bg = $statusColors[$st]['bg'] ?? '#f5f5f5';
                                                $tc = $statusColors[$st]['text'] ?? '#616161';
                                                ?>
                                                <span
                                                    style="background-color: <?php echo $bg; ?>; color: <?php echo $tc; ?>; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                                    <?php echo htmlspecialchars($aq['status'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?php echo date('M d, Y h:i A', strtotime($aq['deleted_at'])); ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <div style="display: flex; gap: 4px;">
                                                    <button onclick="restoreArchivedQuotation(<?php echo $aq['archive_id']; ?>)"
                                                        class="green-action-btn" title="Restore Quotation">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button onclick="permanentDeleteQuotation(<?php echo $aq['archive_id']; ?>)"
                                                        class="red-action-btn" title="Delete Permanently">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12"
                                            style="padding: 30px; text-align: center; color: #6c757d; font-size: 14px;">
                                            <i class="bi bi-inbox"
                                                style="font-size: 36px; display: block; margin-bottom: 10px; color: #dee2e6;"></i>
                                            No archived quotations found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Archive Suppliers Section -->
                <div
                    style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 30px; margin-top: 30px;">
                    <div
                        style="display: flex; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef;">
                        <i class="bi bi-archive" style="color: #f39c12; font-size: 24px; margin-right: 12px;"></i>
                        <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">Archive Suppliers</h4>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        #</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Supplier Name</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Contact Person</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Email</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Phone</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Location</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Registered</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Date Archived</th>
                                    <th
                                        style="padding: 12px; text-align: left; font-weight: 600; color: #6c757d; font-size: 14px; border-bottom: 2px solid #dee2e6;">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($archived_suppliers)): ?>
                                    <?php $sup_counter = 1;
                                    foreach ($archived_suppliers as $asup): ?>
                                        <tr id="archive-supplier-row-<?= $asup['archive_id'] ?>"
                                            style="border-bottom: 1px solid #dee2e6;">
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;"><?= $sup_counter++ ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= htmlspecialchars($asup['supplierName']) ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= htmlspecialchars($asup['contactPerson'] ?? '—') ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= htmlspecialchars($asup['email'] ?? '—') ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= htmlspecialchars($asup['phone'] ?? '—') ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= htmlspecialchars(($asup['city'] ?? '') . ', ' . ($asup['country'] ?? '')) ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= $asup['registrationDate'] ? date('M d, Y', strtotime($asup['registrationDate'])) : '—' ?>
                                            </td>
                                            <td style="padding: 12px; font-size: 14px; color: #2c3e50;">
                                                <?= date('M d, Y h:i A', strtotime($asup['deleted_at'])) ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <div style="display: flex; gap: 4px;">
                                                    <button onclick="restoreArchivedSupplier(<?= $asup['archive_id'] ?>)"
                                                        class="green-action-btn" title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button onclick="permanentDeleteSupplier(<?= $asup['archive_id'] ?>)"
                                                        class="red-action-btn" title="Delete Permanently">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9"
                                            style="padding: 30px; text-align: center; color: #6c757d; font-size: 14px;">No
                                            archived suppliers found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>




            </div>

            <!-- Add/Edit Supplier Modal -->
            <div id="supplierModal" class="staffModal">
                <div class="staffModal-content">
                    <div class="staffModal-header">
                        <h3 id="supplierModalTitle">
                            <i class="fas fa-truck"></i>
                            <span>Add New Supplier</span>
                        </h3>
                        <span class="close" onclick="closeSupplierModal()">&times;</span>
                    </div>

                    <form id="supplierForm" onsubmit="handleSubmit(event)">
                        <div class="staffModal-body">
                            <input type="hidden" id="supplierId">

                            <div class="staffModal-row">
                                <div class="staffModal-group">
                                    <label><i class="fas fa-building"></i> Supplier Name *</label>
                                    <input type="text" id="supplierName" required>
                                </div>

                                <div class="staffModal-group">
                                    <label><i class="fas fa-user"></i> Contact Person</label>
                                    <input type="text" id="contactPerson">
                                </div>
                            </div>

                            <div class="staffModal-row">
                                <div class="staffModal-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" id="email">
                                </div>

                                <div class="staffModal-group">
                                    <label><i class="fas fa-phone"></i> Phone</label>
                                    <input type="text" id="phone">
                                </div>
                            </div>

                            <div class="staffModal-group">
                                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                                <textarea id="address" rows="2"></textarea>
                            </div>

                            <div class="staffModal-row">
                                <div class="staffModal-group">
                                    <label><i class="fas fa-city"></i> City</label>
                                    <input type="text" id="city">
                                </div>

                                <div class="staffModal-group">
                                    <label><i class="fas fa-flag"></i> Country</label>
                                    <input type="text" id="country">
                                </div>
                            </div>
                        </div>

                        <div class="staffModal-footer">
                            <button type="button" onclick="closeSupplierModal()" class="staffModal-btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="staffModal-btn-primary">
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
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-primary" style="background-color: #3498db;" onclick="exportClientsPDF()">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                            <button class="btn-primary" style="background-color: #217346;"
                                onclick="exportClientsExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                    </div>

                    <!-- FILTERS -->
                    <div class="clients-filters">
                        <div class="clients-filter-group">
                            <label>Search</label>
                            <input type="text" id="clientSearch" placeholder="Search by name or email">
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
                        <div class="staff-header-avatar profile-avatar-large">
                            <?php if (!empty($current_staff['profile_picture']) && file_exists('../../uploads/profiles/' . $current_staff['profile_picture'])): ?>
                                <img src="../../uploads/profiles/<?= htmlspecialchars($current_staff['profile_picture']) ?>" alt="" class="staff-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="staff-avatar-initials" style="display:none;">
                                    <?php echo $initials; ?>
                                </div>
                            <?php else: ?>
                                <div class="staff-avatar-initials">
                                    <?php echo $initials; ?>
                                </div>
                            <?php endif; ?>
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
                                    <span
                                        id="headerEmail"><?php echo htmlspecialchars($current_staff['email']); ?></span>
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
                                <div class="info-value" id="displayFirstName">
                                    <?php echo htmlspecialchars($current_staff['firstName']); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Last Name</div>
                                <div class="info-value" id="displayLastName">
                                    <?php echo htmlspecialchars($current_staff['lastName']); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-value" id="displayEmail">
                                    <?php echo htmlspecialchars($current_staff['email']); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value" id="displayContact">
                                    <?php echo htmlspecialchars($current_staff['contact_number'] ?: 'Not set'); ?>
                                </div>
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
                                <div class="info-value">
                                    <?php echo date('F d, Y', strtotime($current_staff['created_at'])); ?>
                                </div>
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
                    <form id="editProfileForm" class="staffModal-form" enctype="multipart/form-data" onsubmit="handleUpdateProfile(event)">
                        <div class="staffModal-body">
                            <div class="staffModal-profile-upload">
                                <!-- Clicking this container triggers the hidden file input -->
                                <div class="staffModal-avatar-container" onclick="document.getElementById('profile_picture_input').click()">
                                    <?php if (!empty($current_staff['profile_picture']) && file_exists('../../uploads/profiles/' . $current_staff['profile_picture'])): ?>
                                        <img id="profile_preview" src="../../uploads/profiles/<?= htmlspecialchars($current_staff['profile_picture']) ?>" alt="" class="staff-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <!-- Initials fallback hidden if image exists -->
                                        <div id="profile_initials" class="staffModal-avatar-initials" style="display: none;">
                                            <?= htmlspecialchars(strtoupper(substr($current_staff['firstName'], 0, 1) . substr($current_staff['lastName'], 0, 1))) ?>
                                        </div>
                                    <?php else: ?>
                                        <img id="profile_preview" style="display:none;" alt="" class="staff-avatar-img">
                                        <div id="profile_initials" class="staffModal-avatar-initials">
                                            <?= htmlspecialchars(strtoupper(substr($current_staff['firstName'], 0, 1) . substr($current_staff['lastName'], 0, 1))) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="staffModal-avatar-overlay">
                                        <span>Upload</span>
                                    </div>
                                </div>
                                
                                <!-- Hidden file input -->
                                <input type="file" 
                                    id="profile_picture_input" 
                                    name="profile_picture" 
                                    accept="image/jpeg, image/png, image/gif, image/webp" 
                                    style="display: none;" 
                                    onchange="previewProfilePic(this)">
                            </div>
                            
                            <div class="staffModal-group">
                                <label><i class="fas fa-user"></i> First Name</label>
                                <input type="text" id="profileFirstName"
                                    value="<?php echo htmlspecialchars($current_staff['firstName']); ?>" required>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-user"></i> Last Name</label>
                                <input type="text" id="profileLastName"
                                    value="<?php echo htmlspecialchars($current_staff['lastName']); ?>" required>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" id="profileEmail"
                                    value="<?php echo htmlspecialchars($current_staff['email']); ?>" required>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-phone"></i> Contact Number</label>
                                <input type="tel" id="profileContactNumber"
                                    value="<?php echo htmlspecialchars($current_staff['contact_number']); ?>">
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
                                    <button type="button" class="toggle-password"
                                        onclick="togglePasswordField('currentPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-lock"></i> New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="newPassword" required>
                                    <button type="button" class="toggle-password"
                                        onclick="togglePasswordField('newPassword', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="staffModal-group">
                                <label><i class="fas fa-lock"></i> Confirm New Password</label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirmPassword" required>
                                    <button type="button" class="toggle-password"
                                        onclick="togglePasswordField('confirmPassword', this)">
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
    <?php include "includes/export-clients-pdf.php"; ?>
    <?php include "includes/export-clients-excel.php"; ?>
    <?php include "includes/export-orders-pdf.php"; ?>
    <?php include "includes/export-orders-excel.php"; ?>
    <?php include "includes/export-suppliers-pdf.php"; ?>
    <?php include "includes/export-suppliers-excel.php"; ?>
    <?php include "includes/export-quotations-excel.php"; ?>
    <?php include "includes/export-quotations-pdf.php"; ?>
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
        document.addEventListener('DOMContentLoaded', function () {
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

        // ======= INQUIRIES JS =======
        (function () {
            var search = document.getElementById('inqSearch');
            var filter = document.getElementById('inqStatusFilter');
            if (!search || !filter) return;
            function run() {
                var term = search.value.toLowerCase();
                var status = filter.value;
                document.querySelectorAll('#inqBody .inq-row').forEach(function (row) {
                    var ok = (!term || row.dataset.name.includes(term) || row.dataset.email.includes(term) || row.dataset.phone.includes(term))
                        && (!status || row.dataset.status === status);
                    row.style.display = ok ? '' : 'none';
                });
            }
            search.addEventListener('input', run);
            filter.addEventListener('change', run);
        })();

        function inqOpen(msg) {
            var sc = { new: 'background:#fff3cd;color:#856404', read: 'background:#d1ecf1;color:#0c5460', replied: 'background:#d4edda;color:#155724' }[msg.status] || 'background:#eee;color:#333';
            var dt = new Date(msg.created_at);
            var dtStr = dt.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) + ' at ' + dt.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
            var phone = msg.phone ? '<span><i class="fas fa-phone"></i>' + msg.phone + '</span>' : '';
            var readBtn = (msg.status !== 'read' && msg.status !== 'replied') ? '<button class="inq-modal-act inq-modal-act-read" onclick="inqSetStatus(' + msg.id + ',\'read\',true)"><i class="fas fa-check"></i> Mark as Read</button>' : '';
            var replyBtn = msg.status !== 'replied' ? '<button class="inq-modal-act inq-modal-act-reply" onclick="inqSetStatus(' + msg.id + ',\'replied\',true)"><i class="fas fa-reply"></i> Mark as Replied</button>' : '';

            document.getElementById('inqModalBody').innerHTML =
                '<div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">'
                + '<div class="inq-avatar" style="width:52px;height:52px;font-size:22px;flex-shrink:0;">' + msg.name.charAt(0).toUpperCase() + '</div>'
                + '<div><div style="font-size:18px;font-weight:700;color:#1a1a2e;">' + msg.name + '</div>'
                + '<small style="color:#888;">Submitted ' + dtStr + '</small></div>'
                + '<span class="inq-badge" style="margin-left:auto;' + sc + '">' + msg.status.charAt(0).toUpperCase() + msg.status.slice(1) + '</span></div>'
                + '<div class="inq-modal-info"><span><i class="fas fa-envelope"></i>' + msg.email + '</span>' + phone + '</div>'
                + '<div class="inq-modal-msg"><label><i class="fas fa-comment" style="margin-right:6px;"></i>Message</label>'
                + '<p>' + msg.message.replace(/\n/g, '<br>') + '</p></div>'
                + '<div class="inq-modal-footer">' + readBtn + replyBtn + '</div>';

            document.getElementById('inqOverlay').style.display = 'flex';
        }

        function inqClose() {
            document.getElementById('inqOverlay').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            var overlay = document.getElementById('inqOverlay');
            if (overlay) overlay.addEventListener('click', function (e) { if (e.target === this) inqClose(); });
        });

        function inqSetStatus(id, status, fromModal) {
            fetch(window.location.href + '?ajax=1&action=update_inquiry_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: status })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) { alert('Update failed: ' + (data.message || '')); return; }
                    // Update badge
                    var badge = document.getElementById('inqBadge' + id);
                    if (badge) {
                        badge.className = 'inq-badge inq-badge-' + status;
                        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    }
                    // Update row
                    document.querySelectorAll('#inqBody .inq-row').forEach(function (row) {
                        var hasBtn = row.querySelector('#inqReadBtn' + id) || row.querySelector('#inqReplyBtn' + id);
                        if (hasBtn) {
                            row.dataset.status = status;
                            var pill = row.querySelector('.inq-new-pill');
                            if (pill) pill.remove();
                            var rb = document.getElementById('inqReadBtn' + id);
                            var rpb = document.getElementById('inqReplyBtn' + id);
                            if (rb && (status === 'read' || status === 'replied')) rb.remove();
                            if (rpb && status === 'replied') rpb.remove();
                        }
                    });
                    if (fromModal) inqClose();
                })
                .catch(function () { alert('Network error. Please try again.'); });
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
        function previewProfilePic(input) {
            const file = input.files[0];
            if (file) {
                const maxSizeBytes = 2 * 1024 * 1024; // 2MB
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                // Client-side Validation
                if (file.size > maxSizeBytes) {
                    showToast('File size exceeds 2MB limit. Please choose a smaller image.', 'error');
                    input.value = ""; // Clear file
                    return;
                }

                if (!validTypes.includes(file.type)) {
                    showToast('Invalid file type. Please upload a JPG, PNG, GIF, or WEBP image.', 'error');
                    input.value = "";
                    return;
                }

                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profile_preview');
                    const initials = document.getElementById('profile_initials');
                    
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    
                    if (initials) {
                        initials.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        }

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

            // Create form data for file upload support
            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('firstName', firstName);
            formData.append('lastName', lastName);
            formData.append('email', email);
            formData.append('contact_number', contact);

            const fileInput = document.getElementById('profile_picture_input');
            if (fileInput.files.length > 0) {
                formData.append('profile_picture', fileInput.files[0]);
            }

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            try {
                const response = await fetch('profile_api.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message || 'Profile updated successfully!', 'success');

                    // If profile picture was updated, reload to reflect in header and profile
                    if (fileInput.files.length > 0) {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                        return;
                    }

                    // Update display values
                    document.getElementById('displayFirstName').textContent = firstName;
                    document.getElementById('displayLastName').textContent = lastName;
                    document.getElementById('displayEmail').textContent = email;
                    if(document.getElementById('headerEmail')) document.getElementById('headerEmail').textContent = email;
                    if(document.getElementById('displayContact')) document.getElementById('displayContact').textContent = contact || 'Not set';

                    // Update header
                    const profileName = document.querySelector('.profile-name');
                    if (profileName) {
                        profileName.textContent = `${firstName} ${lastName}`;
                    }

                    // Update avatar initials only if no picture exists
                    const profileAvatar = document.querySelector('.profile-avatar-large .staff-avatar-initials');
                    if (profileAvatar) {
                        const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                        profileAvatar.textContent = initials;
                    }
                    const smallAvatar = document.querySelector('.user-avatar .staff-avatar-initials');
                    if (smallAvatar) {
                        const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                        smallAvatar.textContent = initials;
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
                    headers: { 'Content-Type': 'application/json' },
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
        window.addEventListener('click', function (event) {
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
        document.addEventListener('keydown', function (event) {
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
                    body: JSON.stringify({ ...formData, action })
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
                    body: JSON.stringify({ id, action: 'delete' })
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
        window.onclick = function (event) {
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
        document.addEventListener('DOMContentLoaded', function () {
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
        window.showPage = function (pageId, pageTitle) {
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
        document.addEventListener('DOMContentLoaded', function () {
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
        document.addEventListener('DOMContentLoaded', function () {
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
                            <p><strong>₱${parseFloat(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></p>
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

        const ClientsModule = {
            init: function () {
                // Load data initially
                this.loadClients();

                // Add search functionality
                document.getElementById('clientSearch')?.addEventListener('input', (e) => {
                    this.filterClients(e.target.value);
                });
            },

            loadClients: function () {
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

            renderTable: function (clients) {
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

            filterClients: function (searchTerm) {
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

        // ==================== ORDERS MODULE ====================
        const OrdersModule = {
            orders: [],
            filtered: [],

            // ── Load ──────────────────────────────────────────────────────────────
            async loadOrders() {
                const tbody = document.getElementById('ordersTableBody');
                const table = document.getElementById('ordersTable');
                const loading = document.getElementById('ordersLoadingState');
                const empty = document.getElementById('ordersEmptyState');

                if (tbody) tbody.innerHTML = '';
                if (table) table.style.display = 'none';
                if (loading) loading.style.display = 'block';
                if (empty) empty.style.display = 'none';

                try {
                    const res = await fetch('dashboard.php?ajax=1&action=fetch_orders');
                    const json = await res.json();

                    if (!json.success) throw new Error(json.message || 'Server error');

                    this.orders = json.data || [];
                    this.filtered = [...this.orders];

                    this.updateStats();
                    this.renderOrders();
                    this.bindFilters();

                } catch (err) {
                    console.error('OrdersModule:', err);
                    if (tbody) tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;color:#e74c3c;padding:24px;">
                <i class="fas fa-exclamation-circle"></i> ${err.message}
            </td></tr>`;
                    if (table) table.style.display = 'table';
                } finally {
                    if (loading) loading.style.display = 'none';
                }
            },

            // ── Stats ─────────────────────────────────────────────────────────────
            updateStats() {
                const orders = this.orders;
                const total = orders.length;
                const pending = orders.filter(o => (o.payment_status || '').toLowerCase() === 'pending').length;
                const verify = orders.filter(o => (o.payment_status || '').toLowerCase() === 'pending_verification').length;
                const paid = orders.filter(o => (o.payment_status || '').toLowerCase() === 'paid').length;
                const revenue = orders
                    .filter(o => (o.payment_status || '').toLowerCase() === 'paid')
                    .reduce((s, o) => s + parseFloat(o.total_amount || 0), 0);

                this._setText('statTotal', total);
                this._setText('statPending', pending);
                this._setText('statVerify', verify);
                this._setText('statPaid', paid);
                this._setText('statRevenue', '₱' + revenue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                // Highlight badge when orders need verification
                const badge = document.getElementById('ordersBadge');
                if (badge) {
                    if (verify > 0) {
                        badge.textContent = verify + ' need verification';
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            },

            _setText(id, val) {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            },

            // ── Render ────────────────────────────────────────────────────────────
            renderOrders() {
                const tbody = document.getElementById('ordersTableBody');
                const table = document.getElementById('ordersTable');
                const empty = document.getElementById('ordersEmptyState');
                if (!tbody) return;

                if (this.filtered.length === 0) {
                    tbody.innerHTML = '';
                    if (table) table.style.display = 'none';
                    if (empty) empty.style.display = 'block';
                    return;
                }

                if (empty) empty.style.display = 'none';
                if (table) table.style.display = 'table';

                tbody.innerHTML = this.filtered.map(o => {
                    const date = o.created_at
                        ? new Date(o.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
                        : '—';
                    const amount = parseFloat(o.total_amount || 0)
                        .toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

                    const isManual = ['instapay', 'gcash'].some(m => (o.payment_method || '').toLowerCase().includes(m));
                    const hasReceipt = !!o.receipt_url;
                    const needsVerify = (o.payment_status || '').toLowerCase() === 'pending_verification';

                    // Receipt cell
                    let receiptCell;
                    if (hasReceipt) {
                        receiptCell = `<button onclick="viewReceipt('${this.esc(o.receipt_url)}','${this.esc(o.order_reference)}')"
                    style="background:#28a745;color:#fff;border:none;padding:5px 11px;border-radius:5px;cursor:pointer;font-size:12px;white-space:nowrap;">
                    <i class="fas fa-image"></i> View
                </button>`;
                    } else if (isManual) {
                        receiptCell = `<span style="color:#dc3545;font-size:12px;" title="No receipt uploaded">
                    <i class="fas fa-exclamation-triangle"></i> Missing
                </span>`;
                    } else {
                        receiptCell = `<span style="color:#ccc;font-size:12px;">—</span>`;
                    }

                    // Row highlight for pending verification
                    const rowStyle = needsVerify ? 'background:#faf0ff;' : '';

                    return `<tr style="${rowStyle}">
                <td style="font-weight:700;font-size:12px;">${this.esc(o.order_reference || '—')}</td>
                <td>
                    <div style="font-weight:600;">${this.esc(o.customer_name || '—')}</div>
                    <div style="font-size:11px;color:#888;">${this.esc(o.customer_email || '')}</div>
                </td>
                <td style="font-weight:700;">${amount}</td>
                <td style="font-size:12px;white-space:nowrap;">${date}</td>
                <td style="font-size:13px;">${this.formatMethod(o.payment_method || '')}</td>
                <td>${this.payBadge(o.payment_status)}</td>
                <td>${this.orderBadge(o.order_status)}</td>
                <td style="text-align:center;">${receiptCell}</td>
                <td style="text-align:center;">
                    <button onclick="OrdersModule.openDetail(${o.id})"
                        style="background:#007bff;color:#fff;border:none;padding:5px 11px;border-radius:5px;cursor:pointer;font-size:12px;">
                        <i class="fas fa-eye"></i> Details
                    </button>
                </td>
            </tr>`;
                }).join('');
            },

            // ── Detail Modal ──────────────────────────────────────────────────────
            openDetail(id) {
                const o = this.orders.find(x => x.id == id);
                if (!o) return;

                const modal = document.getElementById('orderDetailModal');
                const body = document.getElementById('orderDetailBody');
                const amount = parseFloat(o.total_amount || 0)
                    .toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
                const date = o.created_at ? new Date(o.created_at).toLocaleString('en-PH') : '—';
                const hasReceipt = !!o.receipt_url;
                const needsVerify = (o.payment_status || '').toLowerCase() === 'pending_verification';

                const verifySection = needsVerify ? `
        <div style="background:#f3f0ff;border:1px solid #c4a8f5;border-radius:10px;padding:16px;margin-bottom:16px;">
            <p style="margin:0 0 10px;font-weight:700;color:#6f42c1;"><i class="fas fa-clock"></i> This order is awaiting payment verification</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button onclick="OrdersModule.verifyPayment(${o.id}, 'paid')"
                    style="background:#28a745;color:#fff;border:none;padding:9px 20px;border-radius:7px;cursor:pointer;font-weight:600;font-size:14px;">
                    <i class="fas fa-check"></i> Approve — Mark as Paid
                </button>
                <button onclick="OrdersModule.verifyPayment(${o.id}, 'failed')"
                    style="background:#dc3545;color:#fff;border:none;padding:9px 20px;border-radius:7px;cursor:pointer;font-weight:600;font-size:14px;">
                    <i class="fas fa-times"></i> Cancel Order!
                </button>
            </div>
        </div>` : '';

                const receiptSection = hasReceipt ? `
        <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:10px;padding:16px;">
            <p style="margin:0 0 10px;font-weight:700;color:#2e7d32;font-size:13px;">
                <i class="fas fa-receipt"></i> Payment Receipt Uploaded
            </p>
            <div style="text-align:center;">
                <img src="${this.esc(o.receipt_url)}" alt="Receipt"
                    style="max-width:100%;max-height:220px;border-radius:8px;cursor:pointer;box-shadow:0 2px 10px rgba(0,0,0,.15);border:1px solid #c8e6c9;"
                    onclick="viewReceipt('${this.esc(o.receipt_url)}','${this.esc(o.order_reference)}')"
                    onerror="this.style.display='none';document.getElementById('receiptFb_${o.id}').style.display='block'">
                <div id="receiptFb_${o.id}" style="display:none;">
                    <a href="${this.esc(o.receipt_url)}" target="_blank"
                       style="display:inline-block;padding:8px 18px;background:#28a745;color:#fff;border-radius:6px;text-decoration:none;font-size:13px;">
                        <i class="fas fa-file-pdf"></i> Open PDF Receipt
                    </a>
                </div>
                <br>
                <button onclick="viewReceipt('${this.esc(o.receipt_url)}','${this.esc(o.order_reference)}')"
                    style="margin-top:10px;background:#28a745;color:#fff;border:none;padding:8px 20px;border-radius:7px;cursor:pointer;font-size:13px;">
                    <i class="fas fa-expand-alt"></i> View Full Receipt
                </button>
            </div>
        </div>` : `
        <div style="background:#fff3e0;border:1px solid #ffcc80;border-radius:10px;padding:14px;text-align:center;color:#e65100;font-size:13px;">
            <i class="fas fa-exclamation-triangle"></i> No payment receipt uploaded for this order.
        </div>`;

                body.innerHTML = `
        ${verifySection}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div>
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Order Reference</div>
                <div style="font-weight:700;margin-top:3px;">${this.esc(o.order_reference || '—')}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Date Placed</div>
                <div style="margin-top:3px;font-size:13px;">${date}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Customer Name</div>
                <div style="font-weight:600;margin-top:3px;">${this.esc(o.customer_name || '—')}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Email</div>
                <div style="margin-top:3px;font-size:13px;">${this.esc(o.customer_email || '—')}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Phone</div>
                <div style="margin-top:3px;font-size:13px;">${this.esc(o.customer_phone || '—')}</div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Total Amount</div>
                <div style="font-weight:800;font-size:20px;color:#28a745;margin-top:3px;">${amount}</div>
            </div>
            <div style="grid-column:1/-1;">
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;letter-spacing:.5px;">Delivery Address</div>
                <div style="margin-top:3px;font-size:13px;">${this.esc(o.customer_address || '—')}</div>
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
            <div style="flex:1;background:#f8f9fa;border-radius:8px;padding:12px;min-width:120px;">
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;">Payment Method</div>
                <div style="margin-top:6px;">${this.formatMethod(o.payment_method || '—')}</div>
            </div>
            <div style="flex:1;background:#f8f9fa;border-radius:8px;padding:12px;min-width:120px;">
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;">Payment Status</div>
                <div style="margin-top:6px;">${this.payBadge(o.payment_status)}</div>
            </div>
            <div style="flex:1;background:#f8f9fa;border-radius:8px;padding:12px;min-width:120px;">
                <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;">Order Status</div>
                <div style="margin-top:6px;">${this.orderBadge(o.order_status)}</div>
            </div>
        </div>
        ${o.staff_notes ? `
        <div style="background:#fffde7;border:1px solid #ffe082;border-radius:8px;padding:12px;margin-bottom:16px;">
            <div style="font-size:10px;font-weight:800;color:#999;text-transform:uppercase;margin-bottom:6px;"><i class="fas fa-sticky-note"></i> Staff Notes / Payment Info</div>
            <div style="font-size:13px;">${this.esc(o.staff_notes)}</div>
        </div>` : ''}
        ${receiptSection}
        `;

                modal.style.display = 'flex';
            },

            // ── Verify Payment ────────────────────────────────────────────────────
            async verifyPayment(orderId, newStatus) {
                const label = newStatus === 'paid' ? 'approve' : 'Cancel';
                if (!confirm(`Are you sure you want to ${label} this payment?`)) return;

                try {
                    const res = await fetch('dashboard.php?ajax=1&action=verify_payment', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ order_id: orderId, payment_status: newStatus })
                    });
                    const json = await res.json();

                    if (json.success) {
                        closeOrderDetailModal();
                        this.showToast(newStatus === 'paid' ? 'Payment approved — order is now Processing.' : 'Payment rejected.', newStatus === 'paid' ? 'success' : 'warning');
                        await this.loadOrders();
                    } else {
                        this.showToast('Error: ' + (json.message || 'Unknown error'), 'error');
                    }
                } catch (err) {
                    this.showToast('Connection error: ' + err.message, 'error');
                }
            },

            // ── Filter by status (called from stat card click) ───────────────────
            filterByStatus(status) {
                const sel = document.getElementById('orderStatusFilter');
                if (sel) { sel.value = status; sel.dispatchEvent(new Event('change')); }
                // Navigate to orders page if not already there
                if (typeof showPage === 'function') showPage('orders', 'Orders');
            },

            // ── Bind Filters ──────────────────────────────────────────────────────
            bindFilters() {
                const search = document.getElementById('orderSearch');
                const status = document.getElementById('orderStatusFilter');
                const payment = document.getElementById('paymentFilter');
                const receipt = document.getElementById('receiptFilter');

                const apply = () => {
                    const term = (search?.value || '').toLowerCase();
                    const stat = (status?.value || '').toLowerCase();
                    const pay = (payment?.value || '').toLowerCase();
                    const rec = (receipt?.value || '');

                    this.filtered = this.orders.filter(o => {
                        const matchTerm = !term ||
                            (o.order_reference || '').toLowerCase().includes(term) ||
                            (o.customer_name || '').toLowerCase().includes(term) ||
                            (o.customer_email || '').toLowerCase().includes(term);

                        const matchStat = !stat || (o.payment_status || '').toLowerCase() === stat;
                        const matchPay = !pay || (o.payment_method || '').toLowerCase().includes(pay);
                        const matchRec = !rec ||
                            (rec === 'has_receipt' && !!o.receipt_url) ||
                            (rec === 'no_receipt' && !o.receipt_url);

                        return matchTerm && matchStat && matchPay && matchRec;
                    });
                    this.renderOrders();
                };

                // Remove old listeners by replacing elements (simplest approach)
                [search, status, payment, receipt].forEach(el => {
                    if (!el) return;
                    const clone = el.cloneNode(true);
                    el.parentNode.replaceChild(clone, el);
                    clone.addEventListener(clone.tagName === 'INPUT' ? 'input' : 'change', apply);
                });
            },

            // ── Helpers ───────────────────────────────────────────────────────────
            formatMethod(method) {
                const m = (method || '').toLowerCase();
                if (m.includes('instapay')) return '<i class="fas fa-university" style="color:#6f42c1;"></i> InstaPay';
                if (m.includes('gcash')) return '<i class="fas fa-mobile-alt" style="color:#007bff;"></i> GCash';
                if (m.includes('maya')) return '<i class="fas fa-credit-card" style="color:#fd7e14;"></i> Maya';
                if (m.includes('cash')) return '<i class="fas fa-money-bill-wave" style="color:#28a745;"></i> Cash';
                return method || '—';
            },

            payBadge(status) {
                const map = {
                    paid: { bg: '#d4edda', c: '#155724', t: 'Paid' },
                    pending: { bg: '#fff3cd', c: '#856404', t: 'Pending' },
                    pending_verification: { bg: '#e8d5ff', c: '#4a235a', t: 'Verifying' },
                    failed: { bg: '#f8d7da', c: '#721c24', t: 'Failed' },
                    cancelled: { bg: '#e2e3e5', c: '#383d41', t: 'Cancelled' },
                };
                const k = (status || 'pending').toLowerCase();
                const cfg = map[k] || { bg: '#eee', c: '#555', t: status || '—' };
                return `<span style="background:${cfg.bg};color:${cfg.c};padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;">${cfg.t}</span>`;
            },

            orderBadge(status) {
                const map = {
                    pending: { bg: '#fff3cd', c: '#856404', t: 'Pending' },
                    processing: { bg: '#cce5ff', c: '#004085', t: 'Processing' },
                    confirmed: { bg: '#d1ecf1', c: '#0c5460', t: 'Confirmed' },
                    shipped: { bg: '#d4edda', c: '#155724', t: 'Shipped' },
                    delivered: { bg: '#28a745', c: '#fff', t: 'Delivered' },
                    cancelled: { bg: '#f8d7da', c: '#721c24', t: 'Cancelled' },
                    archived: { bg: '#e2e3e5', c: '#383d41', t: 'Archived' },
                };
                const k = (status || 'pending').toLowerCase();
                const cfg = map[k] || { bg: '#eee', c: '#555', t: status || '—' };
                return `<span style="background:${cfg.bg};color:${cfg.c};padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;">${cfg.t}</span>`;
            },

            esc(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            },

            showToast(msg, type = 'success') {
                // Use existing toast system if available, else fallback alert
                if (typeof showToast === 'function') {
                    showToast(msg, type);
                } else if (typeof Toast !== 'undefined') {
                    Toast.show(msg, type);
                } else {
                    alert(msg);
                }
            }
        };

        // ── Receipt Modal ──────────────────────────────────────────────────────────
        function viewReceipt(url, orderRef) {
            const modal = document.getElementById('receiptModal');
            const body = document.getElementById('receiptModalBody');
            const refEl = document.getElementById('receiptOrderRef');
            const dlBtn = document.getElementById('receiptDownloadBtn');

            if (!modal) return;
            if (refEl) refEl.textContent = 'Order: ' + (orderRef || '');
            if (dlBtn) { dlBtn.href = url; dlBtn.download = 'receipt_' + (orderRef || '') + '.jpg'; }

            const isPdf = (url || '').toLowerCase().endsWith('.pdf');
            body.innerHTML = isPdf
                ? `<iframe src="${url}" style="width:100%;height:520px;border:none;border-radius:6px;"></iframe>`
                : `<img src="${url}" alt="Payment Receipt"
               style="max-width:100%;max-height:72vh;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.25);"
               onerror="this.outerHTML='<p style=color:#c0392b>Could not load receipt. <a href=\\'${url}\\' target=\\'_blank\\'>Open directly</a></p>'">`;

            modal.style.display = 'flex';
        }

        function closeReceiptModal() {
            const modal = document.getElementById('receiptModal');
            const body = document.getElementById('receiptModalBody');
            if (modal) modal.style.display = 'none';
            if (body) body.innerHTML = ''; // free memory / stop video
        }

        function closeOrderDetailModal() {
            const modal = document.getElementById('orderDetailModal');
            if (modal) modal.style.display = 'none';
        }

        // Close on backdrop click
        document.addEventListener('click', function (e) {
            if (e.target === document.getElementById('receiptModal')) closeReceiptModal();
            if (e.target === document.getElementById('orderDetailModal')) closeOrderDetailModal();
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeReceiptModal(); closeOrderDetailModal(); }
        });

        // Auto-load orders when navigating to the orders page
        const _origShowPage = window.showPage;
        window.showPage = function (pageId, pageTitle) {
            if (typeof _origShowPage === 'function') _origShowPage(pageId, pageTitle);
            else if (typeof PageNavigation !== 'undefined') PageNavigation.showPage(pageId, pageTitle);
            if (pageId === 'orders') setTimeout(() => OrdersModule.loadOrders(), 80);
            if (pageId === 'tracking') setTimeout(() => TrackingModule.init(), 100);
        };



        // Initialize tracking when page is shown
        const originalShowPageFunc = window.showPage;
        window.showPage = function (pageId, pageTitle) {
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
            const lgAvatar = document.querySelector('.profile-avatar-large .staff-avatar-initials');
            if (lgAvatar) {
                lgAvatar.textContent = `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
            }

            // Here you would send to your backend
            console.log('Updating info:', { firstName, lastName, email, contact });

            alert('Information updated successfully!');
            closeEditInfoModal();
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
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
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const productCards = document.querySelectorAll('.product-card');
            const productCountElement = document.getElementById('displayedProductCount');
            const searchInput = document.getElementById('productSearchInput');

            let currentCategory = 'all';
            let currentSearchTerm = '';

            // Make product cards clickable to edit
            productCards.forEach(card => {
                card.addEventListener('click', function (e) {
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
            loadProductCardImages();

            // Filter button click handler
            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
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
                searchInput.addEventListener('input', function () {
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
                filterIcon.addEventListener('click', function () {
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
                    // Show/hide MOQ wrapper based on loaded category
                    const _editMoqWrapper = document.getElementById('editMoqWrapper');
                    const _editMoqInput = document.getElementById('editMoq');
                    const _editMoqHint = document.getElementById('editMoqHint');
                    const _editMoqCats = ['Panel', 'Mounting & Accessories'];

                    function _applyEditMoqVisibility(cat) {
                        if (_editMoqCats.includes(cat)) {
                            _editMoqWrapper.style.display = 'block';
                            if (cat === 'Panel') {
                                if (_editMoqHint) _editMoqHint.textContent = 'Solar Panels: bulk tiers 5 / 10 / 15 / 20 pcs';
                            } else {
                                if (_editMoqHint) _editMoqHint.textContent = 'Mounting & Accessories: set minimum order quantity';
                            }
                        } else {
                            _editMoqWrapper.style.display = 'none';
                        }
                    }

                    _editMoqInput.value = product.moq || 1;
                    _applyEditMoqVisibility(product.category);

                    document.getElementById('editDescription').value = product.description || '';

                    // Auto-update MOQ when category changes in edit form
                    const editCatEl = document.getElementById('editCategory');
                    editCatEl.onchange = function () {
                        _applyEditMoqVisibility(this.value);
                        if (this.value === 'Panel' && parseInt(_editMoqInput.value) < 2) {
                            _editMoqInput.value = 2;
                        } else if (!_editMoqCats.includes(this.value)) {
                            _editMoqInput.value = 1;
                        }
                    };

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

        // Carousel state
        let carouselImages = [];
        let currentCarouselIndex = 0;

        // Load product images into the carousel
        function loadProductImages(images) {
            const mainContainer = document.getElementById('carouselImageContainer');
            const thumbnailsContainer = document.getElementById('carouselThumbnails');
            const counterContainer = document.getElementById('carouselCounter');
            const prevBtn = document.getElementById('carouselPrevBtn');
            const nextBtn = document.getElementById('carouselNextBtn');

            carouselImages = images || [];
            currentCarouselIndex = 0;

            // Clear containers
            mainContainer.innerHTML = '';
            thumbnailsContainer.innerHTML = '';
            counterContainer.innerHTML = '';

            if (!images || images.length === 0) {
                // Show no images placeholder
                mainContainer.innerHTML = `
            <div class="no-images-placeholder">
                <i class="fas fa-image"></i>
                <p>No images uploaded yet</p>
            </div>
        `;
                // Hide navigation buttons
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                return;
            }

            // Show navigation buttons if more than 1 image
            if (prevBtn) prevBtn.style.display = images.length > 1 ? 'flex' : 'none';
            if (nextBtn) nextBtn.style.display = images.length > 1 ? 'flex' : 'none';

            // Create main carousel image display
            const mainImageWrapper = document.createElement('div');
            mainImageWrapper.className = 'carousel-slide-wrapper';
            mainImageWrapper.innerHTML = `
        <img id="carouselMainImage" src="../../${images[0].image_path}" alt="Product image" onerror="this.src='../../assets/img/product-placeholder.png'">
        <button type="button" class="carousel-delete-btn" id="carouselDeleteBtn" onclick="markCarouselImageForDeletion()">
            <i class="fas fa-trash"></i> Delete This Image
        </button>
    `;
            mainContainer.appendChild(mainImageWrapper);

            // Create thumbnails
            images.forEach((image, index) => {
                const thumb = document.createElement('div');
                thumb.className = 'carousel-thumbnail' + (index === 0 ? ' active' : '');
                thumb.dataset.imageId = image.id;
                thumb.dataset.index = index;
                thumb.innerHTML = `
            <img src="../../${image.image_path}" alt="Thumbnail ${index + 1}" onerror="this.src='../../assets/img/product-placeholder.png'">
            ${imagesToDelete.includes(image.id) ? '<span class="thumb-deleted-badge"><i class="fas fa-trash"></i></span>' : ''}
        `;
                thumb.onclick = () => goToCarouselSlide(index);
                thumbnailsContainer.appendChild(thumb);
            });

            // Update counter
            updateCarouselCounter();
        }

        // Go to specific carousel slide
        function goToCarouselSlide(index) {
            if (carouselImages.length === 0) return;

            currentCarouselIndex = index;
            if (currentCarouselIndex < 0) currentCarouselIndex = carouselImages.length - 1;
            if (currentCarouselIndex >= carouselImages.length) currentCarouselIndex = 0;

            const mainImage = document.getElementById('carouselMainImage');
            const deleteBtn = document.getElementById('carouselDeleteBtn');

            if (mainImage) {
                mainImage.src = '../../' + carouselImages[currentCarouselIndex].image_path;
            }

            // Update thumbnail active state
            document.querySelectorAll('.carousel-thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === currentCarouselIndex);
            });

            // Update delete button state
            const currentImageId = carouselImages[currentCarouselIndex].id;
            if (deleteBtn) {
                if (imagesToDelete.includes(currentImageId)) {
                    deleteBtn.innerHTML = '<i class="fas fa-undo"></i> Undo Delete';
                    deleteBtn.classList.add('marked-delete');
                } else {
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete This Image';
                    deleteBtn.classList.remove('marked-delete');
                }
            }

            updateCarouselCounter();
        }

        // Carousel navigation
        function carouselNext() {
            goToCarouselSlide(currentCarouselIndex + 1);
        }

        function carouselPrev() {
            goToCarouselSlide(currentCarouselIndex - 1);
        }

        // Update carousel counter
        function updateCarouselCounter() {
            const counter = document.getElementById('carouselCounter');
            if (counter && carouselImages.length > 0) {
                counter.innerHTML = `<span>${currentCarouselIndex + 1}</span> / <span>${carouselImages.length}</span>`;
            }
        }

        // Mark current carousel image for deletion
        function markCarouselImageForDeletion() {
            if (carouselImages.length === 0) return;

            const currentImageId = carouselImages[currentCarouselIndex].id;
            const deleteBtn = document.getElementById('carouselDeleteBtn');
            const thumbnail = document.querySelector(`.carousel-thumbnail[data-index="${currentCarouselIndex}"]`);

            if (imagesToDelete.includes(currentImageId)) {
                // Unmark for deletion
                imagesToDelete = imagesToDelete.filter(id => id !== currentImageId);
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete This Image';
                    deleteBtn.classList.remove('marked-delete');
                }
                if (thumbnail) {
                    const badge = thumbnail.querySelector('.thumb-deleted-badge');
                    if (badge) badge.remove();
                }
            } else {
                // Mark for deletion
                imagesToDelete.push(currentImageId);
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="fas fa-undo"></i> Undo Delete';
                    deleteBtn.classList.add('marked-delete');
                }
                if (thumbnail) {
                    const badge = document.createElement('span');
                    badge.className = 'thumb-deleted-badge';
                    badge.innerHTML = '<i class="fas fa-trash"></i>';
                    thumbnail.appendChild(badge);
                }
            }

            // Update hidden input
            document.getElementById('deleteImagesInput').value = imagesToDelete.join(',');
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

            // Clear new images input and preview
            const newImagesInput = document.getElementById('newImagesInput');
            const newImagesPreview = document.getElementById('newImagesPreview');

            if (newImagesInput) newImagesInput.value = '';
            if (newImagesPreview) newImagesPreview.innerHTML = '';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            const editModal = document.getElementById('editProductModal');
            if (event.target === editModal) {
                closeEditModal();
            }
        });

        // Handle edit form submission
        const editForm = document.getElementById('editProductForm');
        if (editForm) {
            editForm.addEventListener('submit', async function (e) {
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
        async function loadProductCardImages() {
            document.querySelectorAll('.product-card').forEach(async (card) => {
                const productId = card.getAttribute('data-product-id');
                const imageElement = card.querySelector('.product-image img');

                if (productId && imageElement) {
                    try {
                        // Fetch the first image for this product
                        const response = await fetch(`get_product_image.php?product_id=${productId}`);
                        const data = await response.json();

                        if (data.success && data.image_path) {
                            imageElement.src = '../../' + data.image_path;
                        } else {
                            // Keep the placeholder if no image found
                            imageElement.src = '../../assets/img/product-placeholder.png';
                        }
                    } catch (error) {
                        console.error('Error loading image for product ' + productId, error);
                        imageElement.src = '../../assets/img/product-placeholder.png';
                    }
                }
            });
        }

        function closeEditModal() {
            document.getElementById('editProductModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
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

        // Handle new images - upload immediately via AJAX and refresh carousel
        document.addEventListener('DOMContentLoaded', function () {
            const newImagesInput = document.getElementById('newImagesInput');
            if (newImagesInput) {
                newImagesInput.addEventListener('change', async function (e) {
                    const previewDiv = document.getElementById('newImagesPreview');
                    if (!previewDiv) return;

                    previewDiv.innerHTML = '';

                    const files = e.target.files;
                    if (files.length === 0) return;

                    const productId = document.getElementById('editProductId').value;
                    if (!productId) {
                        alert('Product ID is missing. Please try again.');
                        return;
                    }

                    // Show uploading indicator
                    const statusMsg = document.createElement('div');
                    statusMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 10px; background: #fff3cd; color: #856404; border-radius: 6px; font-weight: 600; margin-bottom: 8px;';
                    statusMsg.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading ' + files.length + ' image(s)...';
                    previewDiv.appendChild(statusMsg);

                    // Build FormData and upload via AJAX
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].type.startsWith('image/')) {
                            formData.append('new_images[]', files[i]);
                        }
                    }

                    try {
                        const response = await fetch('ajax_upload_images.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        previewDiv.innerHTML = '';

                        if (data.success && data.images && data.images.length > 0) {
                            // Show success message
                            const successMsg = document.createElement('div');
                            successMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 10px; background: #e6f7f1; color: #217346; border-radius: 6px; font-weight: 600; margin-bottom: 8px;';
                            successMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                            previewDiv.appendChild(successMsg);

                            // Show preview thumbnails of newly uploaded images
                            data.images.forEach((img, i) => {
                                const preview = document.createElement('div');
                                preview.className = 'new-image-preview-item';
                                preview.innerHTML =
                                    '<img src="../../' + img.image_path + '" alt="New image ' + (i + 1) + '">' +
                                    '<div class="new-image-badge"><i class="fas fa-plus-circle"></i> New</div>';
                                previewDiv.appendChild(preview);
                            });

                            // Append new images to the carousel array
                            data.images.forEach(img => {
                                carouselImages.push({ id: img.id, image_path: img.image_path });
                            });

                            // Refresh the carousel to show all images including new ones
                            loadProductImages(carouselImages);

                            // Navigate to the first newly added image
                            const firstNewIndex = carouselImages.length - data.images.length;
                            goToCarouselSlide(firstNewIndex);
                        } else {
                            const errorMsg = document.createElement('div');
                            errorMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 6px; font-weight: 600; margin-bottom: 8px;';
                            errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Upload failed');
                            previewDiv.appendChild(errorMsg);
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        previewDiv.innerHTML = '';
                        const errorMsg = document.createElement('div');
                        errorMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 6px; font-weight: 600; margin-bottom: 8px;';
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Failed to upload images. Please try again.';
                        previewDiv.appendChild(errorMsg);
                    }

                    // Reset the file input so the same files can be re-selected if needed
                    newImagesInput.value = '';
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
        window.onclick = function (event) {
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
                <td><span class="quotation-badge badge-${q.system_type.toLowerCase().replace(/ /g, '-')}">${q.system_type}</span></td>
                <td>${q.kw || '-'}</td>
                <td><span class="quotation-badge badge-${q.officer.toLowerCase()}">${this.escapeHtml(officerName)}  </span></td>
                <td><span class="quotation-badge badge-${q.status.toLowerCase()}">${q.status}</span></td>
                <td style="max-width: 200px; font-size: 11px;">${this.escapeHtml(q.remarks || '')}</td>
                <td class="quotation-actions">
                    <button class="btn-small-action btn-edit-quotation" onclick="QuotationModule.editQuotation(${q.id})">
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
        document.addEventListener('DOMContentLoaded', function () {
            const quotationMenuItem = document.querySelector('.menu-item[onclick*="quotation"]');
            if (quotationMenuItem) {
                quotationMenuItem.addEventListener('click', function () {
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
        document.addEventListener('DOMContentLoaded', function () {
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

                // Show/hide and auto-set MOQ for Solar Panel & Mounting & Accessories only
                const moqWrapper = document.getElementById('moq-field-wrapper');
                const moqInput = document.getElementById('moq-input');
                const moqHint = document.getElementById('moq-hint-text');
                const moqCategories = ['Panel', 'Mounting & Accessories'];

                if (moqWrapper && moqInput) {
                    if (moqCategories.includes(category)) {
                        moqWrapper.style.display = 'block';
                        if (category === 'Panel') {
                            moqInput.value = 2;
                            if (moqHint) moqHint.textContent = 'Solar Panels: bulk tiers 5 / 10 / 15 / 20 pcs';
                        } else {
                            moqInput.value = 1;
                            if (moqHint) moqHint.textContent = 'Mounting & Accessories: set minimum order quantity';
                        }
                    } else {
                        moqWrapper.style.display = 'none';
                        moqInput.value = 1; // reset to default when hidden
                    }
                }

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
        document.addEventListener('DOMContentLoaded', function () {
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
        document.addEventListener('DOMContentLoaded', function () {
            // Note: ProductFormHandler already handles form submission via AJAX
            // No need to start UploadAnimation here as it conflicts with the AJAX handler

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



        // ══════════════════════════════════════════════════════════════════════════════
        // BRANDS MODULE
        // ══════════════════════════════════════════════════════════════════════════════
        const BrandsModule = {
            brands: [],
            categories: [],

            init() {
                this.loadCategories().then(() => this.loadBrands());
            },

            ajaxUrl: 'dashboard.php?ajax=1',

            async post(formData) {
                formData.append('ajax', '1');
                const res = await fetch('dashboard.php', { method: 'POST', body: formData });
                return res.json();
            },

            // ── Load categories ──────────────────────────────────────────────────────
            async loadCategories() {
                try {
                    const res = await fetch(this.ajaxUrl + '&action=fetch_categories');
                    const data = await res.json();
                    if (data.success) {
                        this.categories = data.data;
                        this.populateCategoryDropdowns();
                    }
                } catch (e) { console.error('loadCategories error', e); }
            },

            populateCategoryDropdowns() {
                const selectors = ['#bNewCategory', '#bEditCategory', '#bCatFilter'];
                selectors.forEach(sel => {
                    const el = document.querySelector(sel);
                    if (!el) return;
                    // keep first option, remove rest
                    while (el.options.length > 1) el.remove(1);
                    this.categories.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.category_id;
                        opt.textContent = c.category_name;
                        el.appendChild(opt);
                    });
                });
                // Update stat
                const statEl = document.getElementById('brandStatCats');
                if (statEl) statEl.textContent = this.categories.length;
            },

            // ── Load brands ──────────────────────────────────────────────────────────
            async loadBrands() {
                try {
                    const res = await fetch(this.ajaxUrl + '&action=fetch_brands');
                    const data = await res.json();
                    if (data.success) {
                        this.brands = data.data;
                        this.renderTable(this.brands);
                        const statEl = document.getElementById('brandStatTotal');
                        if (statEl) statEl.textContent = this.brands.length;
                    }
                } catch (e) { console.error('loadBrands error', e); }
            },

            // ── Render table ─────────────────────────────────────────────────────────
            renderTable(list) {
                const tbody = document.getElementById('brandsTableBody');
                if (!tbody) return;

                if (!list || list.length === 0) {
                    tbody.innerHTML = '<tr class="brands-empty"><td colspan="4"><i class="fas fa-box-open"></i> No brands yet. Add one!</td></tr>';
                    return;
                }

                tbody.innerHTML = list.map((b, i) => `
            <tr data-brand-id="${b.brand_id}" data-brand-name="${this.esc(b.brand_name)}"
                data-category-id="${b.category_id}" data-category-name="${this.esc(b.category_name)}">
                <td style="color:#999;font-size:12px;">${i + 1}</td>
                <td style="font-weight:600;">${this.esc(b.brand_name)}</td>
                <td><span class="cat-badge ${this.badgeClass(b.category_name)}">${this.esc(b.category_name)}</span></td>
                <td>
                    <div class="brand-action-btns">
                        <button class="btn-brand-edit" onclick="BrandsModule.openEditModal(this)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-brand-del" onclick="BrandsModule.openDeleteConfirm(this)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>`).join('');
            },

            // ── Filters ──────────────────────────────────────────────────────────────
            applyFilters() {
                const search = (document.getElementById('bSearchInput')?.value || '').toLowerCase();
                const catId = document.getElementById('bCatFilter')?.value || '';

                const filtered = this.brands.filter(b => {
                    const nameMatch = b.brand_name.toLowerCase().includes(search);
                    const catMatch = !catId || String(b.category_id) === catId;
                    return nameMatch && catMatch;
                });
                this.renderTable(filtered);
            },

            // ── Add brand ────────────────────────────────────────────────────────────
            async addBrand() {
                const brand_name = document.getElementById('bNewName')?.value.trim();
                const category_id = document.getElementById('bNewCategory')?.value;

                if (!brand_name || !category_id) {
                    return this.toast('Brand name and category are required.', 'error');
                }

                const fd = new FormData();
                fd.append('action', 'add_brand');
                fd.append('brand_name', brand_name);
                fd.append('category_id', category_id);

                const data = await this.post(fd);
                if (data.success) {
                    this.toast(data.message);
                    document.getElementById('bNewName').value = '';
                    document.getElementById('bNewCategory').value = '';
                    await this.loadBrands();
                } else {
                    this.toast(data.message, 'error');
                }
            },

            // ── Edit modal ───────────────────────────────────────────────────────────
            openEditModal(btn) {
                const tr = btn.closest('tr');
                document.getElementById('bEditId').value = tr.dataset.brandId;
                document.getElementById('bEditName').value = tr.dataset.brandName;
                document.getElementById('bEditCategory').value = tr.dataset.categoryId;
                document.getElementById('bEditOverlay').classList.add('open');
            },

            closeEditModal() {
                document.getElementById('bEditOverlay').classList.remove('open');
            },

            async saveEdit() {
                const brand_id = document.getElementById('bEditId').value;
                const brand_name = document.getElementById('bEditName').value.trim();
                const category_id = document.getElementById('bEditCategory').value;

                if (!brand_name || !category_id) {
                    return this.toast('All fields are required.', 'error');
                }

                const fd = new FormData();
                fd.append('action', 'edit_brand');
                fd.append('brand_id', brand_id);
                fd.append('brand_name', brand_name);
                fd.append('category_id', category_id);

                const data = await this.post(fd);
                if (data.success) {
                    this.toast(data.message);
                    this.closeEditModal();
                    await this.loadBrands();
                } else {
                    this.toast(data.message, 'error');
                }
            },

            // ── Delete modal ─────────────────────────────────────────────────────────
            openDeleteConfirm(btn) {
                const tr = btn.closest('tr');
                document.getElementById('bDeleteId').value = tr.dataset.brandId;
                document.getElementById('bDeleteMsg').textContent =
                    `Are you sure you want to delete "${tr.dataset.brandName}"?`;
                document.getElementById('bDeleteOverlay').classList.add('open');
            },

            closeDeleteModal() {
                document.getElementById('bDeleteOverlay').classList.remove('open');
            },

            async confirmDelete() {
                const brand_id = document.getElementById('bDeleteId').value;
                const fd = new FormData();
                fd.append('action', 'delete_brand');
                fd.append('brand_id', brand_id);

                const data = await this.post(fd);
                if (data.success) {
                    this.toast(data.message);
                    this.closeDeleteModal();
                    await this.loadBrands();
                } else {
                    this.toast(data.message, 'error');
                }
            },

            // ── Helpers ──────────────────────────────────────────────────────────────
            toast(msg, type = 'success') {
                const t = document.getElementById('brandToast');
                if (!t) return;
                t.textContent = msg;
                t.className = `brand-toast ${type} show`;
                setTimeout(() => t.classList.remove('show'), 3200);
            },

            badgeClass(name) {
                const n = (name || '').toLowerCase();
                if (n.includes('panel')) return 'panel';
                if (n.includes('inverter')) return 'inverter';
                if (n.includes('battery')) return 'battery';
                if (n.includes('mount')) return 'mount';
                if (n.includes('package')) return 'package';
                if (n.includes('protect')) return 'protect';
                return '';
            },

            esc(str) {
                return String(str)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
        };

        // Close brand overlays on backdrop click
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.brand-overlay').forEach(ov => {
                ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); });
            });
        });

        // Initialize BrandsModule when the brands sidebar item is clicked
        document.addEventListener('DOMContentLoaded', function () {
            const brandsMenuItem = Array.from(document.querySelectorAll('.menu-item')).find(el =>
                el.getAttribute('onclick')?.includes("showPage('brands'")
            );
            if (brandsMenuItem) {
                brandsMenuItem.addEventListener('click', function () {
                    setTimeout(() => BrandsModule.init(), 100);
                });
            }
        });
        // ══════════════════════════════════════════════════════════════════════════════
        // END BRANDS MODULE
        // ══════════════════════════════════════════════════════════════════════════════


    </script>

</body>

</html>