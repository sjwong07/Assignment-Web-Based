<?php
// 1. Correct way to include your root config
require_once __DIR__ . '/config.php'; 

// Lockout configuration
$max_attempts = 3;
$lockout_time = 300; 

$error = "";
$remaining_locked_minutes = 0;

// Redirect if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: " . BASE_URL . "index.php"); // Fixed: removed extra slash
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
        $sql = "SELECT * FROM user WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $user, $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            
            if (isset($row['is_blocked']) && $row['is_blocked'] == 1) {
                $error = "This account is blocked. Please contact support.";
            }
            else if (hash('sha256', $pass) === $row['password'] || password_verify($pass, $row['password'])) {

                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['user_id'];

                unset($_SESSION['login_attempts']);
                unset($_SESSION['locked_until']);

                header("Location: " . BASE_URL . "index.php"); // Fixed: removed extra slash
                exit;

            } else {
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $attempts_left = $max_attempts - $_SESSION['login_attempts'];
                
                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['locked_until'] = time() + $lockout_time;
                    $is_locked = true;
                } else {
                    $error = "Invalid password. " . $attempts_left . " attempts remaining.";
                }
            }
        } else {
            $error = "User not found.";
        }
    }
}

// After this PHP block, you start your HTML
include 'lib/_head.php'; 
?>
<div style="max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">

    <h2 style="text-align:center; margin-bottom:20px;">Login</h2>

    <?php if (!empty($error)): ?>
        <div style="color:red; margin-bottom:10px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($is_locked): ?>
        <div style="color:red;">
            Account locked. Try again in <?= $remaining_locked_minutes ?> minutes.
        </div>
    <?php else: ?>

        <form method="POST">
            <div style="margin-bottom:15px;">
                <label>Username or Email</label>
                <input type="text" name="username" required style="width:100%; padding:8px;">
            </div>

            <div style="margin-bottom:15px;">
                <label>Password</label>
                <input type="password" name="password" required style="width:100%; padding:8px;">
            </div>

            <button type="submit" style="width:100%; padding:10px; background:#667eea; color:white; border:none; border-radius:5px;">
                Login
            </button>
        </form>

    <?php endif; ?>

</div>

</div> <!-- close container -->
</main>
</body>
</html>