<?php
session_start();
$error = ""; $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    $reg_user = $_SESSION['registered_user'] ?? null;

    if (!$reg_user || $email !== $reg_user['email']) {
        $error = "No account found with that email.";
    } elseif (strlen($new_pass) < 8 || !preg_match('/[A-Z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass) || !preg_match('/[\W]/', $new_pass)) {
        $error = "Password must be 8-12 chars with Uppercase, Number, and Special Char.";
    } elseif ($new_pass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $customer_id = $reg_user['customer_id'] ?? time() . rand(100, 999);

        $_SESSION['registered_user']['password'] = password_hash($new_pass, PASSWORD_DEFAULT);
        $_SESSION['registered_user']['customer_id'] = $customer_id;
        $success = "Password reset! <a href='/login.php' style='color:inherit; font-weight:bold;'>Login now</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --p: #475569; --a: #3b82f6; --bg: #f8fafc; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); width: 400px; }
        h2 { color: var(--p); text-align: center; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: var(--p); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .msg { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-size: 13px; text-align: center; border: 1px solid; }
        .err { background: #fee2e2; color: #ef4444; border-color: #ef4444; }
        .succ { background: #d1fae5; color: #10b981; border-color: #10b981; }
        .back { display: block; text-align: center; margin-top: 20px; font-size: 13px; color: var(--a); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Password</h2>
        <?php if ($error): ?> <div class="msg err"><?php echo $error; ?></div> <?php endif; ?>
        <?php if ($success): ?> <div class="msg succ"><?php echo $success; ?></div> <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Your Registered Email" required>
            <input type="password" name="new_password" maxlength="12" placeholder="New Password (8-12 chars)" required>
            <input type="password" name="confirm_password" maxlength="12" placeholder="Confirm New Password" required>
            <button type="submit" class="btn">Update Password</button>
        </form>
        <a href="/login.php" class="back"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
    </div>
</body>
</html>