<?php
session_start();
require_once '../../database.php';
include('../../head.php');

// Security: Only Admins allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Get some quick stats for the dashboard
$adminCount = $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'admin' AND is_deleted = 0")->fetchColumn();
$memberCount = $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'member' AND is_deleted = 0")->fetchColumn();

$_title = "Admin Dashboard";
include('../_head.php'); // Adjust path as needed
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
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.3s ease;
        text-decoration: none;
        color: #333;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
    }
    .card i {
        font-size: 40px;
        color: #667eea;
        margin-bottom: 15px;
    }
    .card h3 { margin-bottom: 10px; }
    .stat { font-size: 24px; font-weight: bold; color: #764ba2; }
</style>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</h1>
    <p>What would you like to manage today?</p>

    <div class="dashboard-grid">
        <a href="members/index.php" class="card">
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

<?php include('../_foot.php'); ?>