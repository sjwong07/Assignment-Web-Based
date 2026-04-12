<?php
session_start();

$max_attempts = 3;
$lockout_time = 300; // 5 minutes in seconds
$error = "";

// Database connection - UPDATED CREDENTIALS
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$dbname = 'store_db'; // MUST match phpMyAdmin

$connection = mysqli_connect($host, $username_db, $password_db, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. Redirect if already logged in
if (isset($_SESSION['loggedin'])) {
    
}

// 2. Login Logic (Database validation)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    // Check lockout status
    if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) {
        $remaining = ceil(($_SESSION['locked_until'] - time()) / 60);
        $error = "Locked! Try again in $remaining minute(s).";
    } else {
        // Query database for user
        $sql = "SELECT * FROM Customer WHERE username = ?";


        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Verify hashed password from database
            if (password_verify($pass, $row['password'])) {
                // Successful login
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['customer_id'] = $row['Customer_id']; // This ID is very critical

                // Clear any previous failed attempts
                unset($_SESSION['login_attempts'], $_SESSION['locked_until']);

                // Set remember me cookie if requested
                if ($remember) {
                    setcookie("remember_me_user", $user, time() + (30 * 24 * 60 * 60), "/");
                    // Also store user data for cookie auto-login
                    $_SESSION['registered_user'] = [
                        'username' => $row['username'],
                        'role' => $row['role'],
                        'customer_id' => $row['Customer_id']
                    ];
                }

                header("location: " . ($row['role'] === 'admin' ? "/order/ProductAdmin.php" : "/order/ProductMember.php"));
                exit;
            } else {
                // Invalid password
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['locked_until'] = time() + $lockout_time;
                    $error = "5 failed attempts. Locked for 1 minutes.";
                } else {
                    $left = $max_attempts - $_SESSION['login_attempts'];
                    $error = "Invalid password. $left attempts left.";
                }
            }
        } else {
            // No user found
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['locked_until'] = time() + $lockout_time;
                $error = "5 failed attempts. Locked for 1 minutes.";
            } else {
                $left = $max_attempts - $_SESSION['login_attempts'];
                $error = "No user found with that username. $left attempts left.";
            }
        }
    }
}

// 3. Remember Me Cookie Auto-login (after lockout check)
// 3. Remember Me Cookie Auto-login
if (isset($_COOKIE['remember_me_user']) && !isset($_SESSION['loggedin'])) {

    if (!isset($_SESSION['locked_until']) || time() >= $_SESSION['locked_until']) {

        $remember_user = $_COOKIE['remember_me_user'];

        $sql = "SELECT * FROM Customer WHERE username = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $remember_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['customer_id'] = $row['Customer_id'];

            header("location: /order/ProductMember.php");
            exit;
        } else {
            // Clear invalid cookie
            setcookie("remember_me_user", "", time() - 3600, "/");
        }
    }
}

            
            

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --p: #475569; --a: #3b82f6; --bg: #f8fafc; --err: #ef4444; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); width: 400px; }
        h2 { color: var(--p); text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--p); }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .error-box { background: #fee2e2; color: var(--err); padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; border: 1px solid var(--err); }
        .btn { width: 100%; background: var(--a); color: white; padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn:disabled { background: #94a3b8; cursor: not-allowed; }
        .footer-link { text-align: center; margin-top: 15px; font-size: 13px; }
        .footer-link a { color: var(--a); text-decoration: none; font-weight: bold; }
        .forgot { display: block; text-align: right; margin-top: -15px; margin-bottom: 15px; font-size: 12px; color: var(--a); text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Store Login</h2>
        <?php if ($error): ?> <div class="error-box"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div> <?php endif; ?>

        <form method="POST">
            <?php $is_locked = (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']); ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required <?php echo $is_locked ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required <?php echo $is_locked ? 'disabled' : ''; ?>>
            </div>
            <a href="resetpassword.php" class="forgot">Forgot Password?</a>
            <div class="form-group" style="display:flex; gap:8px; align-items:center;">
                <input type="checkbox" name="remember" id="rem">
                <label for="rem" style="margin:0; font-size:14px;">Remember Me</label>
            </div>
            <button type="submit" class="btn" <?php echo $is_locked ? 'disabled' : ''; ?>>Login</button>
        </form>
        <div class="footer-link">No account? <a href="register.php">Register now</a></div>
    </div>
</body>
</html>