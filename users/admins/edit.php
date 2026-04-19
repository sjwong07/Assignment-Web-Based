<?php
require_once '../../config.php'; // Path depends on folder depth
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// 2. Security & Role Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); 
    exit();
}

// 3. ID Validation
$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    die('Invalid ID');
}

// 4. Fetch current data using MySQLi
$sql_fetch = "SELECT * FROM user WHERE user_id = ?";
$stmt_fetch = mysqli_prepare($connection, $sql_fetch);
mysqli_stmt_bind_param($stmt_fetch, "i", $user_id);
mysqli_stmt_execute($stmt_fetch);
$result = mysqli_stmt_get_result($stmt_fetch);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    header('Location: index.php');
    exit();
}

$error = '';

// 5. Handle Post Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Check if email or username is already taken by ANOTHER user using MySQLi
    $sql_check = "SELECT user_id FROM user WHERE (username = ? OR email = ?) AND user_id != ?";
    $stmt_check = mysqli_prepare($connection, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ssi", $username, $email, $user_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $error = "Username or Email is already in use by another account.";
    } else {
        // Update basic info
        $sql_update = "UPDATE user SET username = ?, full_name = ?, email = ?, phone = ?, gender = ? WHERE user_id = ?";
        $stmt_update = mysqli_prepare($connection, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssssi", $username, $full_name, $email, $phone, $gender, $user_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            header('Location: index.php?msg=updated');
            exit();
        } else {
            $error = "Failed to update database: " . mysqli_error($connection);
        }
    }
}

$_title = "Edit Admin";
include '../../lib/_head.php';
?>

<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .btn-update { background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
    .btn-cancel { background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-left: 10px; display: inline-block; }
</style>

<div class="container" style="padding: 20px;">
    <h1>Edit Admin: <?= htmlspecialchars($admin['username']) ?></h1>

    <?php if ($error): ?>
        <div style="color: red; background: #fee2e2; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f87171;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender" required>
                <option value="M" <?= ($admin['gender'] ?? '') == 'M' ? 'selected' : '' ?>>Male</option>
                <option value="F" <?= ($admin['gender'] ?? '') == 'F' ? 'selected' : '' ?>>Female</option>
            </select>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-update">Update Information</button>
            <a href="index.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<?php include '../../lib/_foot.php';?>