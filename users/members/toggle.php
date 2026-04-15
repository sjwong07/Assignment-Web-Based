<?php
session_start();
require_once '../../config/database.php';

//Security check: Only Admins can toggle status
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

// Get the Member ID and their Current Status from the URL
$id = $_GET['id'] ?? null;
$current_status = $_GET['status'] ?? 'active';

// Logic: If they are active, block them. If they are blocked, activate them.
$new_status = ($current_status === 'active') ? 'blocked' : 'active';

if ($id && is_numeric($id)) {
    // Update the database
    $stmt = $pdo->prepare("UPDATE user SET status = ? WHERE user_id = ? AND role = 'member'");
    
    if ($stmt->execute([$new_status, $id])) {
        // Redirect back to the member list with a success message
        header("Location: index.php?msg=StatusUpdated");
        exit();
    } else {
        echo "Error updating status.";
    }
} else {
    header("Location: index.php");
    exit();
}