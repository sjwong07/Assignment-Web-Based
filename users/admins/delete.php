<?php
session_start();
require_once '../../config/database.php';

//Security Checks
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

//Validate ID
$target_id = $_GET['id'] ?? null;

if (!$target_id || !is_numeric($target_id)) {
    die('Error: Invalid ID provided.');
}

// Prevent self-deletion
if ($target_id == $_SESSION['user_id']) {
    die('Error: You cannot delete your own account while logged in.');
}

// Verify the target admin exists and is actually an admin
$stmt = $pdo->prepare("SELECT id FROM user WHERE id = ? AND role = 'admin' AND is_deleted = 0");
$stmt->execute([$target_id]);
$admin = $stmt->fetch();

if (!$admin) {
    die('Error: Admin account not found or already deleted.');
}

//Perform Soft Delete
$stmt = $pdo->prepare("UPDATE user SET is_deleted = 1 WHERE id = ?");
if ($stmt->execute([$target_id])) {
    // Redirect with a success flag
    header('Location: index.php?msg=deleted');
    exit();
} else {
    die('Error: Could not update the database.');
}
?>