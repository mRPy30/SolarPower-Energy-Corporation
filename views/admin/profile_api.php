<?php
session_start();
include "../../config/dbconn.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit;
}

$staff_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'update_profile':
        $firstName = $conn->real_escape_string($input['firstName']);
        $lastName = $conn->real_escape_string($input['lastName']);
        $email = $conn->real_escape_string($input['email']);
        $contact_number = $conn->real_escape_string($input['contact_number'] ?? '');
        
        // Check if email is already used by another staff member
        $check = $conn->query("SELECT id FROM staff WHERE email='$email' AND id != $staff_id");
        if ($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already in use by another staff member']);
            break;
        }
        
        // Update staff record - using exact column names from your table
        $sql = "UPDATE staff SET 
                firstName = '$firstName', 
                lastName = '$lastName', 
                email = '$email', 
                contact_number = '$contact_number' 
                WHERE id = $staff_id";
        
        if ($conn->query($sql)) {
            // Update session variables
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update: ' . $conn->error]);
        }
        break;
        
    case 'change_password':
        $currentPassword = $input['currentPassword'];
        $newPassword = $input['newPassword'];
        
        // Get current password from database
        $stmt = $conn->prepare("SELECT password FROM staff WHERE id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();
        $stmt->close();
        
        // Verify current password
        if (!password_verify($currentPassword, $staff['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            break;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $sql = "UPDATE staff SET password = '$hashedPassword' WHERE id = $staff_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password: ' . $conn->error]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>