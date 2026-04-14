<?php
session_start();
require_once '../../config/database.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$user_id]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Detail</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 550px; margin: auto; background: white; padding: 25px; border-radius: 8px; }
        h1 { text-align: center; margin-bottom: 20px; }
        .profile-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 20px; border: 3px solid #007bff; }
        .row { margin: 12px 0; padding: 8px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; width: 120px; display: inline-block; }
        .status-active { color: green; font-weight: bold; }
        .status-blocked { color: red; font-weight: bold; }
        .btn-back { display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Member Details</h1>
        
       <?php
       $photo = !empty($member['profile_photo']) ? $member['profile_photo'] : 'default.png';
       ?>
       <img src="../../uploads/profiles/<?= $photo ?>" class="profile-img">
        
        <div class="row"><span class="label">User ID:</span> <?= $member['user_id'] ?></div>
        <div class="row"><span class="label">Username:</span> <?= htmlspecialchars($member['username']) ?></div>
        <div class="row"><span class="label">Full Name:</span> <?= htmlspecialchars($member['full_name']) ?></div>
        <div class="row"><span class="label">Email:</span> <?= htmlspecialchars($member['email']) ?></div>
        <div class="row"><span class="label">Phone:</span> <?= htmlspecialchars($member['phone']) ?></div>
        <div class="row"><span class="label">Gender:</span> <?= $member['gender'] === 'M' ? 'Male' : ($member['gender'] === 'F' ? 'Female' : '-') ?></div>
        <div class="row"><span class="label">Role:</span> <?= $member['role'] ?></div>
        <div class="row"><span class="label">Status:</span> <span class="<?= $member['is_blocked'] ? 'status-blocked' : 'status-active' ?>"><?= $member['is_blocked'] ? 'Blocked' : 'Active' ?></span></div>
        <div class="row"><span class="label">Registered:</span> <?= !empty($member['created_at']) ? date('Y-m-d H:i', strtotime($member['created_at'])) : '-' ?></div>
        
        <a href="index.php" class="btn-back">← Back to Members</a>
    </div>
</body>
</html>