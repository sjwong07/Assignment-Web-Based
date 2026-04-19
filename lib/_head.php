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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/CSS/app.css">
    
    <!-- Custom JS -->
    <script src="js/app.js"></script>
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
                
                <a href="<?= BASE_URL ?>/users/admins/admin_management.php" class="nav-link">
                    <i class="fas fa-user-shield"></i> Manage Admins
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
                         <a href="<?= BASE_URL ?>/admins/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="/profile.php">
                            <i class="fas fa-user-circle"></i> My Profile
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