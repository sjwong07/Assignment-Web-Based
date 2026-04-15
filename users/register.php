<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'member'; 
    
    if (empty($username) || empty($full_name) || empty($email) || empty($phone) || empty($gender) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 4) {
       $error = 'Password must be at least 4 characters';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
       
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $profile_photo = 'default.png';
            $upload_dir = 'uploads/profiles/';

            // Ensure directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // FIXED: Match name 'profile_photo' from HTML
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $new_name = "user_" . time() . "." . $ext; 
                
                // FIXED: Removed ../../ because this file is in the root
                $destination = $upload_dir . $new_name;
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
                    $profile_photo = $new_name;
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO user (username, full_name, email, phone, gender, password, role, profile_photo, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$username, $full_name, $email, $phone, $gender, $hashed_password, $role, $profile_photo])) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Database error.";
            }
        }
    }
}
?>