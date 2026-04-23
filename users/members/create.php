<?php
require_once '../../config.php';

// Strict Admin Access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $photo_name = 'default.png'; // Default value

    // Handle Profile Photo Upload
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "../../uploads/profiles/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $photo_name = time() . "_" . basename($_FILES['profile_photo']['name']);
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_dir . $photo_name);
    }

    // Check for existing users
    $check = mysqli_query($connection, "SELECT user_id FROM user WHERE username = '$username' OR email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username or Email already exists.";
    } else {
        $sql = "INSERT INTO user (username, full_name, email, gender, password, profile_photo, role) VALUES (?, ?, ?, ?, ?, ?, 'member')";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $username, $full_name, $email, $gender, $password, $photo_name);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?success=1");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}

$_title = "Add New Member";
include '../../lib/_head.php';
?>

<div class="container" style="display: flex; justify-content: center; padding: 50px 0; background-color: #f0f2f5;">
    <form method="POST" enctype="multipart/form-data" style="background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 25px; color: #1e293b;"><i class="fas fa-user-plus"></i> Add New Member</h3>

        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-bottom: 20px;">
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*" style="font-size: 0.8rem;">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Username</label>
            <input type="text" name="username" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Full Name</label>
            <input type="text" name="full_name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email Address</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Gender</label>
            <select name="gender" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <button type="submit" style="background: #22c55e; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%;">Save Member</button>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: #64748b; text-decoration: none;">Cancel</a>
        </div>
    </form>
</div>

<?php include '../../lib/_foot.php'; ?>