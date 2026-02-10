<?php
session_start();
include "../../config/dbconn.php";

header('Content-Type: application/json');

$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'create':
        $firstName = $conn->real_escape_string($input['firstName']);
        $lastName = $conn->real_escape_string($input['lastName']);
        $email = $conn->real_escape_string($input['email']);
        $contact = $conn->real_escape_string($input['contact_number'] ?? '');
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        
        // Check if email exists
        $check = $conn->query("SELECT id FROM staff WHERE email='$email'");
        if ($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            break;
        }
        
        $sql = "INSERT INTO staff (firstName, lastName, email, contact_number, password) 
                VALUES ('$firstName', '$lastName', '$email', '$contact', '$password')";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Staff member added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
        }
        break;
        
    case 'update':
        $id = (int)$input['id'];
        $firstName = $conn->real_escape_string($input['firstName']);
        $lastName = $conn->real_escape_string($input['lastName']);
        $email = $conn->real_escape_string($input['email']);
        $contact = $conn->real_escape_string($input['contact_number'] ?? '');
        
        if (!empty($input['password'])) {
            $password = password_hash($input['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE staff SET firstName='$firstName', lastName='$lastName', 
                    email='$email', contact_number='$contact', password='$password' WHERE id=$id";
        } else {
            $sql = "UPDATE staff SET firstName='$firstName', lastName='$lastName', 
                    email='$email', contact_number='$contact' WHERE id=$id";
        }
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Staff member updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
        }
        break;
        
    case 'delete':
        $id = (int)$input['id'];
        $sql = "DELETE FROM staff WHERE id=$id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Staff member deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>