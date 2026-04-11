<?php
// Start session and require base configuration
require __DIR__ . '/lib/_base.php';
require __DIR__ . '/lib/_head.php';

// Check if user is logged in (optional)
$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ELEX Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php">📱 ELEX Store</a>
        </div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link">Home</a>
            <a href="products.php" class="nav-link">Products</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="contact.php" class="nav-link">Contact</a>
            
            <?php if ($is_logged_in): ?>
                <div class="user-menu">
                    <button class="user-btn">
                        👤 <?php echo htmlspecialchars($username); ?>
                        <span>▼</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="dashboard.php">📊 Dashboard</a>
                        <a href="profile.php">👤 My Profile</a>
                        <a href="orders.php">🛒 My Orders</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" onclick="return confirm('Logout?')">🚪 Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Welcome to ELEX Store</h1>
            <p>Your one-stop shop for the latest smartphones and tablets</p>
            <?php if (!$is_logged_in): ?>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="products.php" class="btn btn-secondary">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="hero-buttons">
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <a href="products.php" class="btn btn-secondary">Shop Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container mt-5">
        <h1>Hello, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Please choose your choice below:</p>
        
        <!-- Quick Actions -->
        <div class="grid mt-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🛍️ Shop Products</h3>
                </div>
                <p>Browse our latest collection of smartphones and tablets</p>
                <a href="products.php" class="btn btn-primary mt-3">View Products</a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📦 My Orders</h3>
                </div>
                <p>Track and manage your orders</p>
                <a href="orders.php" class="btn btn-primary mt-3">View Orders</a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">👤 My Account</h3>
                </div>
                <p>Update your profile and account settings</p>
                <a href="profile.php" class="btn btn-primary mt-3">Manage Account</a>
            </div>
        </div>
        
        <!-- Featured Products Section -->
        <div class="featured-section mt-5">
            <h2>Featured Products</h2>
            <div class="grid mt-3">
                <?php
                // Fetch some featured products from database
                $featured_sql = "SELECT Product_id, Product_model, Product_price, Category_id 
                                FROM Product 
                                LIMIT 6";
                $featured_result = mysqli_query($connection, $featured_sql);
                
                if ($featured_result && mysqli_num_rows($featured_result) > 0):
                    while ($product = mysqli_fetch_assoc($featured_result)):
                ?>
                    <div class="product-card">
                        <div class="product-image">
                            📱
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['Product_model']); ?></h3>
                            <p class="price">RM <?php echo number_format($product['Product_price'], 2); ?></p>
                            <a href="product_detail.php?id=<?php echo $product['Product_id']; ?>" 
                               class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About ELEX Store</h3>
                <p>Your trusted source for quality electronics since 2024.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="returns.php">Returns Policy</a></li>
                    <li><a href="shipping.php">Shipping Info</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: support@elexstore.com</p>
                <p>Phone: +60 12-411-4008</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 ELEXStore. All rights reserved.</p>
        </div>
    </footer>

    <?php include __DIR__ . '/lib/_foot.php'; ?>
</body>
</html>

<style>
    /* Additional styles for index page */
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
    }
    
    .hero-content h1 {
        font-size: 48px;
        margin-bottom: 20px;
    }
    
    .hero-content p {
        font-size: 18px;
        margin-bottom: 30px;
        opacity: 0.9;
    }
    
    .hero-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    
    .hero-buttons .btn-secondary {
        background: transparent;
        border: 2px solid white;
        color: white;
    }
    
    .hero-buttons .btn-secondary:hover {
        background: white;
        color: #667eea;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .mt-5 {
        margin-top: 48px;
    }
    
    .mt-4 {
        margin-top: 32px;
    }
    
    .mt-3 {
        margin-top: 16px;
    }
    
    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .product-image {
        background: #f7fafc;
        padding: 40px;
        text-align: center;
        font-size: 48px;
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-info h3 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #2d3748;
    }
    
    .price {
        font-size: 20px;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .featured-section {
        margin-top: 60px;
        margin-bottom: 60px;
    }
    
    .featured-section h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #2d3748;
    }
    
    .footer {
        background: #2d3748;
        color: white;
        margin-top: 60px;
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }
    
    .footer-section h3 {
        margin-bottom: 15px;
        font-size: 18px;
    }
    
    .footer-section ul {
        list-style: none;
        padding: 0;
    }
    
    .footer-section ul li {
        margin-bottom: 10px;
    }
    
    .footer-section a {
        color: #cbd5e0;
        text-decoration: none;
    }
    
    .footer-section a:hover {
        color: white;
    }
    
    .footer-bottom {
        text-align: center;
        padding: 20px;
        border-top: 1px solid #4a5568;
        color: #cbd5e0;
    }
    
    @media (max-width: 768px) {
        .hero-content h1 {
            font-size: 32px;
        }
        
        .hero-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .hero-buttons .btn {
            width: 200px;
        }
    }
</style>

