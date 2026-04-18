<?php
// 1. Point to your main config file
require_once '../config.php'; 

// 2. Security: Only Admins can see member details
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 3. ID Validation
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 4. Fetch member data using MySQLi
$sql = "SELECT * FROM user WHERE user_id = ? AND role = 'member'";
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);

if (!$member) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Detail - Admin Panel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; padding: 40px; }
        .container { max-width: 550px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { text-align: center; margin-bottom: 25px; color: #333; }
        .profile-img { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 20px; border: 4px solid #3b82f6; }
        .row { margin: 0; padding: 15px; border-bottom: 1px solid #f0f0f0; display: flex; }
        .label { font-weight: bold; width: 140px; color: #555; flex-shrink: 0; }
        .value { color: #333; }
        .status-active { color: #2dce89; font-weight: bold; }
        .status-blocked { color: #f5365c; font-weight: bold; }
        .btn-back { display: inline-block; padding: 10px 25px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; transition: 0.3s; }
        .btn-back:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Member Profile</h1>
        
        <?php
        $photoName = !empty($member['profile_photo']) ? $member['profile_photo'] : 'default.png.jpg';
        $photoPath = "../../uploads/profiles/" . $photoName;
        ?>
        <img src="<?= $photoPath ?>" class="profile-img" alt="Profile Photo">
        
        <div class="row"><span class="label">User ID:</span> <span class="value">#<?= $member['user_id'] ?></span></div>
        <div class="row"><span class="label">Username:</span> <span class="value"><?= htmlspecialchars($member['username']) ?></span></div>
        <div class="row"><span class="label">Full Name:</span> <span class="value"><?= htmlspecialchars($member['full_name'] ?? 'N/A') ?></span></div>
        <div class="row"><span class="label">Email:</span> <span class="value"><?= htmlspecialchars($member['email']) ?></span></div>
        <div class="row"><span class="label">Phone:</span> <span class="value"><?= htmlspecialchars($member['phone'] ?: 'N/A') ?></span></div>
        <div class="row"><span class="label">Gender:</span> <span class="value"><?= $member['gender'] === 'M' ? 'Male' : ($member['gender'] === 'F' ? 'Female' : '-') ?></span></div>
        
        <div class="row">
            <span class="label">Account Status:</span> 
            <?php if ($member['is_blocked'] == 0): ?>
                <span class="value status-active">ACTIVE</span>
            <?php else: ?>
                <span class="value status-blocked">BLOCKED</span>
            <?php endif; ?>
        </div>
        
        <div class="row"><span class="label">Joined Date:</span> <span class="value"><?= date('d M Y, H:i', strtotime($member['created_at'])) ?></span></div>
        
        <div style="text-align: center;">
            <a href="index.php" class="btn-back">← Back to Members</a>
        </div>
    </div>
</body>
</html>