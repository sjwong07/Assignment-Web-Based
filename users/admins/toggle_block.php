<?php
require_once '../../config.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_admin_id = $_SESSION['user_id'] ?? 0;

if ($id > 0 && $id != $current_admin_id) {
    // 1. Fetch current value from the 'is_blocked' column
    $stmt = mysqli_prepare($connection, "SELECT is_blocked FROM user WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // 2. Toggle the number (0 becomes 1, 1 becomes 0)
        $new_value = ($user['is_blocked'] == 0) ? 1 : 0;
        
        $update_stmt = mysqli_prepare($connection, "UPDATE user SET is_blocked = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($update_stmt, "ii", $new_value, $id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            header("Location: index.php?msg=Success");
            exit();
        }
    }
} else if ($id == $current_admin_id) {
    header("Location: index.php?error=SelfBlock");
    exit();
}

header("Location: index.php");
exit();
?>