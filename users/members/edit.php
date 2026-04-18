<?php
// 1. Point to your main config file
require_once '../config.php'; 

// Security: Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) { 
    header("Location: index.php"); 
    exit(); 
}

// 2. Fetch current member data using MySQLi ($connection)
$sql = "SELECT * FROM user WHERE user_id = ? AND role = 'member'";
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);

if (!$member) { 
    die("Member not found."); 
}

// 3. Handle the Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $photo_name = $member['profile_photo']; 

    // 4. Handle Photo Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $target_dir = "../../uploads/profiles/"; // Match your folder structure
        
        // Ensure folder exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = "user_" . $id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $photo_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {

        }
    }

    // 5. Update Database using MySQLi
    $update_sql = "UPDATE user SET full_name = ?, email = ?, phone = ?, profile_photo = ? WHERE user_id = ?";
    $update_stmt = mysqli_prepare($connection, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssssi", $full_name, $email, $phone, $photo_name, $id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        header("Location: index.php?success=MemberUpdated");
        exit();
    } else {
        $error = "Update failed: " . mysqli_error($connection);
    }
}

$_title = "Edit Member";
include('../../lib/_head.php');
?>

<div class="container" style="padding: 20px;">
    <h2>Edit Member Maintenance</h2>
    <a href="index.php" style="text-decoration: none; color: #3b82f6;">← Back to List</a>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 15px;">
            <label style="font-weight: bold;">Current Photo:</label><br>
            <?php 
                $displayPhoto = !empty($member['profile_photo']) ? $member['profile_photo'] : 'default.png.jpg'; 
            ?>
            <img src="../../uploads/profiles/<?= $displayPhoto ?>" width="100" height="100" style="border-radius: 50%; object-fit: cover; border: 2px solid #ddd; margin: 10px 0;">
            <br>
            <label>Change Photo:</label>
            <input type="file" name="photo">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold;">Full Name:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name'] ?? '') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold;">Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold;">Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <button type="submit" style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Update Member Details</button>
    </form>
</div>

<?php include('../../lib/_foot.php'); ?>