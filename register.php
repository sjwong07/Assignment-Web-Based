<?php
session_start();

// Database connection
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$dbname = 'dbA';

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
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // ✅ FORCE ROLE (NO ADMIN ALLOWED)
    $role = 'member';

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
        $error = "Password must contain at least 1 special character.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check existing user
        $check_sql = "SELECT user_id FROM `user` WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username or email already exists.";
        } else {
            // Insert user
            $sql = "INSERT INTO `user` (username, password, email, phone, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed_password, $email, $phone, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
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
:root { --p: #116beb; --a: #3b82f6; --bg: #f8fafc; --err: #ef4444; --succ: #10b981; --muted: #64748b; }
body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
.box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
h2 { text-align: center; }
.form-group { margin-bottom: 15px; position: relative; }
label { font-size: 14px; font-weight: 600; }
input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }

.toggle-btn { position: absolute; right: 12px; top: 38px; cursor: pointer; }

.msg { padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
.error-msg { background: #fee2e2; color: var(--err); }
.success-msg { background: #d1fae5; color: var(--succ); }

.btn { width: 100%; padding: 12px; background: var(--p); color: white; border: none; border-radius: 6px; cursor: pointer; }
</style>
</head>

<body>
<div class="box">
<h2>Create Account</h2>

<?php if ($error): ?>
<div class="msg error-msg"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="msg success-msg"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST">
    
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
    </div>

    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" required value="<?php echo htmlspecialchars($phone ?? ''); ?>">
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="pass" required>
        <i class="fa-solid fa-eye toggle-btn" onclick="toggle('pass', this)"></i>
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="conf" required>
        <i class="fa-solid fa-eye toggle-btn" onclick="toggle('conf', this)"></i>
    </div>

    <button type="submit" class="btn">Register</button>
</form>

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
