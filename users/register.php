<?php
require_once '../config.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $gender    = $_POST['gender'] ?? '';
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'member'; 
    
    // Validation
    if (empty($username) || empty($full_name) || empty($email) || empty($phone) || empty($gender) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 4) {
       $error = 'Password must be at least 4 characters';
    } else {
        // 2. Use $connection (MySQLi) instead of $pdo
        $sql_check = "SELECT * FROM user WHERE username = ? OR email = ?";
        $stmt_check = mysqli_prepare($connection, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
       
        if (mysqli_num_rows($result_check) > 0) {
            $error = 'Username or email already exists';
        } else {
            // Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $profile_photo = 'default.png.jpg';
            $upload_dir = 'uploads/profiles/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $new_name = "user_" . time() . "." . $ext; 
                $destination = $upload_dir . $new_name;
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
                    $profile_photo = $new_name;
                }
            }
            
            // 3. Insert using MySQLi
            $sql_ins = "INSERT INTO user (username, full_name, email, phone, gender, password, role, profile_photo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ins = mysqli_prepare($connection, $sql_ins);
            
            // "ssssssss" means 8 strings
            mysqli_stmt_bind_param($stmt_ins, "ssssssss", $username, $full_name, $email, $phone, $gender, $hashed_password, $role, $profile_photo);
            
            if (mysqli_stmt_execute($stmt_ins)) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Database error: " . mysqli_error($connection);
            }
        }
    }
}
?>