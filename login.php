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

// Lockout configuration
$max_attempts = 3;
$lockout_time = 180; // 3 minutes (180 seconds)

$error = "";
$remaining_locked_minutes = 0;

// Redirect if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header("location: ./admin/dashboard.php");
    } else {
        header("location: index.php");
    }
    exit;
}

// Check if account is locked
$is_locked = false;
if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) {
    $is_locked = true;
    $remaining_seconds = $_SESSION['locked_until'] - time();
    $remaining_locked_minutes = ceil($remaining_seconds / 60);
}

//////////////////////////////////////////////////////
// 🔑 LOGIN PROCESS
//////////////////////////////////////////////////////
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_locked) {

    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (empty($user) || empty($pass)) {
        $error = "Please enter both username and password.";
    } else {
        // Prepare statement to prevent SQL injection
        $sql = "SELECT * FROM user WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $user, $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            // Verify password
            if (password_verify($pass, $row['password'])) {

                // SUCCESSFUL LOGIN
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['email'] = $row['email'];

                // Reset failed attempts on successful login
                unset($_SESSION['login_attempts']);
                unset($_SESSION['locked_until']);

                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header("location: ./admin/dashboard.php");
                } else {
                    header("location: index.php");
                }
                exit;

            } else {
                // WRONG PASSWORD
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $attempts_left = $max_attempts - $_SESSION['login_attempts'];

                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['locked_until'] = time() + $lockout_time;
                    $error = "Too many failed attempts! Account is locked for 3 minutes.";
                    $is_locked = true;
                    $remaining_locked_minutes = 3;
                } else {
                    $error = "Invalid password. " . $attempts_left . " attempt(s) remaining.";
                }
            }

        } else {
            // USER NOT FOUND - Count as an attempt too
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $attempts_left = $max_attempts - $_SESSION['login_attempts'];

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['locked_until'] = time() + $lockout_time;
                $error = "Too many failed attempts! Account is locked for 3 minutes.";
                $is_locked = true;
                $remaining_locked_minutes = 3;
            } else {
                $error = "Username/email not found. " . $attempts_left . " attempt(s) remaining.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Store</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
:root { 
    --primary: #116beb; 
    --primary-dark: #0e56c4;
    --bg: #f8fafc; 
    --err: #ef4444; 
    --warning: #f59e0b;
    --muted: #64748b; 
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex; 
    justify-content: center; 
    align-items: center; 
    min-height: 100vh; 
    margin: 0; 
    padding: 20px;
}

.card { 
    background: white; 
    padding: 40px; 
    border-radius: 16px; 
    box-shadow: 0 20px 35px rgba(0,0,0,0.2); 
    width: 100%; 
    max-width: 420px;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

h2 { 
    text-align: center; 
    margin-bottom: 8px;
    color: #1e293b;
    font-size: 28px;
}

.subtitle {
    text-align: center;
    color: var(--muted);
    margin-bottom: 30px;
    font-size: 14px;
}

.form-group { 
    margin-bottom: 22px;
    position: relative; 
}

label { 
    font-size: 14px; 
    font-weight: 600; 
    display: block;
    margin-bottom: 8px;
    color: #334155;
}

input { 
    width: 100%; 
    padding: 12px 15px; 
    border: 2px solid #e2e8f0; 
    border-radius: 10px; 
    font-size: 15px;
    transition: all 0.3s ease;
    outline: none;
}

input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(17, 107, 235, 0.1);
}

input:disabled {
    background-color: #f1f5f9;
    cursor: not-allowed;
}

.toggle-btn { 
    position: absolute; 
    right: 15px; 
    top: 42px; 
    cursor: pointer; 
    color: var(--muted);
    transition: color 0.3s;
}

.toggle-btn:hover {
    color: var(--primary);
}

.error-box { 
    background: #fee2e2; 
    color: var(--err); 
    padding: 12px 15px; 
    border-radius: 10px; 
    margin-bottom: 20px;
    font-size: 14px;
    border-left: 4px solid var(--err);
    display: flex;
    align-items: center;
    gap: 10px;
}

.error-box i {
    font-size: 18px;
}

.warning-box {
    background: #fef3c7;
    color: var(--warning);
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    border-left: 4px solid var(--warning);
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn { 
    width: 100%; 
    background: var(--primary); 
    color: white; 
    padding: 14px; 
    border: none; 
    border-radius: 10px; 
    cursor: pointer; 
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover:not(:disabled) { 
    background: var(--primary-dark); 
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(17, 107, 235, 0.3);
}

.btn:disabled { 
    background: #94a3b8; 
    cursor: not-allowed;
    transform: none;
}

.register-link {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: var(--muted);
}

.register-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.register-link a:hover {
    text-decoration: underline;
}

.attempts-info {
    font-size: 12px;
    color: var(--muted);
    text-align: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e2e8f0;
}
</style>
</head>

<body>
<div class="card">
    <h2>Welcome Back</h2>
    <div class="subtitle">Sign in to your account</div>

    <?php if ($error): ?>
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($is_locked): ?>
        <div class="warning-box">
            <i class="fas fa-lock"></i>
            <span>Account locked! Please try again in <strong><?php echo $remaining_locked_minutes; ?></strong> minute(s).</span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Username or Email</label>
            <input type="text" name="username" placeholder="Enter your username or email" 
                   required <?php echo $is_locked ? 'disabled' : ''; ?> 
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" 
                   required <?php echo $is_locked ? 'disabled' : ''; ?>>
            <i class="fa-solid fa-eye toggle-btn" onclick="togglePassword('password', this)"></i>
        </div>

        <button type="submit" class="btn" <?php echo $is_locked ? 'disabled' : ''; ?>>
            <?php echo $is_locked ? 'Account Locked' : 'Sign In'; ?>
        </button>

        <div class="attempts-info">
            <i class="fas fa-shield-alt"></i> Maximum 3 attempts - 3 minute lockout
        </div>
    </form>

    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

<script>
function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
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

// Display remaining lockout time if locked
<?php if ($is_locked && isset($_SESSION['locked_until'])): ?>
    let lockUntil = <?php echo $_SESSION['locked_until']; ?> * 1000;
    let timerInterval = setInterval(function() {
        let now = new Date().getTime();
        let remaining = lockUntil - now;
        
        if (remaining <= 0) {
            clearInterval(timerInterval);
            location.reload();
        }
    }, 1000);
<?php endif; ?>
</script>


</body>
</html>