<?php
session_start();
require_once '../../config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

//Check admin role
if ($_SESSION['role'] != 'admin') {
    die('Access denied');
}

// Validate ID
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    die('Invalid ID');
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ? AND role = 'admin' AND is_deleted = 0");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE user SET username = ?, full_name = ?, email = ?, phone = ?, gender = ? WHERE user_id = ?");
    $stmt->execute([$username, $full_name, $email, $phone, $gender, $user_id]);
    
    header('Location: index.php');
    exit();
}

// Page title
$_title = "Edit Admin";
$_subtitle = "Update admin information";
include('../../_head.php');
?>

    <div class="container">
        <h1>Edit Admin</h1>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone']) ?>" required>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="M" <?= $admin['gender'] == 'M' ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= $admin['gender'] == 'F' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <button type="submit">Update</button>
            <a href="index.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
<?php include('../../_foot.php');?>