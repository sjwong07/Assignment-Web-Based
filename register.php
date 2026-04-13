<?php
session_start();

// Database connection
$host = 'localhost';
$username_db = 'root';   // XAMPP default is 'root'
$password_db = '';       // XAMPP default is an empty string (no space)
$dbname = 'dbA'; // Change this to the name you created in phpMyAdmin

$connection = mysqli_connect($host, $username_db, $password_db, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = $_POST['role'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/[A-Za-z]/', $username) || !preg_match('/[0-9]/', $username)) {
        $error = "Username must contain both letters and numbers.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $phone)) {
        $error = "Phone number must be 7-15 digits.";
    } elseif (strlen($password) < 8 || strlen($password) > 12) {
        $error = "Password must be 8-12 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least 1 uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least 1 number.";
    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = "Password must contain at least 1 special character (e.g., !@#$%^&*).";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if username or email already exists
      $check_sql = "SELECT user_id FROM `user` WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username or email already exists. Please choose another.";
        } else {
            // Insert into database
            $sql = "INSERT INTO `user` (username, password, email, phone, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed_password, $email, $phone, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
                // Clear form fields on success
                $username = $email = $phone = "";
            } else {
                $error = "Registration failed: " . mysqli_error($connection);
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}

mysqli_close($connection);
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
        
        /* Password strength indicator */
        .strength-meter { height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 8px; overflow: hidden; }
        .strength-bar { height: 100%; width: 0%; transition: width 0.3s, background 0.3s; border-radius: 2px; }
        .strength-text { font-size: 10px; margin-top: 4px; text-align: right; color: var(--muted); }
    </style>
</head>
<body>
    <div class="box">
        <h2>Create Account</h2>
        <?php if ($error): ?> <div class="msg error-msg"><?php echo htmlspecialchars($error); ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="msg success-msg"><?php echo $success; ?></div> <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-group">
                <label>Account Type</label>
                <select name="role">
                    <option value="member">Member (Customer)</option>
                    <option value="admin">Admin (Staff)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" placeholder="e.g. Alex2024" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                <span class="hint"><i class="fa-solid fa-circle-info"></i> Must contain both letters and numbers.</span>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="phone" placeholder="7 to 15 digits" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="pass" maxlength="12" required>
                <i class="fa-solid fa-eye toggle-btn" onclick="toggle('pass', this)"></i>
                <span class="hint"><i class="fa-solid fa-shield"></i> 8-12 chars, 1 Uppercase, 1 Number, 1 Special Char.</span>
                <div class="strength-meter">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-text" id="strengthText"></div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="conf" maxlength="12" required>
                <i class="fa-solid fa-eye toggle-btn" onclick="toggle('conf', this)"></i>
            </div>

            <button type="submit" class="btn">Register Now</button>
        </form>
        <div class="footer-link">Already have an account? <a href="login.php">Login here</a></div>
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
        
        // Real-time password strength meter
        const passwordInput = document.getElementById('pass');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let criteria = [];
            
            if (password.length >= 8 && password.length <= 12) {
                strength++;
                criteria.push('✓ Length: 8-12 chars');
            } else if (password.length > 0) {
                criteria.push('✗ Length must be 8-12 chars');
            } else {
                criteria.push('Length: 8-12 chars');
            }
            
            if (/[A-Z]/.test(password)) {
                strength++;
                criteria.push('✓ Has uppercase');
            } else if (password.length > 0) {
                criteria.push('✗ Need uppercase letter');
            } else {
                criteria.push('Need uppercase letter');
            }
            
            if (/[0-9]/.test(password)) {
                strength++;
                criteria.push('✓ Has number');
            } else if (password.length > 0) {
                criteria.push('✗ Need number');
            } else {
                criteria.push('Need number');
            }
            
            if (/[\W_]/.test(password)) {
                strength++;
                criteria.push('✓ Has special char');
            } else if (password.length > 0) {
                criteria.push('✗ Need special character');
            } else {
                criteria.push('Need special character');
            }
            
            return { strength, criteria };
        }
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const result = checkPasswordStrength(password);
            
            let width = (result.strength / 4) * 100;
            let color = '#ef4444';
            let text = 'Weak';
            
            if (result.strength === 1) {
                color = '#ef4444';
                text = 'Weak';
            } else if (result.strength === 2) {
                color = '#f59e0b';
                text = 'Fair';
            } else if (result.strength === 3) {
                color = '#3b82f6';
                text = 'Good';
            } else if (result.strength === 4) {
                color = '#10b981';
                text = 'Strong';
            }
            
            if (password.length === 0) {
                width = 0;
                text = '';
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.background = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });
        
        // Client-side validation before submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('pass').value;
            const confirm = document.getElementById('conf').value;
            let errors = [];
            
            // Username validation
            if (!/[A-Za-z]/.test(username) || !/[0-9]/.test(username)) {
                errors.push('Username must contain both letters and numbers');
            }
            
            // Password validation
            if (password.length < 8 || password.length > 12) {
                errors.push('Password must be 8-12 characters long');
            }
            if (!/[A-Z]/.test(password)) {
                errors.push('Password must contain at least 1 uppercase letter');
            }
            if (!/[0-9]/.test(password)) {
                errors.push('Password must contain at least 1 number');
            }
            if (!/[\W_]/.test(password)) {
                errors.push('Password must contain at least 1 special character');
            }
            if (password !== confirm) {
                errors.push('Passwords do not match');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
            }
        });
    </script>
</body>
</html>