<?php
require_once '../../config.php'; // Path depends on folder depth
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// 2. Security Check: Only admins can delete
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// 3. ID Validation
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    die('Invalid ID');
}

// 4. Prevent Admin from deleting themselves (Safety Feature)
if ($user_id == $_SESSION['user_id']) {
    header('Location: index.php?error=self_delete');
    exit();
}

// 5. Perform Delete using MySQLi ($connection)
$sql = "DELETE FROM user WHERE user_id = ? AND role = 'admin'";
$stmt = mysqli_prepare($connection, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id); 
    
    if (mysqli_stmt_execute($stmt)) {
        header('Location: index.php?msg=deleted');
        exit();
    } else {
        die("Error deleting record: " . mysqli_error($connection));
    }
} else {
    die("Database error: " . mysqli_error($connection));
}
?>