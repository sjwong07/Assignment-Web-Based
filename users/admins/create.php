<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $raw_password = $_POST['password'] ?? ''; 
    $profile_photo = 'default.png';

 //  Validation
    if (empty($username) || empty($full_name) || empty($email) || empty($raw_password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($raw_password) < 4) {
        $error = 'Password must be at least 4 characters';
    } else {
        // 2. Check if Username/Email exists
        $stmt = $pdo->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            // 3. Handle Photo Upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $new_name = "admin_" . time() . "." . $ext;
                $upload_path = "../../uploads/profiles/" . $new_name;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $profile_photo = $new_name;
                }
            }

            // 4. Hash Password and Insert
            $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO user (username, full_name, email, phone, gender, password, profile_photo, role, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', NOW())";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $full_name, $email, $phone, $gender, $hashed_password, $profile_photo])) {
                header('Location: index.php?success=1');
                exit();
            } else {
                $error = 'Database error occurred';
            }
        }
    }
}

// UI Variables
$_title = "Add Admin";
$_subtitle = "Create new admin account";
include('../../lib/_head.php');
?>

<style>
    body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 20px; }
    .container { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
    button:hover { background: #218838; }
    .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
    .cancel-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
</style>

<div class="container">
    <h1>Add New Admin</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select</option>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <button type="submit">Create Admin</button>
        <a href="index.php" class="cancel-link">Cancel</a>
        
        <div class="form-group">
            <label>Profile Photo</label>
            <input type="file" name="photo" accept="image/*">
            <small style="color: #666;">Format: JPG, PNG (Max 2MB)</small>
        </div>
    </form>
</div>

<?php include('../../lib/_foot.php'); ?>