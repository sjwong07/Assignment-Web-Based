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
include('../lib/_head.php'); 
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }
    .card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        text-decoration: none;
        color: #333;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s;
        text-align: center;
        border: 1px solid #eee;
    }
    .card:hover { transform: translateY(-5px); }
    .card i { font-size: 2.5rem; color: #3b82f6; margin-bottom: 15px; }
    .stat { font-weight: bold; font-size: 1.2rem; color: #1e293b; margin-top: 10px; }
</style>

<div class="container" style="padding: 40px;">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
    <p>Admin Control Panel — Manage your store and users below.</p>

    <div class="dashboard-grid">
        <a href="admins/index.php" class="card">
            <i class="fas fa-user-shield"></i>
            <h3>Admin Management</h3>
            <p>Add, Edit, or Remove Admins</p>
            <div class="stat"><?= $adminCount ?> Total</div>
        </a>

        <a href="members/index.php" class="card">
            <i class="fas fa-users"></i>
            <h3>Member Maintenance</h3>
            <p>Search, Block, or Edit Members</p>
            <div class="stat"><?= $memberCount ?> Total</div>
        </a>

        <a href="../index.php" class="card">
            <i class="fas fa-store"></i>
            <h3>View Store</h3>
            <p>Back to the main website</p>
        </a>
    </div>
</div>

<?php 
include('../lib/_foot.php'); 
?>