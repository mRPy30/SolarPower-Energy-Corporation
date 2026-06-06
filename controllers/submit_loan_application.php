<?php
// controllers/submit_loan_application.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_pdo.php';

try {
    $db = getPDO();

    // 1. Dynamic Table Creation
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

    // 2. Validate Inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $monthly_bill = floatval($_POST['monthly_bill'] ?? 0);

    if (empty($name) || empty($email) || empty($phone)) {
        throw new Exception("Please fill in all contact details.");
    }

    // 3. Setup Uploads Directory
    $upload_dir = __DIR__ . '/../uploads/loans/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 4. File Validation & Upload Logic
    $file_keys = ['meralco_bill', 'land_title', 'membership_proof'];
    $uploaded_paths = [];

    foreach ($file_keys as $key) {
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Missing requirement document or error uploading: " . str_replace('_', ' ', $key));
        }

        $file = $_FILES[$key];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_type = $file['type'];

        // Allowed formats
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Invalid file type for " . str_replace('_', ' ', $key) . ". Only PDFs and Images are allowed.");
        }

        // Generate clean unique filename
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = ($file_type === 'application/pdf') ? 'pdf' : 'jpg';
        }
        
        $new_name = $key . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest_path = $upload_dir . $new_name;

        if (!move_uploaded_file($file_tmp, $dest_path)) {
            throw new Exception("Failed to save uploaded file: " . $file_name);
        }

        // Save relative path for database storage
        $uploaded_paths[$key] = 'uploads/loans/' . $new_name;
    }

    // 5. Insert into Database
    $stmt = $db->prepare("INSERT INTO `loan_applications` 
        (full_name, email_address, contact_number, monthly_bill, meralco_bill_path, land_title_path, membership_proof_path, status) 
        VALUES (:name, :email, :phone, :monthly_bill, :meralco, :title, :membership, 'Pending')");

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':monthly_bill' => $monthly_bill,
        ':meralco' => $uploaded_paths['meralco_bill'],
        ':title' => $uploaded_paths['land_title'],
        ':membership' => $uploaded_paths['membership_proof']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Your application has been received and saved successfully.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
