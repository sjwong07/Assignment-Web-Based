<?php
session_start();
require_once '../../config/database.php';

// Security: Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit(); }

// 1. Fetch current member data
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ? AND role = 'member'");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) { die("Member not found."); }

// 2. Handle the Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $photo_name = $member['photo']; // Keep old photo by default

    // 3. Handle Photo Upload
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "../../assets/uploads/";
        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = "user_" . $id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $photo_name;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            // New photo uploaded successfully
        }
    }

    // 4. Update Database
    $update_sql = "UPDATE user SET full_name = ?, email = ?, phone = ?, photo = ? WHERE user_id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    
    if ($update_stmt->execute([$full_name, $email, $phone, $photo_name, $id])) {
        header("Location: index.php?success=MemberUpdated");
        exit();
    }
}

$_title = "Edit Member";
include('../../_head.php');
?>

<div class="container">
    <h2>Edit Member Maintenance</h2>
    <a href="index.php">← Back to List</a>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 20px; background: white; padding: 20px; border-radius: 10px;">
        <div style="margin-bottom: 15px;">
            <label>Current Photo:</label><br>
            <img src="../../assets/uploads/<?= $member['photo'] ?: 'default.png' ?>" width="100" style="border-radius: 10px; margin-bottom: 10px;">
            <br>
            <label>Change Photo:</label>
            <input type="file" name="photo">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" class="btn" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer;">Update Member Details</button>
    </form>
</div>

<?php include('../../_foot.php'); ?>