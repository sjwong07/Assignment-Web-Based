<?php
require_once '../../config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    // Ensure we only delete users with the 'member' role for safety
    $sql = "DELETE FROM user WHERE user_id = ? AND role = 'member'";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php");
exit();