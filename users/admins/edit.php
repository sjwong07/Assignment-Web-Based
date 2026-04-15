<?php
session_start();
require_once '../config/database.php';

// 1. Security & Role Check - Fix path to root login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php'); 
    exit();
}

// 2. ID Validation
$user_id = $_GET['id'] ?? null;
if (!$user_id || !is_numeric($user_id)) {
    die('Invalid ID');
}

// 3. Fetch current data - REMOVED is_deleted because your DB doesn't have it
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: index.php');
    exit();
}

$error = '';

// 4. Handle Post Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Check if email or username is already taken by ANOTHER user
    $check = $pdo->prepare("SELECT user_id FROM user WHERE (username = ? OR email = ?) AND user_id != ?");
    $check->execute([$username, $email, $user_id]);
    
    if ($check->fetch()) {
        $error = "Username or Email is already in use by another account.";
    } else {
        // Update basic info
        $sql = "UPDATE user SET username = ?, full_name = ?, email = ?, phone = ?, gender = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $full_name, $email, $phone, $gender, $user_id])) {
            header('Location: index.php?msg=updated');
            exit();
        } else {
            $error = "Failed to update database.";
        }
    }
}

$_title = "Edit Admin";
include('../../lib/_head.php');
?>

<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .btn-update { background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
    .btn-cancel { background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-left: 10px; display: inline-block; }
</style>

<div class="container">
    <h1>Edit Admin: <?= htmlspecialchars($admin['username']) ?></h1>

    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px;"><?= $error ?></div>
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

<?php include('../../lib/_foot.php');?>