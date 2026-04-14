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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Store</title>
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

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
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

.box {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    padding: 45px;
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 500px;
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
    margin-bottom: 20px;
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

.msg {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 25px;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    animation: slideIn 0.4s ease-out;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.msg i {
    font-size: 18px;
}

.error-msg {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

.success-msg {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #059669;
    border-left: 4px solid #059669;
}

.success-msg a {
    color: #059669;
    font-weight: 700;
    text-decoration: none;
    border-bottom: 2px solid #059669;
    transition: all 0.3s ease;
}

.success-msg a:hover {
    color: #047857;
    border-bottom-color: #047857;
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

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
}

.btn:active {
    transform: translateY(0);
}

.login-link {
    text-align: center;
    margin-top: 25px;
    font-size: 14px;
    color: #64748b;
}

.login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
}

.login-link a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.password-requirements {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.password-requirements span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.password-requirements i {
    font-size: 10px;
}

.requirement-met {
    color: #10b981;
}

.requirement-unmet {
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 640px) {
    .box {
        padding: 30px 25px;
    }
    
    h2 {
        font-size: 28px;
    }
    
    .password-requirements {
        font-size: 10px;
        gap: 8px;
    }
}
</style>
</head>

<body>
<div class="box">
    <h2>Create Account</h2>
    <div class="subtitle">Join us today! 🎉</div>

    <?php if ($error): ?>
        <div class="msg error-msg">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg success-msg">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        
        <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" id="username" required 
                   value="<?php echo htmlspecialchars($username ?? ''); ?>"
                   placeholder="e.g., john123">
            <small style="font-size: 11px; color: #94a3b8; margin-top: 5px; display: block;">
                <i class="fas fa-info-circle"></i> Must contain letters and numbers
            </small>
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" id="email" required 
                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                   placeholder="you@example.com">
        </div>

        <div class="form-group">
            <label><i class="fas fa-phone"></i> Phone</label>
            <input type="text" name="phone" id="phone" required 
                   value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                   placeholder="1234567890">
            <small style="font-size: 11px; color: #94a3b8; margin-top: 5px; display: block;">
                <i class="fas fa-info-circle"></i> 7-15 digits only
            </small>
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" id="pass" required>
            <i class="fa-solid fa-eye toggle-btn" onclick="toggle('pass', this)"></i>
            <div class="password-requirements" id="passwordRequirements">
                <span id="lengthReq"><i class="fas fa-circle"></i> 8-12 chars</span>
                <span id="upperReq"><i class="fas fa-circle"></i> Uppercase</span>
                <span id="numberReq"><i class="fas fa-circle"></i> Number</span>
                <span id="specialReq"><i class="fas fa-circle"></i> Special char</span>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-check-circle"></i> Confirm Password</label>
            <input type="password" name="confirm_password" id="conf" required>
            <i class="fa-solid fa-eye toggle-btn" onclick="toggle('conf', this)"></i>
            <small style="font-size: 11px; color: #94a3b8; margin-top: 5px; display: block;" id="matchMsg"></small>
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-user-plus"></i> Register
        </button>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </form>
</div>

<script>
function toggle(id, icon) {
    const input = document.getElementById(id);
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

// Real-time password validation
const passwordInput = document.getElementById('pass');
const confirmInput = document.getElementById('conf');
const matchMsg = document.getElementById('matchMsg');

function validatePassword() {
    const password = passwordInput.value;
    
    // Length check (8-12)
    const lengthValid = password.length >= 8 && password.length <= 12;
    document.getElementById('lengthReq').innerHTML = `<i class="fas ${lengthValid ? 'fa-check-circle' : 'fa-circle'}"></i> 8-12 chars`;
    document.getElementById('lengthReq').style.color = lengthValid ? '#10b981' : '#94a3b8';
    
    // Uppercase check
    const upperValid = /[A-Z]/.test(password);
    document.getElementById('upperReq').innerHTML = `<i class="fas ${upperValid ? 'fa-check-circle' : 'fa-circle'}"></i> Uppercase`;
    document.getElementById('upperReq').style.color = upperValid ? '#10b981' : '#94a3b8';
    
    // Number check
    const numberValid = /[0-9]/.test(password);
    document.getElementById('numberReq').innerHTML = `<i class="fas ${numberValid ? 'fa-check-circle' : 'fa-circle'}"></i> Number`;
    document.getElementById('numberReq').style.color = numberValid ? '#10b981' : '#94a3b8';
    
    // Special character check
    const specialValid = /[\W_]/.test(password);
    document.getElementById('specialReq').innerHTML = `<i class="fas ${specialValid ? 'fa-check-circle' : 'fa-circle'}"></i> Special char`;
    document.getElementById('specialReq').style.color = specialValid ? '#10b981' : '#94a3b8';
    
    return lengthValid && upperValid && numberValid && specialValid;
}

function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    
    if (confirm.length === 0) {
        matchMsg.innerHTML = '';
        matchMsg.style.color = '#94a3b8';
    } else if (password === confirm) {
        matchMsg.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match!';
        matchMsg.style.color = '#10b981';
    } else {
        matchMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match';
        matchMsg.style.color = '#ef4444';
    }
}

passwordInput.addEventListener('input', function() {
    validatePassword();
    checkPasswordMatch();
});

confirmInput.addEventListener('input', checkPasswordMatch);

// Form submission validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    if (!validatePassword()) {
        e.preventDefault();
        alert('Please meet all password requirements:\n- 8-12 characters\n- At least 1 uppercase letter\n- At least 1 number\n- At least 1 special character');
    } else if (passwordInput.value !== confirmInput.value) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

</body>
</html>