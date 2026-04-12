<?php
session_start();
$error = ""; $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = $_POST['role']; 
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/[A-Za-z]/', $username) || !preg_match('/[0-9]/', $username)) {
        $error = "Username must contain letters and numbers.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8 || strlen($password) > 12 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
        $error = "Password does not meet the security requirements.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $_SESSION['registered_user'] = [
            'username' => $username,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => $role, 
            'password' => $hashed_password
        ];
        $success = "Account created as " . ucfirst($role) . "! <a href='/login.php'>Login here</a>";
        $username = $email = $phone = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --p: #475569; --a: #3b82f6; --bg: #f8fafc; --err: #ef4444; --succ: #10b981; --muted: #64748b; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        h2 { color: var(--p); text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; position: relative; }
        label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        
        /* Requirement Hint Styling */
        .hint { font-size: 11px; color: var(--muted); margin-top: 4px; display: block; }
        .hint i { margin-right: 4px; color: var(--a); }

        /* Show/Hide Toggle */
        .toggle-btn { position: absolute; right: 12px; top: 38px; cursor: pointer; color: var(--muted); }

        .msg { padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 13px; text-align: center; border: 1px solid; }
        .error-msg { background: #fee2e2; color: var(--err); border-color: var(--err); }
        .success-msg { background: #d1fae5; color: var(--succ); border-color: var(--succ); }
        .btn { width: 100%; padding: 12px; background: var(--p); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .footer-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .footer-link a { color: var(--a); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Create Account</h2>
        <?php if ($error): ?> <div class="msg error-msg"><?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="msg success-msg"><?php echo $success; ?></div> <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Account Type</label>
                <select name="role">
                    <option value="member">Member (Customer)</option>
                    <option value="admin">Admin (Staff)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="e.g. Alex2024" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                <span class="hint"><i class="fa-solid fa-circle-info"></i> Must contain both letters and numbers.</span>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" placeholder="7 to 15 digits" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="pass" maxlength="12" required>
                <i class="fa-solid fa-eye toggle-btn" onclick="toggle('pass', this)"></i>
                <span class="hint"><i class="fa-solid fa-shield"></i> 8-12 chars, 1 Uppercase, 1 Number, 1 Special Char.</span>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="conf" maxlength="12" required>
                <i class="fa-solid fa-eye toggle-btn" onclick="toggle('conf', this)"></i>
            </div>

            <button type="submit" class="btn">Register Now</button>
        </form>
        <div class="footer-link">Already have an account? <a href="/login.php">Login here</a></div>
    </div>

    <script>
        function toggle(id, icon) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>

