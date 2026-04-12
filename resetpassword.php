<?php
session_start();

// Database connection
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$dbname = 'store_db';

$conn = mysqli_connect($host, $username_db, $password_db, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $new_pass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // 1. Check if user exists in database
    $sql = "SELECT * FROM Customer WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {

        // 2. Password validation
        if (strlen($new_pass) < 8 ||
            !preg_match('/[A-Z]/', $new_pass) ||
            !preg_match('/[0-9]/', $new_pass) ||
            !preg_match('/[\W]/', $new_pass)) {

            $error = "Password must be 8+ chars, include Uppercase, Number, Special Char.";

        } elseif ($new_pass !== $confirm) {
            $error = "Passwords do not match.";
        } else {

            // 3. Hash new password
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

            // 4. Update database password
            $update = "UPDATE Customer SET password = ? WHERE email = ?";
            $stmt2 = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt2, "ss", $hashed, $email);

            if (mysqli_stmt_execute($stmt2)) {
                $success = "Password reset successful! <a href='login.php'>Login now</a>";
            } else {
                $error = "Failed to update password.";
            }
        }

    } else {
        $error = "No account found with that email.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
            color: #3b82f6;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Reset Password</button>
    </form>

    <a href="login.php">Back to Login</a>
</div>

</body>
</html>
