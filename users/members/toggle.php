<?php
// 1. Point to your main config file
require_once '../config.php'; 

// 2. Security check: Only Admins can toggle status
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

// 3. Get the Member ID and their Current Block Status from the URL
$id = $_GET['id'] ?? null;
$current_blocked_status = isset($_GET['current']) ? (int)$_GET['current'] : 0;

if ($id && is_numeric($id)) {
    // 4. Logic: Flip the bit. If 0 (active), set to 1 (blocked). If 1, set to 0.
    $new_blocked_status = ($current_blocked_status === 0) ? 1 : 0;

    // 5. Update the database using MySQLi ($connection)
    $sql = "UPDATE user SET is_blocked = ? WHERE user_id = ? AND role = 'member'";
    $stmt = mysqli_prepare($connection, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $new_blocked_status, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect back to the member list
            header("Location: index.php?msg=StatusUpdated");
            exit();
        } else {
            echo "Error updating status: " . mysqli_error($connection);
        }
    }
} else {
    header("Location: index.php");
    exit();
}