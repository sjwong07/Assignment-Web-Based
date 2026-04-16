index<?php
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
$lockout_time = 300; // 5 minutes (300 seconds)

$error = "";
$remaining_locked_minutes = 0;

// Redirect if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header("location: index.php");
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
// LOGIN PROCESS
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
                $_SESSION['phone'] = $row['phone'];

                // Reset failed attempts on successful login
                unset($_SESSION['login_attempts']);
                unset($_SESSION['locked_until']);

                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header("location: /order/ProductAdmin.php");
                } else {
                    header("location: /order/ProductMember.php");
                }
                exit;

            } else {
                // WRONG PASSWORD
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $attempts_left = $max_attempts - $_SESSION['login_attempts'];

                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['locked_until'] = time() + $lockout_time;
                    $error = "Too many failed attempts! Account is locked for 5 minutes.";
                    $is_locked = true;
                    $remaining_locked_minutes = 5;
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
                $error = "Too many failed attempts! Account is locked for 5 minutes.";
                $is_locked = true;
                $remaining_locked_minutes = 5;
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    position: relative;
    overflow-x: hidden;
}

/* Animated background bubbles */
body::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    padding: 45px;
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 440px;
    animation: fadeInUp 0.6s ease-out;
    position: relative;
    z-index: 1;
}

h2 {
    text-align: center;
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    color: #64748b;
    font-size: 14px;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 22px;
    position: relative;
}

label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    display: block;
    margin-bottom: 8px;
}

label i {
    margin-right: 8px;
    color: #667eea;
}

input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s ease;
    background: #f8fafc;
}

input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

input:hover:not(:focus) {
    border-color: #cbd5e1;
}

input:disabled {
    background-color: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.7;
}

.toggle-btn {
    position: absolute;
    right: 16px;
    top: 44px;
    cursor: pointer;
    color: #94a3b8;
    transition: color 0.3s ease;
    font-size: 18px;
}

.toggle-btn:hover {
    color: #667eea;
}

.error-box {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 14px;
    font-weight: 500;
    border-left: 4px solid #dc2626;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.4s ease-out;
}

.error-box i {
    font-size: 18px;
}

.warning-box {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 14px;
    font-weight: 500;
    border-left: 4px solid #f59e0b;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.4s ease-out;
}

.warning-box i {
    font-size: 18px;
}

.btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s ease;
    margin-top: 10px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
}

.btn:active:not(:disabled) {
    transform: translateY(0);
}

.btn:disabled {
    background: linear-gradient(135deg, #94a3b8 0%, #94a3b8 100%);
    cursor: not-allowed;
    opacity: 0.8;
}

.register-link {
    text-align: center;
    margin-top: 25px;
    font-size: 14px;
    color: #64748b;
}

.register-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
}

.register-link a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.attempts-info {
    font-size: 12px;
    color: #94a3b8;
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.attempts-info i {
    color: #f59e0b;
}

.countdown-timer {
    font-size: 13px;
    font-weight: 600;
    color: #d97706;
    margin-top: 12px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.role-badge {
    text-align: center;
    margin-bottom: 20px;
}

.role-badge span {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-badge .member {
    background: #10b981;
    color: white;
}

.role-badge .admin {
    background: #f59e0b;
    color: white;
}

/* Responsive */
@media (max-width: 640px) {
    .card {
        padding: 30px 25px;
    }
    
    h2 {
        font-size: 28px;
    }
}
</style>
</head>

<body>
<div class="card">
    <h2>Welcome Back</h2>
    <div class="subtitle">Sign in to your account</div>

    <div class="role-badge">
        <span class="member"><i class="fas fa-users"></i> Member Login</span>
        <span class="admin"><i class="fas fa-user-shield"></i> Admin Login</span>
    </div>

    <?php if ($error && !$is_locked): ?>
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($is_locked): ?>
        <div class="warning-box">
            <i class="fas fa-lock"></i>
            <span>Too many failed attempts! Account is locked for <strong><?php echo $remaining_locked_minutes; ?></strong> minute(s).</span>
        </div>
        <div class="countdown-timer" id="countdownTimer">
            <i class="fas fa-hourglass-half"></i>
            <span id="timerText">Unlocking in <?php echo $remaining_locked_minutes; ?>:00</span>
        </div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
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
            <i class="fas fa-sign-in-alt"></i> <?php echo $is_locked ? 'Account Locked' : 'Sign In'; ?>
        </button>

        <div class="attempts-info">
            <i class="fas fa-shield-alt"></i> Maximum 3 attempts - 5 minute lockout
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

<?php if ($is_locked && isset($_SESSION['locked_until'])): ?>
    let lockUntil = <?php echo $_SESSION['locked_until']; ?> * 1000;
    let timerInterval = setInterval(function() {
        let now = new Date().getTime();
        let remaining = lockUntil - now;
        
        if (remaining <= 0) {
            clearInterval(timerInterval);
            location.reload();
        } else {
            let minutes = Math.floor(remaining / 60000);
            let seconds = Math.floor((remaining % 60000) / 1000);
            let timerText = document.getElementById('timerText');
            if (timerText) {
                timerText.textContent = `Unlocking in ${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }
    }, 1000);
<?php endif; ?>

// Add shake animation on error
<?php if ($error && !$is_locked): ?>
    document.querySelector('.card').style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        document.querySelector('.card').style.animation = '';
    }, 500);
<?php endif; ?>
</script>

</body>
</html>