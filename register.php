<?php
require_once __DIR__ . '/config.php'; 

$error = "";
$success = "";
$admin_error = "";

// Secret admin creation key
$ADMIN_SECRET_KEY = "XAm87c";

// 1. CHECK IF ANY ADMIN EXISTS
$check_admin_sql = "SELECT user_id FROM `user` WHERE role = 'admin' LIMIT 1";
$check_admin_result = mysqli_query($connection, $check_admin_sql);
$admin_exists = mysqli_num_rows($check_admin_result) > 0;

// 2. ADMIN REGISTRATION LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_admin'])) {
    $admin_username = trim($_POST['admin_username']);
    $admin_email = trim($_POST['admin_email']);
    $admin_phone = trim($_POST['admin_phone']);
    $admin_password = $_POST['admin_password'];
    $admin_confirm = $_POST['admin_confirm_password'];
    $secret_key = $_POST['admin_secret'];
    
    if ($secret_key !== $ADMIN_SECRET_KEY) {
        $admin_error = "Invalid Special Key!";
    } elseif (empty($admin_username) || empty($admin_email) || empty($admin_phone) || empty($admin_password)) {
        $admin_error = "All admin fields are required.";
    } elseif ($admin_password !== $admin_confirm) {
        $admin_error = "Passwords do not match.";
    } else {
        // HASH THE PASSWORD
        $hashed_admin_password = password_hash($admin_password, PASSWORD_DEFAULT);
        
        // CHECK IF USERNAME OR EMAIL EXISTS
        $check_sql = "SELECT user_id FROM `user` WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $admin_username, $admin_email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $admin_error = "Admin username or email already exists.";
        } else {
            // INSERT INTO DATABASE
            $sql = "INSERT INTO `user` (username, password, email, phone, role, is_blocked) VALUES (?, ?, ?, ?, 'admin', 0)";
            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $admin_username, $hashed_admin_password, $admin_email, $admin_phone);
            
            if (mysqli_stmt_execute($stmt)) {
                // REDIRECT TO LOGIN AFTER SAVING
                header("Location: login.php?msg=admin_created");
                exit();
            } else {
                $admin_error = "Database Error: " . mysqli_error($connection);
            }
        }
    }
}

// 3. MEMBER REGISTRATION LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_member'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // HASH THE PASSWORD
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // CHECK IF EXISTING
        $check_sql = "SELECT user_id FROM `user` WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username or email already exists.";
        } else {
            // INSERT INTO DATABASE
            $sql = "INSERT INTO `user` (username, password, email, phone, role, is_blocked) VALUES (?, ?, ?, ?, 'member', 0)";
            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $email, $phone);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Database Error: " . mysqli_error($connection);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Store</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; position: relative; overflow-x: hidden; }
body::before { content: ''; position: absolute; width: 100%; height: 100%; background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%); pointer-events: none; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
.box { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); padding: 45px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 500px; animation: fadeInUp 0.6s ease-out; position: relative; z-index: 1; }
h2 { text-align: center; font-size: 32px; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 8px; }
.subtitle { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 30px; }
.form-group { margin-bottom: 20px; position: relative; }
label { font-size: 14px; font-weight: 600; color: #334155; display: block; margin-bottom: 8px; }
label i { margin-right: 8px; color: #667eea; }
input { width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; font-family: 'Inter', sans-serif; transition: all 0.3s ease; background: #f8fafc; }
input:focus { outline: none; border-color: #667eea; background: white; box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1); }
input:hover:not(:focus) { border-color: #cbd5e1; }
.toggle-btn { position: absolute; right: 16px; top: 44px; cursor: pointer; color: #94a3b8; transition: color 0.3s ease; font-size: 18px; }
.toggle-btn:hover { color: #667eea; }
.msg { padding: 14px 18px; border-radius: 12px; margin-bottom: 25px; text-align: center; font-size: 14px; font-weight: 500; animation: slideIn 0.4s ease-out; display: flex; align-items: center; justify-content: center; gap: 10px; }
.msg i { font-size: 18px; }
.error-msg { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626; border-left: 4px solid #dc2626; }
.success-msg { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #059669; border-left: 4px solid #059669; }
.success-msg a { color: #059669; font-weight: 700; text-decoration: none; border-bottom: 2px solid #059669; transition: all 0.3s ease; }
.success-msg a:hover { color: #047857; border-bottom-color: #047857; }
.btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 700; font-family: 'Inter', sans-serif; transition: all 0.3s ease; margin-top: 10px; position: relative; overflow: hidden; }
.btn::before { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; border-radius: 50%; background: rgba(255, 255, 255, 0.3); transform: translate(-50%, -50%); transition: width 0.6s, height 0.6s; }
.btn:hover::before { width: 300px; height: 300px; }
.btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4); }
.btn:active { transform: translateY(0); }
.login-link { text-align: center; margin-top: 25px; font-size: 14px; color: #64748b; }
.login-link a { color: #667eea; text-decoration: none; font-weight: 700; transition: all 0.3s ease; }
.login-link a:hover { color: #764ba2; text-decoration: underline; }
.admin-section { margin-top: 30px; border-top: 2px dashed #e2e8f0; padding-top: 25px; }
.admin-toggle { text-align: center; margin-bottom: 20px; }
.admin-toggle-btn { background: transparent; border: 2px solid #667eea; color: #667eea; padding: 8px 20px; border-radius: 30px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s ease; }
.admin-toggle-btn:hover { background: #667eea; color: white; }
.admin-form { display: none; animation: fadeInUp 0.4s ease-out; }
.admin-form.show { display: block; }
.admin-badge { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; margin-bottom: 15px; }
.admin-note { background: #fef3c7; color: #92400e; padding: 10px 15px; border-radius: 10px; font-size: 12px; margin-bottom: 20px; text-align: center; }
.warning-box { background: #fff3cd; color: #856404; padding: 10px; text-align: center; border-radius: 5px; margin-bottom: 15px; font-weight: bold; border: 1px solid #ffeeba; }
@media (max-width: 640px) { .box { padding: 30px 25px; } h2 { font-size: 28px; } }
</style>
</head>

<body>
<div class="box">
    <h2>Create Account</h2>
    <div class="subtitle">Join us today! 🎉</div>

    <?php if (!$admin_exists): ?>
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i> No Admin Found! Please create the master admin account below first.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="msg error-msg">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg success-msg">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <input type="hidden" name="register_member" value="1">
        
        <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>" placeholder="e.g., john123">
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="you@example.com">
        </div>

        <div class="form-group">
            <label><i class="fas fa-phone"></i> Phone</label>
            <input type="text" name="phone" required value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="1234567890">
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" id="pass" required>
            <i class="fa-solid fa-eye toggle-btn" onclick="toggle('pass', this)"></i>
        </div>

        <div class="form-group">
            <label><i class="fas fa-check-circle"></i> Confirm Password</label>
            <input type="password" name="confirm_password" id="conf" required>
            <i class="fa-solid fa-eye toggle-btn" onclick="toggle('conf', this)"></i>
            <small style="font-size: 11px; color: #94a3b8; margin-top: 5px; display: block;" id="matchMsg"></small>
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-user-plus"></i> Register as Member
        </button>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </form>

    <div class="admin-section">
        <div class="admin-toggle">
            <button type="button" class="admin-toggle-btn" onclick="toggleAdminForm()">
                <i class="fas fa-user-shield"></i> Admin Registration
            </button>
        </div>
        
        <div id="adminForm" class="admin-form <?php echo (!$admin_exists || $admin_error) ? 'show' : ''; ?>">
            <div style="text-align: center;">
                <span class="admin-badge"><i class="fas fa-crown"></i> Administrator Access</span>
            </div>
            <div class="admin-note">
                <i class="fas fa-lock"></i> Authorized administrators only. Enter the valid special key to proceed.
            </div>
            
            <?php if (!empty($admin_error)): ?>
                <div class="msg error-msg" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($admin_error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user-shield"></i> Admin Username</label>
                    <input type="text" name="admin_username" required placeholder="Enter admin username">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Admin Email</label>
                    <input type="email" name="admin_email" required placeholder="admin@example.com">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Admin Phone</label>
                    <input type="text" name="admin_phone" required placeholder="Enter phone number">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Admin Password</label>
                    <input type="password" name="admin_password" id="adminPass" required>
                    <i class="fa-solid fa-eye toggle-btn" onclick="toggle('adminPass', this)"></i>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirm Password</label>
                    <input type="password" name="admin_confirm_password" id="adminConf" required>
                    <i class="fa-solid fa-eye toggle-btn" onclick="toggle('adminConf', this)"></i>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Special Key</label>
                    <input type="password" name="admin_secret" required placeholder="Enter special key" value="<?php echo (!$admin_exists) ? 'XAm87c' : ''; ?>">
                </div>
                
                <button type="submit" name="create_admin" class="btn" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <i class="fas fa-user-shield"></i> Create Admin Account
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Password Visibility Toggle
function toggle(id, icon) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Show/Hide Admin Form
function toggleAdminForm() {
    const adminForm = document.getElementById('adminForm');
    adminForm.classList.toggle('show');
}

// Simple check just to visually warn if passwords don't match
const passwordInput = document.getElementById('pass');
const confirmInput = document.getElementById('conf');
const matchMsg = document.getElementById('matchMsg');

function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    
    if (confirm.length === 0) {
        matchMsg.innerHTML = '';
    } else if (password === confirm) {
        matchMsg.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match!';
        matchMsg.style.color = '#10b981';
    } else {
        matchMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match';
        matchMsg.style.color = '#ef4444';
    }
}

if (passwordInput && confirmInput) {
    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmInput.addEventListener('input', checkPasswordMatch);
}

// Prevent submitting if passwords don't match
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            alert('Your passwords do not match!');
        }
    });
}
</script>

</body>
</html>