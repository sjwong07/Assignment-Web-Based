<?php
session_start();

// 1. Protection: If not logged in, boot them to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: /login.php");
    exit;
}

/**
 * 2. Safe Data Retrieval 
 * We check if the specific keys (email/phone) exist. 
 * If they don't, we provide a default empty string or a placeholder.
 */
$sessionUser = $_SESSION['registered_user'] ?? [];

$userData = [
    'username' => $sessionUser['username'] ?? ($_SESSION['username'] ?? 'User'),
    'email'    => $sessionUser['email'] ?? '', // Defaults to empty if not found
    'phone'    => $sessionUser['phone'] ?? ''  // Defaults to empty if not found
];

$error = "";
$success = "";

// 3. Handle Profile Updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Update Personal Info
    if (isset($_POST['update_profile'])) {
        $new_email = trim($_POST['email']);
        $new_phone = trim($_POST['phone']);

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (!preg_match('/^[0-9]{7,15}$/', str_replace(['+', '-', ' '], '', $new_phone))) {
            $error = "Please enter a valid phone number.";
        } else {
            // Update Session Data and the current $userData variable
            $_SESSION['registered_user']['email'] = $new_email;
            $_SESSION['registered_user']['phone'] = $new_phone;
            $userData['email'] = $new_email;
            $userData['phone'] = $new_phone;
            $success = "Profile updated successfully!";
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass     = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (
            strlen($new_pass) < 8 || strlen($new_pass) > 12 || 
            !preg_match('/[A-Z]/', $new_pass) ||   
            !preg_match('/[0-9]/', $new_pass) ||   
            !preg_match('/[\W]/', $new_pass)
        ) {
            $error = "New password must be 8-12 chars with Uppercase, Number, and Special Char.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "New passwords do not match.";
        } else {
            $_SESSION['registered_user']['password'] = password_hash($new_pass, PASSWORD_DEFAULT);
            $success = "Password changed successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Account | Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { 
            --primary-color: #475569; --accent-color: #3b82f6; --bg-light: #f8fafc; 
            --error-red: #ef4444; --success-green: #10b981; --text-muted: #64748b;
        }

        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg-light); margin: 0; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; display: flex; gap: 30px; flex-wrap: wrap; }
        
        .profile-card { 
            background: white; padding: 30px; border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); flex: 1; min-width: 300px; 
        }

        h2 { color: var(--primary-color); margin-top: 0; border-bottom: 2px solid var(--bg-light); padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; position: relative; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--primary-color); }
        
        input { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; 
            box-sizing: border-box; font-size: 15px;
        }

        .toggle-password { position: absolute; right: 12px; top: 38px; cursor: pointer; color: var(--text-muted); }

        .btn { 
            padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; 
            cursor: pointer; transition: 0.3s; width: 100%;
        }
        .btn-primary { background: var(--accent-color); color: white; }
        .btn-primary:hover { background: #2563eb; }
        
        .msg { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; border: 1px solid; }
        .error { background: #fee2e2; color: var(--error-red); border-color: var(--error-red); }
        .success { background: #d1fae5; color: var(--success-green); border-color: var(--success-green); }

        .nav-links { margin-top: 20px; text-align: center; width: 100%; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 600; margin: 0 10px; }
        .nav-links a:hover { color: var(--accent-color); }
    </style>
</head>
<body>

<div class="container">
    <div style="width: 100%;">
        <?php if ($error): ?> <div class="msg error"><?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="msg success"><?php echo $success; ?></div> <?php endif; ?>
    </div>

    <div class="profile-card">
        <h2><i class="fa-solid fa-user-gear"></i> Profile Info</h2>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled style="background:#f1f5f9;">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Info</button>
        </form>
    </div>

    <div class="profile-card">
        <h2><i class="fa-solid fa-shield-halved"></i> Security</h2>
        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" id="curr_pass" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('curr_pass', this)"></i>
            </div>
            <div class="form-group">
                <label>New Password (8-12 chars)</label>
                <input type="password" name="new_password" id="new_pass" maxlength="12" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('new_pass', this)"></i>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="conf_pass" maxlength="12" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('conf_pass', this)"></i>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary" style="background: var(--primary-color);">Change Password</button>
        </form>
    </div>

    <div class="nav-links">
        <a href="product.php"><i class="fa-solid fa-house"></i> Home</a> |
        <a href="logout.php" style="color:var(--error-red);"><i class="fa-solid fa-power-off"></i> Logout</a>
    </div>
</div>

<script>
    function togglePass(id, icon) {
        const el = document.getElementById(id);
        if (el.type === "password") {
            el.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            el.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

</body>
</html>