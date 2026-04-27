<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../../config/dbconn.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$staff_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_POST['action'] ?? ($input['action'] ?? '');

switch ($action) {
    case 'update_profile':
        // Get and validate input (from POST or JSON)
        $firstName = trim($_POST['firstName'] ?? ($input['firstName'] ?? ''));
        $lastName = trim($_POST['lastName'] ?? ($input['lastName'] ?? ''));
        $email = trim($_POST['email'] ?? ($input['email'] ?? ''));
        $contact = trim($_POST['contact_number'] ?? ($input['contact_number'] ?? ''));

        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'First name, last name, and email are required']);
            break;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            break;
        }

        // Check if email is already used by another staff
        $check_stmt = $conn->prepare("SELECT id FROM staff WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $staff_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $check_stmt->close();
            echo json_encode(['success' => false, 'message' => 'Email already in use by another staff member']);
            break;
        }
        $check_stmt->close();

        // Handle File Upload if a new file was provided
        $profilePictureFilename = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            // Server-side size validation
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File exceeds 2MB limit.']);
                exit;
            }

            // Server-side type validation
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMimes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file format.']);
                exit;
            }

            // Ensure directory exists
            $uploadDir = '../../uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Standardize file name: staff_{id}_{time}.ext
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = "staff_{$staff_id}_" . time() . ".{$ext}";
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $profilePictureFilename = $filename;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                exit;
            }
        }

        // Determine whether to update profile picture
        if ($profilePictureFilename) {
            $query = "UPDATE staff SET firstName = ?, lastName = ?, email = ?, contact_number = ?, profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $firstName, $lastName, $email, $contact, $profilePictureFilename, $staff_id);
        } else {
            $query = "UPDATE staff SET firstName = ?, lastName = ?, email = ?, contact_number = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $firstName, $lastName, $email, $contact, $staff_id);
        }

        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            if ($profilePictureFilename) {
                $_SESSION['profile_picture'] = $profilePictureFilename;
            }

            $stmt->close();

            // Get updated image path for response if it was uploaded, else get current
            $responsePic = $profilePictureFilename;

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email,
                    'contact_number' => $contact,
                    'profile_picture' => $responsePic
                ]
            ]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $error]);
        }
        break;

    case 'change_password':
        // Get input
        $currentPassword = $input['currentPassword'] ?? '';
        $newPassword = $input['newPassword'] ?? '';

        // Validate input
        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Both current and new password are required']);
            break;
        }

        // Basic password length validation
        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long']);
            break;
        }

        // Get current password from database
        $stmt = $conn->prepare("SELECT password FROM staff WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            break;
        }

        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();
        $stmt->close();

        if (!$staff) {
            echo json_encode([
                'success' => false,
                'message' => 'Staff member not found',
                'debug' => ['staff_id' => $staff_id]
            ]);
            break;
        }

        $stored_password = $staff['password'];
        $password_correct = false;

        // Check if stored password is hashed or plain text
        if (substr($stored_password, 0, 4) === '$2y$') {
            // Hashed password - use password_verify
            $password_correct = password_verify($currentPassword, $stored_password);
        } else {
            // Plain text password (old data) - compare directly
            $password_correct = ($currentPassword === $stored_password);
        }

        if (!$password_correct) {
            echo json_encode([
                'success' => false,
                'message' => 'Current password is incorrect',
                'debug' => [
                    'stored_password_preview' => substr($stored_password, 0, 10) . '...',
                    'is_hashed' => (substr($stored_password, 0, 4) === '$2y$')
                ]
            ]);
            break;
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Verify that the hashed password was created successfully
        if (strlen($hashedPassword) < 60) {
            echo json_encode([
                'success' => false,
                'message' => 'Error creating password hash. Column might be too short.',
                'debug' => [
                    'hash_length' => strlen($hashedPassword),
                    'staff_id' => $staff_id
                ]
            ]);
            break;
        }

        // Update password
        $update_stmt = $conn->prepare("UPDATE staff SET password = ? WHERE id = ?");
        if (!$update_stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to prepare update: ' . $conn->error,
                'debug' => ['error' => $conn->error]
            ]);
            break;
        }

        $update_stmt->bind_param("si", $hashedPassword, $staff_id);

        if ($update_stmt->execute()) {
            $rows_affected = $update_stmt->affected_rows;
            $update_stmt->close();

            // CRITICAL: Verify what actually got saved in database
            $verify_stmt = $conn->prepare("SELECT password, LENGTH(password) as pwd_len FROM staff WHERE id = ?");
            $verify_stmt->bind_param("i", $staff_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $saved_data = $verify_result->fetch_assoc();
            $verify_stmt->close();

            $saved_password = $saved_data['password'];
            $saved_length = $saved_data['pwd_len'];

            // Check if password was truncated
            if ($saved_length < 60) {
                echo json_encode([
                    'success' => false,
                    'message' => 'CRITICAL ERROR: Password was truncated!',
                    'debug' => [
                        'issue' => 'Password column is too short',
                        'expected_length' => strlen($hashedPassword),
                        'actual_saved_length' => $saved_length,
                        'truncated' => true,
                        'rows_affected' => $rows_affected,
                        'fix_required' => 'ALTER TABLE staff MODIFY COLUMN password VARCHAR(255) NOT NULL;',
                        'saved_password_preview' => substr($saved_password, 0, 20) . '...'
                    ]
                ]);
            } else if ($rows_affected > 0) {
                // Test if the saved password can be verified
                $can_verify = password_verify($newPassword, $saved_password);

                echo json_encode([
                    'success' => true,
                    'message' => 'Password changed successfully',
                    'debug' => [
                        'rows_affected' => $rows_affected,
                        'hash_created_length' => strlen($hashedPassword),
                        'saved_password_length' => $saved_length,
                        'hash_properly_saved' => ($saved_length >= 60),
                        'can_verify_new_password' => $can_verify,
                        'saved_password_preview' => substr($saved_password, 0, 20) . '...',
                        'staff_id' => $staff_id
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No rows were updated',
                    'debug' => [
                        'rows_affected' => $rows_affected,
                        'staff_id' => $staff_id,
                        'note' => 'Password might be the same as current'
                    ]
                ]);
            }
        } else {
            $error = $update_stmt->error;
            $update_stmt->close();
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update password: ' . $error,
                'debug' => [
                    'mysql_error' => $error,
                    'staff_id' => $staff_id
                ]
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
