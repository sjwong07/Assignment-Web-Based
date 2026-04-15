<?php
session_start();
require_once '../../config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// check admin role
if ($_SESSION['role'] != 'admin') {
    die('Access denied');
}

// Validate ID
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    die('Invalid ID');
}

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    die('You cannot delete your own account');
}

// Check if admin exists
$stmt = $pdo->prepare("SELECT * FROM user WHERE role = 'admin' AND is_deleted = 0");
$admin = $stmt->fetch();

if (!$admin) {
    die('Admin not found');
}

// Delete admin
$stmt = $pdo->prepare("UPDATE user SET is_deleted = 1 WHERE user_id = ? AND role = 'admin'");
$stmt->execute([$user_id]);


header('Location: index.php');
exit();
?>