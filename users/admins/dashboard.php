<?php
session_start();
//Path to database
require_once '../config/database.php';

//Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

//Stats Queries
$adminCount = $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'admin'")->fetchColumn();
$memberCount = $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'member'")->fetchColumn();

$_title = "Admin Dashboard";

include('../../lib/_head.php'); 
?>

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
include('../../lib/_foot.php'); 
?>