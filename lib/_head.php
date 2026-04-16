<?php
// ========================================
// HEAD COMPONENT
// ========================================
// This file should be included at the top of every page
// It contains HTML head, navigation, and global styles
?>

<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Assignment-Web-Based');}
$session_photo = $_SESSION['profile_photo'] ?? 'uploads/profiles/default.png.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="ELEX Store - Your trusted source for quality electronics">
    <meta name="keywords" content="electronics, smartphones, tablets, gadgets">
    <meta name="author" content="ELEX Store Team">
    <meta name="theme-color" content="#667eea">
    
    <!-- Open Graph Tags for Social Media -->
    <meta property="og:title" content="<?php echo $_title ?? 'ELEX Store'; ?>">
    <meta property="og:description" content="Your trusted source for quality electronics">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ELEX Store">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/images/favicon.png">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
    
    <title><?php echo $_title ?? 'ELEX Store'; ?> - <?php echo ucfirst(basename($_SERVER['PHP_SELF'], '.php')); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="/Assignment-Web-Based/assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Custom JS -->
    <script src="js/app.js"></script>
    
    <style>
        /* ========================================
           GLOBAL VARIABLES & RESET
        ======================================== */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --primary-dark: #5a67d8;
            --secondary-color: #764ba2;
            --success-color: #38a169;
            --danger-color: #f56565;
            --danger-dark: #c53030;
            --warning-color: #ed8936;
            --info-color: #4299e1;
            --text-dark: #2d3748;
            --text-gray: #4a5568;
            --text-light: #718096;
            --border-color: #e2e8f0;
            --bg-light: #f7fafc;
            --bg-white: #ffffff;
            --bg-dark: #2d3748;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 20px;
            --transition-fast: 150ms ease;
            --transition-normal: 300ms ease;
            --transition-slow: 500ms ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* ========================================
           HEADER STYLES
        ======================================== */
        header {
            background: var(--bg-white);
            box-shadow: var(--shadow-sm);
            padding: 1rem 2rem;
        }
        
        header h1 a {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        /* ========================================
           NAVIGATION BAR STYLES
        ======================================== */
        .navbar {
            background: var(--bg-white);
            box-shadow: var(--shadow-sm);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all var(--transition-normal);
        }
        
        .navbar.scrolled {
            box-shadow: var(--shadow-md);
            padding: 0.75rem 2rem;
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .nav-brand a {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
        }
        
        .nav-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-link {
            color: var(--text-gray);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }
        
        .nav-link.active {
            background: var(--primary-gradient);
            color: white;
        }
        
        /* Legacy Navigation (from your original code) */
        nav {
            background: #2d3748;
            padding: 0.75rem 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }
        
        nav a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        /* User Menu Dropdown */
        .user-menu {
            position: relative;
            display: inline-block;
        }
        
        .user-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            font-weight: 500;
            color: var(--text-gray);
        }
        
        .user-btn:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: var(--bg-white);
            min-width: 220px;
            box-shadow: var(--shadow-lg);
            border-radius: var(--radius-md);
            z-index: 1001;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        
        .user-menu:hover .dropdown-content {
            display: block;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dropdown-content a,
        .dropdown-content button {
            color: var(--text-dark);
            padding: 0.75rem 1rem;
            text-decoration: none;
            display: block;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            transition: background var(--transition-fast);
            font-size: 14px;
        }
        
        .dropdown-content a:hover,
        .dropdown-content button:hover {
            background: var(--bg-light);
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.25rem 0;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-gray);
        }
        
        /* Breadcrumb Navigation */
        .breadcrumb {
            background: var(--bg-light);
            padding: 0.75rem 2rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .breadcrumb ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .breadcrumb li {
            display: inline-flex;
            align-items: center;
        }
        
        .breadcrumb li:not(:last-child):after {
            content: '/';
            margin-left: 0.5rem;
            color: var(--text-light);
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb .current {
            color: var(--text-light);
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Main Content */
        main {
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .page-header p {
            color: var(--text-light);
            margin-top: 0.5rem;
        }
        
        /* Flash Messages */
        .flash-message {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease-out;
        }
        
        .flash-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid var(--success-color);
        }
        
        .flash-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid var(--danger-color);
        }
        
        .flash-warning {
            background: #feebc8;
            color: #7b341e;
            border-left: 4px solid var(--warning-color);
        }
        
        .flash-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid var(--info-color);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--bg-white);
                flex-direction: column;
                padding: 1rem;
                box-shadow: var(--shadow-lg);
                border-top: 1px solid var(--border-color);
            }
            
            .nav-menu.show {
                display: flex;
            }
            
            .navbar {
                padding: 1rem;
            }
            
            nav {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--bg-light);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .mt-5 { margin-top: 2rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mb-5 { margin-bottom: 2rem; }
        
        .hide { display: none; }
        .show { display: block; }
        
        /* Additional inline styles for better compatibility */
        .cart-count {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            margin-left: 5px;
            display: none;
        }
        
        .form-group input.error,
        .form-group select.error,
        .form-group textarea.error {
            border-color: var(--danger-color);
        }
        
        .error-message {
            color: var(--danger-color);
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .flash-message {
            transition: opacity 0.3s ease;
        }
        
        /* Dashboard specific styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--bg-white);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-fast);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        /* Profile specific styles */
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Password reset specific styles */
        .password-strength-meter {
            margin-top: 0.5rem;
        }
        
        /* Verify email specific styles */
        .verification-container {
            text-align: center;
            padding: 3rem;
        }
    </style>
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar" id="navbar">
        <div class="nav-brand">
            <a href="/index.php">📱 ELEX Store</a>
        </div>
        
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="nav-menu" id="navMenu">
            <a href="/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="/order/ProductMember.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-mobile-alt"></i> Products
            </a>
            <a href="/about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i> About
            </a>
            <a href="/contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Contact
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Cart Icon -->
                <a href="/order/cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <span class="cart-count" id="cartCount">0</span>
                </a>
                
                <a href="/contact.php" class="nav-link">Contact</a>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                <a href="/users/admins/index.php" class="nav-link">
                    <i class="fas fa-users-cog"></i> Admin Panel
                </a>
            <?php endif; ?>

                <!-- User Dropdown Menu -->
                <div class="user-menu">
                    <button class="user-btn" id="userBtn">
                        <?php if (isset($_SESSION['profile_photo'])): ?>
                            <img src="<?= BASE_URL . '/' . $session_photo ?>" alt="Avatar" class="user-avatar" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <span><?= encode($_SESSION['username'] ?? 'Guest') ?></span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="/profile.php">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                        <a href="orders.php">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
    
                        <div class="dropdown-divider"></div>
                        
                        <form method="POST" action="/logout.php" style="margin: 0;">
                            <button type="submit" onclick="return confirm('Are you sure you want to logout?')">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/register.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Legacy Navigation Menu (Order Management) -->
    <nav>
        <a href="/order/ProductMember.php">Product</a>
        <a href="/order/ProductAdmin.php">Manage Product</a>
        <a href="/order/cart.php">🛒 Shopping Cart</a>
        <a href="/order/checkout.php">💳 CheckOut</a>
        <a href="/order/history.php">🕓 History</a>
        <a href="/order/orderlisting.php">📋 Listing</a>
    </nav>

    <!-- Breadcrumb Navigation (Optional - shows on inner pages) -->
    <?php 
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    $exclude_breadcrumb = ['index', 'login', 'register', 'forgot_password', 'reset_password', 'verify_email'];
    
    if (!in_array($current_page, $exclude_breadcrumb) && $current_page != 'index'):
    ?>
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <?php if ($current_page == 'dashboard'): ?>
                    <li class="current">Dashboard</li>
                <?php elseif ($current_page == 'profile'): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="current">My Profile</li>
                <?php elseif ($current_page == 'orders'): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="current">My Orders</li>
                <?php elseif ($current_page == 'products'): ?>
                    <li class="current">Products</li>
                <?php elseif ($current_page == 'cart'): ?>
                    <li class="current">Shopping Cart</li>
                <?php elseif ($current_page == 'checkout'): ?>
                    <li><a href="cart.php">Cart</a></li>
                    <li class="current">Checkout</li>
                <?php else: ?>
                    <li class="current"><?php echo ucfirst($current_page); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main>
        <div class="container">
            <!-- Flash Messages -->
            <?php 
            if (function_exists('getFlashMessage') && $flash = getFlashMessage()): ?>
                <div class="flash-message flash-<?php echo $flash['type']; ?>">
                    <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : ($flash['type'] == 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Page Header -->
            <?php if (isset($_title) && !in_array($current_page, ['index', 'login', 'register'])): ?>
            <div class="page-header">
                <h1><?php echo htmlspecialchars($_title); ?></h1>
                <?php if (isset($_subtitle)): ?>
                    <p><?php echo htmlspecialchars($_subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
                        
<script>
    // ========================================
    // NAVIGATION & UI INTERACTIONS
    // ========================================
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navMenu = document.getElementById('navMenu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (navMenu && navMenu.classList.contains('show')) {
            if (!navMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                navMenu.classList.remove('show');
            }
        }
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Set active navigation link based on current page
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath) {
            link.classList.add('active');
        } else if (currentPath === '' && href === 'index.php') {
            link.classList.add('active');
        }
    });
    
    // Cart count update function (if cart exists)
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const cartCount = document.getElementById('cartCount');
                if (cartCount && data.count > 0) {
                    cartCount.textContent = data.count;
                    cartCount.style.display = 'inline-block';
                } else if (cartCount) {
                    cartCount.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching cart count:', error));
    }
    
    // Call updateCartCount if on a page that shows cart
    if (document.getElementById('cartCount')) {
        updateCartCount();
    }
    
    // Form validation helper
    function validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;
        
        let isValid = true;
        const inputs = form.querySelectorAll('[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        
        return isValid;
    }
    
    // Auto-hide flash messages after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.flash-message').forEach(flash => {
            flash.style.opacity = '0';
            setTimeout(() => {
                flash.style.display = 'none';
            }, 300);
        });
    }, 5000);
</script>