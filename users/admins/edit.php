<?php
require_once '../../config.php';

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

$user_id = intval($_GET['id'] ?? 0);

// Fetch existing admin data
$query = "SELECT * FROM user WHERE user_id = $user_id AND role = 'admin'";
$res = mysqli_query($connection, $query);
$admin = mysqli_fetch_assoc($res);

if (!$admin) { 
    header("Location: index.php"); 
    exit(); 
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($connection, $_POST['full_name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $gender = mysqli_real_escape_string($connection, $_POST['gender']);
    
    // Handle Profile Photo Update
    $profile_photo = $admin['profile_photo']; // Keep old photo by default
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "../../uploads/profiles/";
        $file_ext = pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        $new_photo_name = time() . "_" . $admin['username'] . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_dir . $new_photo_name)) {
            $profile_photo = $new_photo_name;
            // Optional: Delete old photo file here if it's not default.png
        }
    }

    $stmt = mysqli_prepare($connection, "UPDATE user SET full_name = ?, email = ?, gender = ?, profile_photo = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $gender, $profile_photo, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?msg=Updated");
        exit();
    } else {
        $error = "Update failed: " . mysqli_error($connection);
    }
}

$_title = "Edit Admin";
include '../../lib/_head.php';
?>

<div class="container" style="padding: 40px; display: flex; justify-content: center; background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif;">
    <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border: 1px solid #e2e8f0;">
        
        <h2 style="margin-bottom: 10px; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-edit" style="color: #4f46e5;"></i> Edit Admin
        </h2>
        <p style="color: #64748b; margin-bottom: 30px;">Updating account for: <strong><?= htmlspecialchars($admin['username']) ?></strong></p>

        <?php if($error): ?>
            <div style="padding: 12px; background: #fee2e2; color: #b91c1c; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 25px; text-align: center;">
                <img src="<?= BASE_URL ?>/uploads/profiles/<?= $admin['profile_photo'] ?>" 
                     style="width: 80px; height: 80px; border-radius: 15px; object-fit: cover; border: 2px solid #4f46e5; margin-bottom: 10px;">
                <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 8px;">Change Profile Photo</label>
                <input type="file" name="profile_photo" style="font-size: 0.8rem; color: #94a3b8;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569;">Gender</label>
                <select name="gender" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: white;">
                    <option value="M" <?= ($admin['gender'] == 'M') ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= ($admin['gender'] == 'F') ? 'selected' : '' ?>>Female</option>
                    <option value="O" <?= ($admin['gender'] == 'O') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 2; background: #4f46e5; color: white; padding: 14px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s;">
                    Update Changes
                </button>
                <a href="index.php" style="flex: 1; text-align: center; background: #f1f5f9; color: #64748b; padding: 14px; border-radius: 10px; text-decoration: none; font-weight: 600;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../../lib/_foot.php'; ?>