<?php
require_once '../config.php'; 

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); 
    exit();
}

$_title = "Admin Management";
include('../../lib/_head.php'); 

// Fetch Admins
$query = "SELECT * FROM user WHERE role = 'admin' ORDER BY user_id DESC";
$result = mysqli_query($connection, $query);
?>

<div class="container mt-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2><i class="fas fa-user-shield"></i> Admin Team</h2>
        
        <a href="members/create.php" class="nav-link active" style="border-radius: 8px; padding: 10px 20px;">
            <i class="fas fa-plus"></i> Add New Admin
        </a>
    </div>

    <div class="stat-card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-light); text-align: left;">
                    <th style="padding: 15px;">Username</th>
                    <th style="padding: 15px;">Full Name</th>
                    <th style="padding: 15px;">Email</th>
                    <th style="padding: 15px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr style="border-top: 1px solid var(--border-color);">
                    <td style="padding: 15px;"><?= htmlspecialchars($row['username']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($row['full_name']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($row['email']) ?></td>
                    <td style="padding: 15px;">
                        <a href="members/edit.php?id=<?= $row['user_id'] ?>" style="color: orange; margin-right:10px;"><i class="fas fa-edit"></i></a>
                        <a href="members/delete.php?id=<?= $row['user_id'] ?>" style="color: red;" onclick="return confirm('Delete admin?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../../lib/_foot.php'); ?>