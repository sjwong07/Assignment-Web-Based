<?php
require_once '../../config.php'; 

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$_title = "Create User Account";
include('../../lib/_head.php'); 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'member';
    $password = $_POST['password'] ?? ''; 
    $profile_photo = 'default.png.jpg'; 

    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if user exists
        $sql_check = "SELECT user_id FROM user WHERE username = ? OR email = ?";
        $stmt_check = mysqli_prepare($connection, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = 'Username or Email already taken.';
        } else {
            // Handle Photo Upload (Note the ../../ for pathing)
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $new_name = "user_" . time() . "." . $ext;
                $upload_path = "../../uploads/profiles/" . $new_name;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $profile_photo = $new_name;
                }
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO user (username, full_name, email, password, profile_photo, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $username, $full_name, $email, $hashed_password, $profile_photo, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                // If you created an admin, go to the admin list. Otherwise, member list.
                $redirect = ($role === 'admin') ? "../admin_management.php" : "index.php";
                header("Location: $redirect?success=1");
                exit();
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="stat-card" style="max-width: 500px; margin: 0 auto;">
        <h2>Add User</h2>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="full_name" style="width:100%; padding:8px;" required>
            </div>
            
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" style="width:100%; padding:8px;" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" style="width:100%; padding:8px;" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" style="width:100%; padding:8px;" required>
            </div>

            <div class="mb-3">
                <label>User Role</label>
                <select name="role" style="width:100%; padding:8px;">
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Profile Photo</label>
                <input type="file" name="photo">
            </div>

            <button type="submit" class="nav-link active" style="width:100%; border:none; cursor:pointer;">
                Create User
            </button>
            
            <p class="mt-3 text-center">
                <a href="../admin_management.php">Cancel and go back</a>
            </p>
        </form>
    </div>
</div>

<?php include('../../lib/_foot.php'); ?>