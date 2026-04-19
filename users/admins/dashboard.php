<?php
require_once '../../config.php'; // Path depends on folder depth

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// 2. Security Check: Redirect non-admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// 3. Stats Queries using MySQLi ($connection) instead of PDO ($pdo)
$adminResult = mysqli_query($connection, "SELECT COUNT(*) as total FROM user WHERE role = 'admin'");
$adminData = mysqli_fetch_assoc($adminResult);
$adminCount = $adminData['total'];

$memberResult = mysqli_query($connection, "SELECT COUNT(*) as total FROM user WHERE role = 'member'");
$memberData = mysqli_fetch_assoc($memberResult);
$memberCount = $memberData['total'];

$_title = "Admin Dashboard";

// 4. Correct relative path to your header
include '../../lib/_head.php'; 
?>



<div class="container" style="padding: 40px;">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
    <p>Admin Control Panel — Manage your store and users below.</p>

    <div class="dashboard-grid">
        <a href="index.php" class="card">
            <i class="fas fa-user-shield"></i>
            <h3>Admin Management</h3>
            <p>Add, Edit, or Remove Admins</p>
            <div class="stat"><?= $adminCount ?> Total</div>
        </a>

        <a href="../members/index.php" class="card">
            <i class="fas fa-users"></i>
            <h3>Member Maintenance</h3>
            <p>Search, Block, or Edit Members</p>
            <div class="stat"><?= $memberCount ?> Total</div>
        </a>

        <a href="../../index.php" class="card">
            <i class="fas fa-store"></i>
            <h3>View Store</h3>
            <p>Back to the main website</p>
        </a>
    </div>
</div>

<?php 
include '../../lib/_foot.php'; 
?>