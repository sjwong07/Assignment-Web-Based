<?php
require_once '../../config.php';

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $full_name = mysqli_real_escape_string($connection, $_POST['full_name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = mysqli_real_escape_string($connection, $_POST['gender']); 
    $role = 'admin';

    // Handle Profile Photo Upload
    $profile_photo = 'default.png';
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "../../uploads/profiles/";
        $file_ext = pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        $profile_photo = time() . "_" . $username . "." . $file_ext;
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_dir . $profile_photo);
    }

    // Check if username or email exists
    $check = mysqli_query($connection, "SELECT * FROM user WHERE username='$username' OR email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username or Email already exists!";
    } else {
        // FIXED SQL: Changed 'status' to 'is_blocked' and set default to 0 (Active)
        $sql = "INSERT INTO user (username, full_name, email, password, gender, role, profile_photo, is_blocked) 
                VALUES ('$username', '$full_name', '$email', '$password', '$gender', '$role', '$profile_photo', 0)";
        
        if (mysqli_query($connection, $sql)) {
            header("Location: index.php?msg=AdminAdded");
            exit();
        } else {
            $error = "Registration failed: " . mysqli_error($connection);
        }
    }
}

$_title = "Add New Admin";
include('../../lib/_head.php');
?>

<div class="container" style="padding: 40px; display: flex; justify-content: center; background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif;">
    <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border: 1px solid #e2e8f0;">
        
        <h2 style="margin-bottom: 30px; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-plus" style="color: #4f46e5;"></i> Add New Admin
        </h2>

        <?php if($error): ?>
            <div style="padding: 12px; background: #fee2e2; color: #b91c1c; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Profile Photo</label>
                <div style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px dashed #e2e8f0; border-radius: 12px; background: #f8fafc;">
                    <i class="fas fa-image" style="font-size: 1.5rem; color: #94a3b8;"></i>
                    <input type="file" name="profile_photo" accept="image/*">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Username</label>
                <input type="text" name="username" required placeholder="e.g. admin_josh" 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; transition: 0.3s;"
                       onfocus="this.style.borderColor='#4f46e5'">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Full Name</label>
                <input type="text" name="full_name" required placeholder="Enter full name" 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Email Address</label>
                <input type="email" name="email" required placeholder="email@shop.com" 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Gender</label>
                <select name="gender" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: white; cursor: pointer;">
                    <option value="">Select Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Temporary Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 2; background: #4f46e5; color: white; padding: 14px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">
                    Create Admin Account
                </button>
                <a href="index.php" style="flex: 1; text-align: center; background: #f1f5f9; color: #64748b; padding: 14px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: 0.2s;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include('../../lib/_foot.php'); ?>