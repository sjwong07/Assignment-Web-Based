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
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
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

        // Check if user exists
        $check_sql = "SELECT user_id FROM `user` WHERE username = ? AND email = ?";
        $stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 0) {
            $error = "User not found. Please check your details.";
        } else {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $update_sql = "UPDATE `user` SET password = ? WHERE username = ? AND email = ?";
            $update_stmt = mysqli_prepare($connection, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sss", $hashed_password, $username, $email);

            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Password reset successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Error updating password.";
            }

            mysqli_stmt_close($update_stmt);
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
<title>Reset Password</title>

<style>
    body {
        font-family: Arial;
        background: #f8fafc;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .box {
        background: white;
        padding: 30px;
        border-radius: 10px;
        width: 350px;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    input {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #0a51b5;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .msg {
        padding: 10px;
        margin-bottom: 10px;
        text-align: center;
        border-radius: 5px;
    }

    .error { background: #fee2e2; color: red; }
    .success { background: #d1fae5; color: green; }
</style>
</head>

<body>

<div class="box">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Reset Password</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
        <a href="login.php">Back to Login</a>
    </p>
</div>

</body>
</html>
