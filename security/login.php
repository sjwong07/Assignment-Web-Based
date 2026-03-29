<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Online Shop</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    <div class="login-container">
        <h2>Member Login</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <p class="error-banner">
                <?php 
                    if($_GET['error'] == "blocked") echo "Too many failed attempts. Try again in 15 mins.";
                    else echo "Invalid username or password.";
                ?>
            </p>
        <?php endif; ?>

        <form id="login-form" method="POST" action="login_process.php">
            <div>
                <label>Username:</label>
                <input type="text" name="username" id="username" required>
                <span class="error" id="user-err"></span>
            </div>
            
            <div>
                <label>Password:</label>
                <input type="password" name="password" id="password" required>
                <span class="error" id="pass-err"></span>
            </div>

            <div class="form-options">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Remember Me</label>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>