<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Database connection (use the same credentials as dashboard.php)
include "../../config/dbconn.php";

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'fetch':
        // Join quotations with staff table to get officer names
        $query = "SELECT 
                    q.*,
                    s.firstName as officer_firstName,
                    s.lastName as officer_lastName
                  FROM quotations q
                  LEFT JOIN staff s ON q.officer = UPPER(s.firstName)
                  ORDER BY q.id DESC";
        
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $quotations = [];
            while ($row = mysqli_fetch_assoc($result)) {
                // Combine officer name for display
                if ($row['officer_firstName'] && $row['officer_lastName']) {
                    $row['officer_display_name'] = $row['officer_firstName'] . ' ' . $row['officer_lastName'];
                } else {
                    $row['officer_display_name'] = $row['officer']; // Fallback to stored value
                }
                $quotations[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $quotations]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Query failed: ' . mysqli_error($conn)]);
        }
        break;

    case 'fetch_officers':
        // Fetch all staff members to populate the officer dropdown
        $query = "SELECT id, firstName, lastName, UPPER(firstName) as officer_code 
                  FROM staff 
                  ORDER BY firstName";
        
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $officers = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $officers[] = [
                    'code' => $row['officer_code'],
                    'name' => $row['firstName'] . ' ' . $row['lastName']
                ];
            }
            echo json_encode(['success' => true, 'data' => $officers]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Query failed: ' . mysqli_error($conn)]);
        }
        break;

    case 'create':
        // Generate quotation number
        $quotation_number = 'Q' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $client_name = mysqli_real_escape_string($conn, $_POST['clientName']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $system_type = mysqli_real_escape_string($conn, $_POST['systemType']);
        $kw = !empty($_POST['kw']) ? floatval($_POST['kw']) : NULL;
        $officer = mysqli_real_escape_string($conn, $_POST['officer']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
        $created_by = $_SESSION['user_id'];
        
        if ($kw !== NULL) {
            $query = "INSERT INTO quotations (quotation_number, client_name, email, contact, location, system_type, kw, officer, status, remarks, created_by) 
                      VALUES ('$quotation_number', '$client_name', '$email', '$contact', '$location', '$system_type', $kw, '$officer', '$status', '$remarks', $created_by)";
        } else {
            $query = "INSERT INTO quotations (quotation_number, client_name, email, contact, location, system_type, kw, officer, status, remarks, created_by) 
                      VALUES ('$quotation_number', '$client_name', '$email', '$contact', '$location', '$system_type', NULL, '$officer', '$status', '$remarks', $created_by)";
        }
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Insert failed: ' . mysqli_error($conn)]);
        }
        break;

    case 'update':
        $id = intval($_POST['id']);
        $client_name = mysqli_real_escape_string($conn, $_POST['clientName']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $system_type = mysqli_real_escape_string($conn, $_POST['systemType']);
        $kw = !empty($_POST['kw']) ? floatval($_POST['kw']) : NULL;
        $officer = mysqli_real_escape_string($conn, $_POST['officer']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
        
        if ($kw !== NULL) {
            $query = "UPDATE quotations SET 
                      client_name='$client_name', 
                      email='$email',
                      contact='$contact',
                      location='$location', 
                      system_type='$system_type', 
                      kw=$kw, 
                      officer='$officer', 
                      status='$status', 
                      remarks='$remarks' 
                      WHERE id=$id";
        } else {
            $query = "UPDATE quotations SET 
                      client_name='$client_name',
                      email='$email',
                      contact='$contact', 
                      location='$location', 
                      system_type='$system_type', 
                      kw=NULL, 
                      officer='$officer', 
                      status='$status', 
                      remarks='$remarks' 
                      WHERE id=$id";
        }
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Update failed: ' . mysqli_error($conn)]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id']);
        $query = "DELETE FROM quotations WHERE id=$id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Delete failed: ' . mysqli_error($conn)]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

mysqli_close($conn);
?>