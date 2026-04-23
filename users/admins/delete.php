<?php
require_once '../../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') exit;

$id = intval($_GET['id'] ?? 0);

// Prevent deleting yourself
if ($id && $id != $_SESSION['user_id']) {
    $stmt = mysqli_prepare($connection, "DELETE FROM user WHERE user_id = ? AND role = 'admin'");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php?msg=Deleted");
exit();