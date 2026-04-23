<?php
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Stats
$adminResult = mysqli_query($connection, "SELECT COUNT(*) as total FROM user WHERE role = 'admin'");
$adminCount = mysqli_fetch_assoc($adminResult)['total'];

$memberResult = mysqli_query($connection, "SELECT COUNT(*) as total FROM user WHERE role = 'member'");
$memberCount = mysqli_fetch_assoc($memberResult)['total'];

$_title = "Admin Dashboard";
include '../../lib/_head.php'; 
?>

<div class="container" style="padding: 40px;">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
    <p style="color: #64748b; margin-bottom: 30px;">Store Overview & Management</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <a href="index.php" style="text-decoration: none; color: inherit; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center;">
            <i class="fas fa-user-shield" style="font-size: 2.5rem; color: #7d7ed3; margin-bottom: 15px;"></i>
            <h3>Admin Team</h3>
            <p><?= $adminCount ?> Registered Admins</p>
        </a>

        <a href="../members/index.php" style="text-decoration: none; color: inherit; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center;">
            <i class="fas fa-users" style="font-size: 2.5rem; color: #10b981; margin-bottom: 15px;"></i>
            <h3>Members</h3>
            <p><?= $memberCount ?> Registered Members</p>
        </a>

        <a href="../../index.php" style="text-decoration: none; color: inherit; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center;">
            <i class="fas fa-store" style="font-size: 2.5rem; color: #f59e0b; margin-bottom: 15px;"></i>
            <h3>Front Store</h3>
            <p>Back to Homepage</p>
        </a>
    </div>
</div>

<?php include '../../lib/_foot.php'; ?>