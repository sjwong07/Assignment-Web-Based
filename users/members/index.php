<?php
require_once '../../config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Fetch only members
$sql = "SELECT * FROM user WHERE role = 'member' ORDER BY user_id DESC";
$result = mysqli_query($connection, $sql);

$_title = "Member Management";
include('../../lib/_head.php'); 
?>

<div class="container" style="padding: 40px; background-color: #f0f2f5; min-height: 100vh;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="font-weight: bold; color: #1e293b;"><i class="fas fa-users"></i> Member Management</h2>
        <a href="create.php" style="background: #7d7ed3; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">+ Add New Member</a>
    </div>

    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: #adc4ff;">
                <tr style="text-align: left;">
                    <th style="padding: 15px;">Photo</th>
                    <th style="padding: 15px;">Username</th>
                    <th style="padding: 15px;">Full Name</th>
                    <th style="padding: 15px;">Email</th>
                    <th style="padding: 15px;">Gender</th>
                    <th style="padding: 15px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($m = mysqli_fetch_assoc($result)): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">
                        <img src="<?= BASE_URL ?>/uploads/profiles/<?= $m['profile_photo'] ?? 'default.png' ?>" 
                             style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;"
                             onerror="this.src='<?= BASE_URL ?>/uploads/profiles/default.png'">
                    </td>
                    <td style="padding: 15px;"><strong><?= htmlspecialchars($m['username']) ?></strong></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($m['full_name'] ?? '') ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($m['email']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($m['gender'] ?? 'N/A') ?></td>
                    <td style="padding: 15px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="edit.php?id=<?= $m['user_id'] ?>" style="color: #f59e0b; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete.php?id=<?= $m['user_id'] ?>" style="color: #ef4444;" onclick="return confirm('Delete this member?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../../lib/_foot.php'); ?>