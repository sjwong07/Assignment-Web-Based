<?php
require '../lib/_base.php';
$_title = 'Home';
include '../lib/_head.php';
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background: #f5f5f5;
}

.container {
    max-width: 800px;
    margin: 50px auto;
    text-align: center;
}

h1 {
    color: #333;
    margin-bottom: 30px;
}

.nav-box {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.nav-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    min-width: 200px;
    text-decoration: none;
    color: #333;
    transition: transform 0.2s;
}

.nav-card:hover {
    transform: scale(1.05);
    background: #667eea;
    color: white;
}

.nav-card h2 {
    margin: 0 0 10px 0;
}

.nav-card p {
    margin: 0;
    opacity: 0.8;
}
</style>

<div class="container">
    <h1>🛍️ E-Commerce System</h1>
    
    <div class="nav-box">
        <a href="ProductMember.php" class="nav-card">
            <h2>🛒 Shop</h2>
            <p>Browse Products</p>
        </a>
        
        <a href="ProductAdmin.php" class="nav-card">
            <h2>⚙️ Admin</h2>
            <p>Manage Products</p>
        </a>
        
        <a href="history.php" class="nav-card">
            <h2>📋 Orders</h2>
            <p>View History</p>
        </a>
    </div>
    
    <?php
    $product_count = $_db->query("SELECT COUNT(*) FROM Product")->fetchColumn();
    ?>
    <p style="margin-top: 40px; color: #666;">
        Currently <?= $product_count ?> products available
    </p>
</div>

<?php include '../lib/_foot.php'; ?>