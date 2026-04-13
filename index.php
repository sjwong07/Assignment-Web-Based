<?php
// 1. Start session and require base configuration
// Note: session_start() is usually inside _base.php, but called here if not.
require __DIR__ . '/lib/_base.php';
require __DIR__ . '/lib/_head.php';

// 2. Logic Check
$is_logged_in = isset($_SESSION['loggedin']);
$username = $_SESSION['username'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ELEX Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Essential styles from both versions */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; margin: 0; }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }
        
        .hero-content h1 { font-size: 48px; margin-bottom: 20px; }
        .hero-content p { font-size: 18px; margin-bottom: 30px; opacity: 0.9; }
        
        .hero-buttons { display: flex; gap: 15px; justify-content: center; }
        .hero-buttons .btn-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .mt-5 { margin-top: 48px; }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            text-align: center;
        }
        
        .product-card:hover { transform: translateY(-5px); }
        .product-image { background: #f7fafc; padding: 40px; font-size: 48px; }
        .product-info { padding: 20px; }
        .price { font-size: 20px; font-weight: bold; color: #667eea; margin-bottom: 15px; }

        .footer { background: #2d3748; color: white; margin-top: 60px; padding: 40px 0 20px; }
        .footer-content { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 30px; 
            max-width: 1200px; 
            margin: auto; 
            padding: 0 20px; 
        }
        .footer-section ul { list-style: none; padding: 0; }
        .footer-section a { color: #cbd5e0; text-decoration: none; }
        .footer-bottom { text-align: center; border-top: 1px solid #4a5568; margin-top: 30px; padding-top: 20px; color: #cbd5e0; }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 32px; }
            .hero-buttons { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php">📱 ELEX Store</a>
        </div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link">Home</a>
            <a href="products.php" class="nav-link">Products</a>
            <a href="about.php" class="nav-link">About</a>
            
            <?php if ($is_logged_in): ?>
                <div class="user-menu">
                    <button class="user-btn">
                        👤 <?php echo htmlspecialchars($username); ?> <span>▼</span>
                    </button>
                    <div class="dropdown-content">
                        <a href="dashboard.php">📊 Dashboard</a>
                        <a href="profile.php">👤 Profile</a>
                        <a href="orders.php">🛒 Orders</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" onclick="return confirm('Logout?')">🚪 Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-content">
            <h1>Welcome to the Electronics Hub</h1>
            <p>Your one-stop shop for high-quality components and the latest gadgets.</p>
            
            <div class="hero-buttons">
                <?php if (!$is_logged_in): ?>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="products.php" class="btn btn-secondary">Browse Products</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <a href="products.php" class="btn btn-secondary">Shop Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2>Hello, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Explore our wide range of tech essentials below.</p>
        
        <div class="featured-section mt-5">
            <h3>Featured Products</h3>
            <div class="grid mt-3">
                <?php
                // Fetch products from database
                $featured_sql = "SELECT Product_id, Product_model, Product_price FROM Product LIMIT 6";
                $featured_result = mysqli_query($connection, $featured_sql);
                
                if ($featured_result && mysqli_num_rows($featured_result) > 0):
                    while ($product = mysqli_fetch_assoc($featured_result)):
                ?>
                    <div class="product-card">
                        <div class="product-image">📱</div>
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
                    <p>No products found. Please <a href="/login.php">login</a> to see our full catalog.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About ELEX Store</h3>
                <p>Your trusted source for quality electronics since 2024.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
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
            <p>&copy; 2026 ELEXStore. All rights reserved.</p>
        </div>
    </footer>

    <?php include __DIR__ . '/lib/_foot.php'; ?>
</body>
</html>