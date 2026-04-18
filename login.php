<?php
// 1. PHP LOGIC FIRST (No HTML above this!)
require_once __DIR__ . '/config.php'; 

$error = "";

// Redirect if already logged in based on their role
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (strtolower($_SESSION['role']) === 'admin') {
        header("Location: " . BASE_URL . "/users/admins/index.php");
    } else {
        header("Location: " . BASE_URL . "/index.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

            if (isset($row['is_blocked']) && $row['is_blocked'] == 1) {
                $error = "This account is blocked. Please contact support.";
            }
            else if (hash('sha256', $pass) === $row['password'] || password_verify($pass, $row['password'])) {
                
                // Prevent regular users from logging in through the Admin tab
                if ($login_type === 'admin' && $user_role !== 'admin') {
                    $error = "Access denied. You do not have administrator privileges.";
                } else {
                    // Success! Set session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $user_role;
                    $_SESSION['user_id'] = $row['user_id'];

                    // REDIRECT BASED ON ROLE 
                    if ($user_role === 'admin') {
                        header("Location: " . BASE_URL . "/users/admins/index.php");
                    } else {
                        header("Location: " . BASE_URL . "/index.php");
                    }
                    exit;
                }
            } else {
                // Simplified failure message without tracking attempts
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
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
    .alert-error { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c6cb; }
</style>

<div class="login-container">
    <div class="tabs">
        <div class="tab active" onclick="switchTab('user')" id="tab-user">
            <i class="fas fa-users"></i> For Users
        </div>
        <div class="tab" onclick="switchTab('admin')" id="tab-admin">
            <i class="fas fa-sliders-h"></i> For Admins
        </div>
    </div>

    <div class="form-content">
        <h2 style="text-align:center; margin-bottom:20px; color: #333;" id="form-title">Webmail Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="login_type" id="login_type" value="user">

            <div class="input-group">
                <label>Login (email or username)</label>
                <input type="text" name="username" required placeholder="Enter email or username">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 14px;">
                <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; color: #666; font-weight: normal;">
                    <input type="checkbox"> Remember me
                </label>
                <a href="#" style="color: #0056b3; text-decoration: none;">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login" id="submit-btn">LOGIN</button>
        </form>
    </div>
</div>

<script>
    function switchTab(type) {
        document.getElementById('tab-user').classList.remove('active');
        document.getElementById('tab-admin').classList.remove('active');
        document.getElementById('tab-' + type).classList.add('active');

        document.getElementById('login_type').value = type;
        
        if (type === 'admin') {
            document.getElementById('form-title').innerText = 'Admin Portal Login';
            document.getElementById('submit-btn').style.background = '#0056b3';
            document.getElementById('submit-btn').innerText = 'SECURE ADMIN LOGIN';
        } else {
            document.getElementById('form-title').innerText = 'Webmail Login';
            document.getElementById('submit-btn').style.background = '#8bc34a';
            document.getElementById('submit-btn').innerText = 'LOGIN';
        }
    }

    window.onload = function() {
        <?php if(isset($login_type) && $login_type === 'admin'): ?>
            switchTab('admin');
        <?php endif; ?>
    }
</script>

<?php include 'lib/_foot.php'; ?>