<?php
// 1. PHP LOGIC FIRST (No HTML above this!)
require_once __DIR__ . '/config.php'; 

$error = "";
$success = "";
$reset_mode = false;
$reset_token = $_GET['token'] ?? $_POST['reset_token'] ?? '';

// Check if this is a password reset request
if (!empty($reset_token)) {
    $reset_mode = true;
    // Verify token
    $sql = "SELECT * FROM user WHERE reset_token = ? AND reset_expires > NOW()";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "s", $reset_token);
    mysqli_stmt_execute($stmt);
    $reset_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($reset_result) == 0) {
        $error = "Invalid or expired reset token. Please request a new password reset.";
        $reset_mode = false;
    } else {
        $reset_user = mysqli_fetch_assoc($reset_result);
    }
}

// Handle password reset submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $token = $_POST['reset_token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please enter both password fields.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Verify token again
        $sql = "SELECT * FROM user WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Hash the new password using password_hash (standard method)
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $update_sql = "UPDATE user SET password = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?";
            $update_stmt = mysqli_prepare($connection, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $row['user_id']);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Password has been reset successfully! You can now login with your new password.";
                $reset_mode = false;
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } else {
            $error = "Invalid or expired reset token.";
            $reset_mode = false;
        }
    }
}

// Handle forgot password request - WITHOUT EMAIL, shows link directly
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    $email = trim($_POST['reset_email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        // Check if email exists
        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Generate unique token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save token to database
            $update_sql = "UPDATE user SET reset_token = ?, reset_expires = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($connection, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssi", $token, $expires, $row['user_id']);
            
            if (mysqli_stmt_execute($update_stmt)) {
                // Display the reset link directly (no email sending)
                $reset_link = BASE_URL . "/login.php?token=" . $token;
                $success = "
                    <div style='text-align: left;'>
                        <strong>Password Reset Link Generated!</strong><br><br>
                        Click the link below to reset your password:<br><br>
                        <a href='$reset_link' style='display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 10px 0;'>Click Here to Reset Password</a><br><br>
                        <strong>Or copy this link:</strong><br>
                        <input type='text' value='$reset_link' style='width: 100%; padding: 8px; margin-top: 5px; font-size: 12px;' readonly onclick='this.select()'><br><br>
                        <small style='color:#666;'>⚠️ This link will expire in 1 hour.</small>
                    </div>
                ";
            } else {
                $error = "Failed to generate reset token. Please try again.";
            }
        } else {
            // Don't reveal if email exists or not for security
            $error = "No account found with that email address.";
        }
    }
}

// Redirect if already logged in based on their role
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !$reset_mode) {
    if (strtolower($_SESSION['role']) === 'admin') {
        header("Location: " . BASE_URL . "/users/admins/index.php");
    } else {
        header("Location: " . BASE_URL . "/index.php");
    }
    exit;
}

// Initialize login attempt tracking if not exists
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = 0;
}

// Check if account is locked
$current_time = time();
if ($_SESSION['lockout_time'] > 0 && $current_time < $_SESSION['lockout_time']) {
    $remaining_minutes = ceil(($_SESSION['lockout_time'] - $current_time) / 60);
    $error = "Too many failed login attempts. Please try again in " . $remaining_minutes . " minute(s).";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error) && !isset($_POST['forgot_password']) && !isset($_POST['reset_password'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $login_type = $_POST['login_type'] ?? 'user'; // Check which tab they logged in from

    if (empty($user) || empty($pass)) {
        $error = "Please enter both username and password.";
    } else {
        $sql = "SELECT * FROM user WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $user, $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
    // Force role to lowercase to prevent database collation errors
    $user_role = strtolower($row['role']);
    
    // Check password - FIXED VERSION
    $password_valid = false;
    
    if (isset($row['password']) && !empty($row['password'])) {
        $stored_password = $row['password'];
        
        // Check if it's a SHA256 hash (exactly 64 characters, hex format)
        if (strlen($stored_password) == 64 && ctype_xdigit($stored_password)) {
            // Compare using SHA256
            $password_valid = (hash('sha256', $pass) === $stored_password);
        } 
        // Otherwise try bcrypt (for password_hash)
        else {
            $password_valid = password_verify($pass, $stored_password);
        }
    }

    if (isset($row['is_blocked']) && $row['is_blocked'] == 1) {
        $error = "This account is blocked. Please contact support.";
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }
    else if ($password_valid) {
        // Rest of your success logic...
          
                
                // Prevent regular users from logging in through the Admin tab
                if ($login_type === 'admin' && $user_role !== 'admin') {
                    $error = "Access denied. You do not have administrator privileges.";
                    // Increment failed attempts for unauthorized admin access
                    $_SESSION['login_attempts']++;
                    if ($_SESSION['login_attempts'] >= 3) {
                        $_SESSION['lockout_time'] = time() + (3 * 60); // 3 minutes lockout
                        $error = "Too many failed login attempts. Please try again in 3 minutes.";
                    }
                } else {
                    // Success! Reset attempts and set session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $user_role;
                    $_SESSION['user_id'] = $row['user_id'];

                    $_temp_cart = $_SESSION['cart'] ?? [];
                    if(!empty($temp_cart)) {
                        $ab_cart = get_cart();
                        foreach($temp_cart as $id => $unit) {
                            $db_cart[$id] = $unit;
                        }
                        set_cart($db_cart);
                        unset($_SESSION['cart']);
                    }
                    
                    // Reset login attempts on successful login
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['lockout_time'] = 0;

                    // REDIRECT BASED ON ROLE 
                    if ($user_role === 'admin') {
                        header("Location: " . BASE_URL . "/users/admins/index.php");
                    } else {
                        header("Location: " . BASE_URL . "/index.php");
                    }
                    exit;
                }
            } else {
                // Invalid password - increment attempts
                $_SESSION['login_attempts']++;
                $remaining_attempts = 3 - $_SESSION['login_attempts'];
                
                if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['lockout_time'] = time() + (3 * 60); // 3 minutes lockout
                    $error = "Too many failed login attempts. Your account has been locked for 3 minutes.";
                } else {
                    $error = "Invalid password. You have " . $remaining_attempts . " attempt(s) remaining.";
                }
            }
        } else {
            // User not found - increment attempts
            $_SESSION['login_attempts']++;
            $remaining_attempts = 3 - $_SESSION['login_attempts'];
            
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time() + (3 * 60); // 3 minutes lockout
                $error = "Too many failed login attempts. Your account has been locked for 3 minutes.";
            } else {
                $error = "User not found. You have " . $remaining_attempts . " attempt(s) remaining.";
            }
        }
    }
}
include 'lib/_head.php'; 
?>

<style>
    .login-container { max-width: 450px; margin: 50px auto; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; }
    .tabs { display: flex; border-bottom: 2px solid #eee; background: #f8f9fa; }
    .tab { flex: 1; padding: 15px; text-align: center; cursor: pointer; font-weight: bold; color: #6c757d; border-bottom: 3px solid transparent; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .tab.active { color: #0056b3; border-bottom: 3px solid #0056b3; background: white; }
    .tab:hover:not(.active) { background: #e9ecef; }
    .form-content { padding: 30px; }
    .input-group { margin-bottom: 20px; }
    .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
    .input-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
    .btn-login { width: 100%; padding: 12px; background: #8bc34a; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
    .btn-login:hover { background: #7cb342; }
    .btn-login:disabled { background: #cccccc; cursor: not-allowed; }
    .btn-reset { background: #007bff; margin-top: 10px; }
    .btn-reset:hover { background: #0056b3; }
    .alert-error { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c6cb; }
    .alert-success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #c3e6cb; }
    .attempt-warning { color: #856404; background-color: #fff3cd; padding: 8px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #ffeeba; font-size: 14px; }
    .forgot-link { text-align: center; margin-top: 15px; }
    .forgot-link a { color: #007bff; text-decoration: none; font-size: 14px; }
    .forgot-link a:hover { text-decoration: underline; }
    .back-to-login { text-align: center; margin-top: 20px; }
    .password-requirements { font-size: 12px; color: #666; margin-top: 5px; }
    .reset-link-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; word-break: break-all; }
    .copy-btn { background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-top: 5px; }
    .copy-btn:hover { background: #218838; }
</style>

<div class="login-container">
    <?php if ($reset_mode): ?>
        <!-- Password Reset Form -->
        <div class="form-content">
            <h2 style="text-align:center; margin-bottom:20px; color: #333;">Reset Your Password</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <input type="hidden" name="reset_token" value="<?= htmlspecialchars($reset_token) ?>">
                <input type="hidden" name="reset_password" value="1">
                
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required placeholder="Enter new password" minlength="6">
                    <div class="password-requirements">Password must be at least 6 characters long</div>
                </div>
                
                <div class="input-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm new password">
                </div>
                
                <button type="submit" class="btn-login">RESET PASSWORD</button>
            </form>
            
            <div class="back-to-login">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
        
    <?php elseif (isset($_POST['forgot_password']) || (isset($_GET['action']) && $_GET['action'] == 'forgot')): ?>
        <!-- Forgot Password Form -->
        <div class="form-content">
            <h2 style="text-align:center; margin-bottom:20px; color: #333;">Forgot Password?</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (empty($success)): ?>
                <p style="text-align:center; margin-bottom:20px; color:#666;">Enter your email address to generate a password reset link.</p>
                
                <form method="POST" action="login.php">
                    <input type="hidden" name="forgot_password" value="1">
                    
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="reset_email" required placeholder="Enter your registered email">
                    </div>
                    
                    <button type="submit" class="btn-login">GENERATE RESET LINK</button>
                </form>
            <?php endif; ?>
            
            <div class="back-to-login">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Normal Login Form -->
        <div class="tabs">
            <div class="tab active" onclick="switchTab('user')" id="tab-user">
                <i class="fas fa-users"></i> For Members
            </div>
            <div class="tab" onclick="switchTab('admin')" id="tab-admin">
                <i class="fas fa-sliders-h"></i> For Admins
            </div>
        </div>

        <div class="form-content">
            <h2 style="text-align:center; margin-bottom:20px; color: #333;" id="form-title">Member Login</h2>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($_SESSION['login_attempts'] > 0 && $_SESSION['login_attempts'] < 3 && $_SESSION['lockout_time'] == 0): ?>
                <div class="attempt-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Login attempts remaining: <?= 3 - $_SESSION['login_attempts'] ?> / 3
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="login_type" id="login_type" value="user">

                <div class="input-group">
                    <label>Login (email or username)</label>
                    <input type="text" name="username" required placeholder="Enter email or username" 
                           <?= ($_SESSION['lockout_time'] > 0 && time() < $_SESSION['lockout_time']) ? 'disabled' : '' ?>>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password"
                           <?= ($_SESSION['lockout_time'] > 0 && time() < $_SESSION['lockout_time']) ? 'disabled' : '' ?>>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 14px;">
                    <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #666; font-weight: normal;">
                        <input type="checkbox" name="remember_me"> Remember me
                    </label>
                </div>

                <button type="submit" class="btn-login" id="submit-btn" 
                        <?= ($_SESSION['lockout_time'] > 0 && time() < $_SESSION['lockout_time']) ? 'disabled' : '' ?>>
                    LOGIN
                </button>
            </form>
            
            <div class="forgot-link">
                <a href="?action=forgot">Forgot Password?</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function switchTab(type) {
        const userTab = document.getElementById('tab-user');
        const adminTab = document.getElementById('tab-admin');
        
        if (userTab && adminTab) {
            userTab.classList.remove('active');
            adminTab.classList.remove('active');
            document.getElementById('tab-' + type).classList.add('active');
        }

        const loginTypeInput = document.getElementById('login_type');
        if (loginTypeInput) {
            loginTypeInput.value = type;
        }
        
        const formTitle = document.getElementById('form-title');
        const submitBtn = document.getElementById('submit-btn');
        
        if (formTitle && submitBtn) {
            if (type === 'admin') {
                formTitle.innerText = 'Admin Portal Login';
                submitBtn.style.background = '#0056b3';
                submitBtn.innerText = 'SECURE ADMIN LOGIN';
            } else {
                formTitle.innerText = 'Member Login';
                submitBtn.style.background = '#8bc34a';
                submitBtn.innerText = 'LOGIN';
            }
        }
    }
    
    // Auto-refresh page to check if lockout has expired (every 30 seconds)
    <?php if ($_SESSION['lockout_time'] > 0 && time() < $_SESSION['lockout_time']): ?>
        setTimeout(function() {
            location.reload();
        }, 30000);
    <?php endif; ?>

    window.onload = function() {
        <?php if(isset($login_type) && $login_type === 'admin'): ?>
            switchTab('admin');
        <?php endif; ?>
    }
    
    // Copy link function
    function copyLink(link) {
        navigator.clipboard.writeText(link).then(function() {
            alert('Reset link copied to clipboard!');
        });
    }
</script>

<?php include 'lib/_foot.php'; ?>