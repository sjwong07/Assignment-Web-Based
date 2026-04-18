<?php
require __DIR__ . '/lib/_base.php';
require __DIR__ . '/lib/_head.php';


// 2. Auto-login check via remember me token (if implemented)
// This would typically be placed before the main logic
if (!isset($_SESSION['loggedin']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    // Validate token against database (simplified example)
    $sql = "SELECT u.* FROM users u 
            INNER JOIN user_tokens ut ON u.user_id = ut.user_id 
            WHERE ut.token = ? AND ut.expires_at > NOW()";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['user_id'] = $row['user_id'];

        // Redirect based on role even in Auto-Login
        if ($row['role'] === 'admin') {
            header("location: index.php");
        } else {
            header("location: index.php");
        }
        exit;
    }
}

// 3. Logic Check
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
        
        .product-card  {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            text-align: center;
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
}

.product-image {
    width: 100%;
    height: 180px; /
    overflow: hidden;
    padding: 15px; 
    box-sizing: border-box; 
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: contain; 
}

.product-info {
    padding: 20px;
    text-align: center;
}
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

        /* Navbar styles */
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand a {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: #667eea;
        }
        .nav-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .nav-link {
            text-decoration: none;
            color: #4a5568;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-primary {
            background: #667eea;
            color: white;
            border: none;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        /* Click-to-toggle dropdown styles - Updated */
        .user-menu {
            position: relative;
            display: inline-block;
        }
        .user-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .user-btn:hover {
            background: #f0f0f0;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background: white;
            min-width: 180px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            border-radius: 8px;
            z-index: 1000;
            overflow: hidden;
        }
        .dropdown-content.show {
            display: block;
        }
        .dropdown-content a {
            color: #4a5568;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.2s;
        }
        .dropdown-content a:hover {
            background: #f7fafc;
        }
        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 4px 0;
        }

        /* Close dropdown when clicking outside */
        .dropdown-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
        }
        .dropdown-overlay.show {
            display: block;
        }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 32px; }
            .hero-buttons { flex-direction: column; align-items: center; }
            .nav-menu { gap: 0.75rem; }

            
        }
    </style>
</head>
<body>

    <div class="hero-section">
        <div class="hero-content">
            <h1>Welcome to the ELEX Store</h1>
            <p>Your one-stop shop for high-quality components and the latest gadgets.</p>
            
            <div class="hero-buttons">
                <?php if (!$is_logged_in): ?>
                    <a href="register.php" class="btn btn-primary" style="font-size: 20px; margin-top: 20px;">Get Started</a>
                    <a href="/order/ProductMember.php" class="btn btn-secondary">Browse Products</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <a href="/order/ProductMember.php" class="btn btn-secondary">Shop Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    

    <div class="container mt-5">
        <h2>Hello, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Explore our wide range of tech essentials below.</p>
        
        <div class="featured-section mt-5">
            <h3>Top 6 Best Selling Products!!!</h3>
            <div class="grid mt-3">
                <?php
                $featured_sql = "SELECT Product_id, Product_model, Product_price, Product_photo FROM Product LIMIT 6";
                $featured_result = mysqli_query($connection, $featured_sql);
                
                if ($featured_result && mysqli_num_rows($featured_result) > 0):
                    while ($product = mysqli_fetch_assoc($featured_result)):
                ?>
                    <div class="product-card">

    <!-- IMAGE -->
    <div class="product-image">
        <?php 
        $image_path = __DIR__ . "/images/" . $product['Product_photo'];

        if (!empty($product['Product_photo']) && file_exists($image_path)): 
        ?>
            <img class="product-img" 
                 src="images/<?= htmlspecialchars($product['Product_photo']) ?>" 
                 alt="<?= htmlspecialchars($product['Product_model']) ?>">
        <?php else: ?>
            <div class="image-placeholder">No Image</div>
        <?php endif; ?>
    </div>

    <!-- INFO -->
    <div class="product-info">
        <h3><?= htmlspecialchars($product['Product_model']); ?></h3>
        <p class="price">RM <?= number_format($product['Product_price'], 2); ?></p>
        <a href="product_detail.php?id=<?= $product['Product_id']; ?>" 
           class="btn btn-sm btn-primary">View Details</a>
    </div>

</div> <?php endwhile;
                else:
                ?>
                    <p>No products found. Please <a href="/login.php">login</a> to see our full catalog.</p>
                <?php endif; ?>
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

    <script>
        // Click-to-toggle dropdown functionality
        (function() {
            const userBtn = document.getElementById('userBtn');
            const dropdownContent = document.getElementById('dropdownContent');
            const overlay = document.getElementById('dropdownOverlay');
            
            if (userBtn && dropdownContent) {
                // Toggle dropdown when clicking the user button
                userBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    dropdownContent.classList.toggle('show');
                    if (overlay) {
                        overlay.classList.toggle('show');
                    }
                });
                
                // Close dropdown when clicking on overlay
                if (overlay) {
                    overlay.addEventListener('click', function() {
                        dropdownContent.classList.remove('show');
                        overlay.classList.remove('show');
                    });
                }
                
                // Close dropdown when pressing Escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        dropdownContent.classList.remove('show');
                        if (overlay) {
                            overlay.classList.remove('show');
                        }
                    }
                });
            }
        })();
    </script>
</body>
</html>





