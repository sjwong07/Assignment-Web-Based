<?php
require_once '../../config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;
$member = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM user WHERE user_id = $user_id"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $photo_name = $member['profile_photo'];

    // Handle Profile Photo Upload
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "../../uploads/profiles/";
        $photo_name = time() . "_" . $_FILES['profile_photo']['name'];
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_dir . $photo_name);
    }

    $sql = "UPDATE user SET full_name=?, email=?, gender=?, profile_photo=? WHERE user_id=?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $gender, $photo_name, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?success=updated");
        exit();
    }
}

$_title = "Edit Member";
include('../../lib/_head.php'); 
?>

<div class="container" style="display: flex; justify-content: center; padding: 50px 0;">
    <form method="POST" enctype="multipart/form-data" style="background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 25px;"><i class="fas fa-user-edit"></i> Edit Member: <?= htmlspecialchars($member['username']) ?></h3>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="<?= BASE_URL ?>/uploads/profiles/<?= $member['profile_photo'] ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
            <input type="file" name="profile_photo" style="font-size: 0.8rem;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight:bold;">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight:bold;">Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="font-weight:bold;">Gender</label>
            <select name="gender" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <option value="Male" <?= $member['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $member['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $member['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <button type="submit" style="background: #f59e0b; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%;">Update Member</button>
        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: #64748b; text-decoration: none;">Cancel</a>
        </div>
    </form>
</div>