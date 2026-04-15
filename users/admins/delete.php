<?php
session_start();
require_once '../config/database.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// 2. ID Validation
$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    die('Invalid ID');
}

// 3. Perform Delete
try {
    // If you want to permanently delete:
    $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ? AND role = 'admin'");
    
    // OR if you added a 'status' column later:
    // $stmt = $pdo->prepare("UPDATE user SET status = 'inactive' WHERE user_id = ?");
    
    $stmt->execute([$user_id]);
    header('Location: index.php?msg=deleted');
} catch (PDOException $e) {
    die("Error deleting record: " . $e->getMessage());
}
?>