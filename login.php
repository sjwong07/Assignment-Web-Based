<?php
session_start();

$max_attempts = 3;
$lockout_time = 300; // 5 minutes
$error = "";

// Database connection
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$dbname = 'dbA';

$connection = mysqli_connect($host, $username_db, $password_db, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Redirect if already logged in
if (isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

//////////////////////////////////////////////////////
// 🔐 AUTO LOGIN USING REMEMBER TOKEN
//////////////////////////////////////////////////////
if (isset($_COOKIE['remember_token']) && !isset($_SESSION['loggedin'])) {

    if (!isset($_SESSION['locked_until']) || time() >= $_SESSION['locked_until']) {

        $token = $_COOKIE['remember_token'];

        $sql = "SELECT * FROM user WHERE remember_token = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['user_id'] = $row['user_id'];

            header("location: index.php");
            exit;
        } else {
            setcookie("remember_token", "", time() - 3600, "/");
        }
    }
}

//////////////////////////////////////////////////////
// 🔑 LOGIN PROCESS
//////////////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    // Lockout check
    if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) {
        $remaining = ceil(($_SESSION['locked_until'] - time()) / 60);
        $error = "Locked! Try again in $remaining minute(s).";
    } else {

        $sql = "SELECT * FROM user WHERE username = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            if (password_verify($pass, $row['password'])) {

                // SUCCESS LOGIN
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['user_id'];

                // Reset attempts
                unset($_SESSION['login_attempts'], $_SESSION['locked_until']);

                //////////////////////////////////////////////////////
                // ✅ REMEMBER ME (SECURE TOKEN)
                //////////////////////////////////////////////////////
                if ($remember) {
                    $token = bin2hex(random_bytes(32));

                    $update = "UPDATE user SET remember_token = ? WHERE user_id = ?";
                    $stmt2 = mysqli_prepare($connection, $update);
                    mysqli_stmt_bind_param($stmt2, "si", $token, $row['user_id']);
                    mysqli_stmt_execute($stmt2);

                    setcookie("remember_token", $token, time() + (30 * 24 * 60 * 60), "/", "", false, true);
                }

                // Redirect by role
                if ($row['role'] === 'admin') {
    header("location: ./admin/dashboard.php");
} else {
    if ($row['role'] === 'admin') {
        header("location: ./admin/dashboard.php");
    } else {
        header("location: index.php");
    }
    exit;
}
                }
                exit;

            } else {
                // WRONG PASSWORD
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['locked_until'] = time() + $lockout_time;
                    $error = "3 failed attempts. Locked for 5 minutes.";
                } else {
                    $left = $max_attempts - $_SESSION['login_attempts'];
                    $error = "Invalid password. $left attempts left.";
                }
            }

        
            // USER NOT FOUND
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['locked_until'] = time() + $lockout_time;
                $error = "3 failed attempts. Locked for 5 minutes.";
            } else {
                $left = $max_attempts - $_SESSION['login_attempts'];
                $error = "User not found. $left attempts left.";
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
:root { --p:#475569; --a:#3b82f6; --bg:#f8fafc; --err:#ef4444; }
body { font-family:'Segoe UI'; background:var(--bg); display:flex; justify-content:center; align-items:center; height:100vh; margin:0;}
.card { background:white; padding:40px; border-radius:12px; box-shadow:0 10px 15px rgba(0,0,0,0.1); width:400px;}
h2 { text-align:center; margin-bottom:25px;}
.form-group { margin-bottom:20px;}
input { width:100%; padding:12px; border:1px solid #ccc; border-radius:6px;}
.error-box { background:#fee2e2; color:var(--err); padding:12px; border-radius:6px; margin-bottom:20px;}
.btn { width:100%; background:var(--a); color:white; padding:12px; border:none; border-radius:6px; cursor:pointer;}
.btn:disabled { background:#94a3b8;}
</style>
</head>

<body>
<div class="card">
<h2>Store Login</h2>

<?php if ($error): ?>
<div class="error-box"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
<?php $is_locked = (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']); ?>

<div class="form-group">
<input type="text" name="username" placeholder="Username" required <?php echo $is_locked ? 'disabled' : ''; ?>>
</div>

<div class="form-group">
<input type="password" name="password" placeholder="Password" required <?php echo $is_locked ? 'disabled' : ''; ?>>
</div>

<div class="form-group">
<input type="checkbox" name="remember"> Remember Me
</div>

<button class="btn" <?php echo $is_locked ? 'disabled' : ''; ?>>Login</button>

</form>
<div style="text-align:center; margin-top:15px; font-size:14px;">
    <p>Don't have an account?
        <a href="register.php" style="color:#3b82f6; font-weight:bold; text-decoration:none;">
            Register here
        </a>
    </p>
</div>

</form>

</div>
</body>
</html>