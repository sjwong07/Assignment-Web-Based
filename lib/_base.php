<?php
// ========================================
// PHP SETUPS & SESSION START
// ========================================

// Start session first
session_start();

// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// ========================================
// DATABASE CONFIGURATION & CONNECTION
// ========================================

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dbA');

// Security configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
define('SESSION_TIMEOUT', 3600); // 1 hour
define('REMEMBER_ME_DAYS', 30);
define('CSRF_TOKEN_LENGTH', 32);

// Create MySQLi connection (for authentication)
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($connection === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($connection, "utf8mb4");

// Create PDO connection (for shopping cart and other features)
try {
    $_db = new PDO('mysql:host=localhost;dbname=dbA;charset=utf8mb4', 'root', '', [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// ========================================
// GENERAL PAGE FUNCTIONS
// ========================================

function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

function encode($value) {
    return htmlentities($value);
}

function html_hidden($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

function html_select($key, $items, $default = '- Select One -', $selected = null, $attr = '') {
    $value = $selected !== null ? $selected : encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// ========================================
// ERROR HANDLING
// ========================================

// Global error array
$_err = [];

function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ========================================
// SESSION MANAGEMENT FUNCTIONS
// ========================================

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

// Regenerate session ID periodically
function regenerateSessionIfNeeded() {
    if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Store user session
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
}

// Destroy user session
function destroyUserSession() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// ========================================
// CSRF PROTECTION FUNCTIONS
// ========================================

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========================================
// RATE LIMITING FUNCTIONS
// ========================================

// Check rate limit
function checkRateLimit() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $_SESSION['last_attempt_time'] < LOCKOUT_TIME) {
            $remaining = LOCKOUT_TIME - (time() - $_SESSION['last_attempt_time']);
            return ['allowed' => false, 'remaining' => $remaining];
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }
    return ['allowed' => true, 'remaining' => 0];
}

// Log failed attempt (using MySQLi)
function logFailedAttempt($connection, $username, $ip) {
    $sql = "INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Count attempts and block IP if needed
    $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
            WHERE (username = ? OR ip_address = ?) 
            AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $username, $ip, LOCKOUT_TIME);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $attempts = $row['attempts'];
        mysqli_stmt_close($stmt);
        
        if ($attempts >= MAX_LOGIN_ATTEMPTS * 2) {
            $sql = "INSERT INTO blocked_ips (ip_address, expires_at) VALUES (?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
                    ON DUPLICATE KEY UPDATE expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE)";
            if ($stmt2 = mysqli_prepare($connection, $sql)) {
                $lockout_minutes = LOCKOUT_TIME / 60;
                mysqli_stmt_bind_param($stmt2, "sii", $ip, $lockout_minutes, $lockout_minutes);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            }
        }
    }
}

// Clear login attempts on success
function clearLoginAttempts($connection, $username, $ip) {
    $sql = "DELETE FROM login_attempts WHERE username = ? OR ip_address = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Check if IP is blocked
function isIPBlocked($connection, $ip) {
    $sql = "SELECT * FROM blocked_ips WHERE ip_address = ? AND (expires_at > NOW() OR expires_at IS NULL)";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $ip);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $blocked = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        return $blocked;
    }
    return false;
}

// ========================================
// REMEMBER ME FUNCTIONS
// ========================================

// Set remember me cookie
function setRememberMe($connection, $user_id) {
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $expires = time() + (REMEMBER_ME_DAYS * 86400);
    
    $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO auth_tokens (user_id, selector, hashed_validator, expires_at) 
            VALUES (?, ?, ?, FROM_UNIXTIME(?))";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "issi", $user_id, $selector, $hashed_validator, $expires);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        setcookie('remember_me', $selector . ':' . $validator, $expires, '/', '', true, true);
        return true;
    }
    return false;
}

// Check remember me cookie
function checkRememberMe($connection) {
    if (isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) === 2) {
            list($selector, $validator) = $parts;
            
            $sql = "SELECT * FROM auth_tokens WHERE selector = ? AND expires_at > NOW()";
            if ($stmt = mysqli_prepare($connection, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $selector);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    if (password_verify($validator, $row['hashed_validator'])) {
                        $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
                        if ($stmt2 = mysqli_prepare($connection, $sql)) {
                            mysqli_stmt_bind_param($stmt2, "i", $row['user_id']);
                            mysqli_stmt_execute($stmt2);
                            $user_result = mysqli_stmt_get_result($stmt2);
                            $user = mysqli_fetch_assoc($user_result);
                            
                            if ($user) {
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['email'] = $user['email'];
                                $_SESSION['role'] = $user['role'];
                                $_SESSION['login_time'] = time();
                                $_SESSION['last_activity'] = time();
                                return true;
                            }
                            mysqli_stmt_close($stmt2);
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
    return false;
}

// Clear remember me
function clearRememberMe($connection) {
    if (isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) === 2) {
            $selector = $parts[0];
            $sql = "DELETE FROM auth_tokens WHERE selector = ?";
            if ($stmt = mysqli_prepare($connection, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $selector);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        setcookie('remember_me', '', time() - 3600, '/', '', true, true);
    }
}

// ========================================
// PASSWORD RESET FUNCTIONS
// ========================================

// Generate password reset token
function generateResetToken($connection, $user_id) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $sql = "INSERT INTO password_resets (user_id, token, expires_at) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, used = 0";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $token, $expires, $token, $expires);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $token;
    }
    return false;
}

// Verify reset token
function verifyResetToken($connection, $token) {
    $sql = "SELECT pr.user_id, pr.token, pr.expires_at, u.username, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// Update password
function updatePassword($connection, $user_id, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ?, updated_at = NOW(), last_password_change = NOW() WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

// Mark reset token as used
function markResetTokenUsed($connection, $token) {
    $sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

// ========================================
// EMAIL VERIFICATION FUNCTIONS
// ========================================

// Generate email verification token
function generateEmailVerificationToken($connection, $user_id) {
    $token = bin2hex(random_bytes(32));
    
    $sql = "UPDATE users SET verification_token = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $token, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $token;
    }
    return false;
}

// Verify email
function verifyEmail($connection, $token) {
    $sql = "SELECT id, email FROM users WHERE verification_token = ? AND email_verified = 0";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            $update_sql = "UPDATE users SET email_verified = 1, verification_token = NULL, 
                           email_verified_at = NOW() WHERE id = ?";
            if ($update_stmt = mysqli_prepare($connection, $update_sql)) {
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                $result = mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
                mysqli_stmt_close($stmt);
                return $result;
            }
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// ========================================
// TWO-FACTOR AUTHENTICATION FUNCTIONS
// ========================================

// Generate 2FA code
function generate2FACode($connection, $user_id) {
    $code = sprintf("%06d", mt_rand(1, 999999));
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    $sql = "UPDATE users SET twofa_code = ?, twofa_expires = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $code, $expires, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $code;
    }
    return false;
}

// Verify 2FA code
function verify2FACode($connection, $user_id, $code) {
    $sql = "SELECT twofa_code, twofa_expires FROM users WHERE id = ? AND twofa_expires > NOW()";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $valid = hash_equals($row['twofa_code'], $code);
            mysqli_stmt_close($stmt);
            
            if ($valid) {
                $sql = "UPDATE users SET twofa_code = NULL, twofa_expires = NULL WHERE id = ?";
                if ($stmt2 = mysqli_prepare($connection, $sql)) {
                    mysqli_stmt_bind_param($stmt2, "i", $user_id);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_close($stmt2);
                }
                return true;
            }
        }
    }
    return false;
}

// ========================================
// USER MANAGEMENT FUNCTIONS (MySQLi)
// ========================================

// Get user by ID
function getUserById($connection, $user_id) {
    $sql = "SELECT id, username, email, role, twofa_enabled, last_login, created_at, 
                   email_verified, profile_picture, first_name, last_name, phone
            FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $user;
    }
    return null;
}

// Get user by email or username
function getUserByLogin($connection, $login) {
    $sql = "SELECT id, username, email, password, role, twofa_enabled, email_verified 
            FROM users WHERE username = ? OR email = ? LIMIT 1";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $login, $login);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $user;
    }
    return null;
}

// Update last login time
function updateLastLogin($connection, $user_id) {
    $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

// Update user profile
function updateUserProfile($connection, $user_id, $data) {
    $fields = [];
    $params = [];
    $types = "";
    
    if (isset($data['username'])) {
        $fields[] = "username = ?";
        $params[] = $data['username'];
        $types .= "s";
    }
    if (isset($data['email'])) {
        $fields[] = "email = ?";
        $params[] = $data['email'];
        $types .= "s";
    }
    if (isset($data['first_name'])) {
        $fields[] = "first_name = ?";
        $params[] = $data['first_name'];
        $types .= "s";
    }
    if (isset($data['last_name'])) {
        $fields[] = "last_name = ?";
        $params[] = $data['last_name'];
        $types .= "s";
    }
    if (isset($data['phone'])) {
        $fields[] = "phone = ?";
        $params[] = $data['phone'];
        $types .= "s";
    }
    if (isset($data['profile_picture'])) {
        $fields[] = "profile_picture = ?";
        $params[] = $data['profile_picture'];
        $types .= "s";
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $fields[] = "updated_at = NOW()";
    $params[] = $user_id;
    $types .= "i";
    
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        // Build the bind_param arguments dynamically
        $bind_params = [$stmt, $types];
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    return false;
}

// ========================================
// PASSWORD VALIDATION FUNCTIONS
// ========================================

// Validate password strength
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match("/[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/", $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

// ========================================
// ACTIVITY LOGGING FUNCTIONS
// ========================================

// Log user activity
function logActivity($connection, $user_id, $action) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $sql = "INSERT INTO user_activity_log (user_id, action, ip_address, user_agent, timestamp) 
            VALUES (?, ?, ?, ?, NOW())";
    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $ip_address, $user_agent);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

// ========================================
// SHOPPING CART FUNCTIONS (PDO)
// ========================================

// Get shopping cart
function get_cart() {
    global $_user, $_db;

    if($_user){
        $stm = $_db->prepare('
            SELECT product_id, unit FROM cart_item
            WHERE customer_id = ?
        ');
        $stm->execute([$_user->Customer_id]);
        $rows = $stm->fetchAll();

        $cart = [];
        foreach ($rows as $row){
            $cart[$row->product_id] = $row->unit;
        }
        return $cart;
    } else{
        return $_SESSION['cart'] ?? [];
    }
}

// Set shopping cart
function set_cart($cart = []) {
    global $_user, $_db;

    if($_user){
        $stm = $_db->prepare('
            DELETE FROM cart_item WHERE customer_id = ?
        ');
        $stm->execute([$_user->Customer_id]);
        
        $stm = $_db->prepare('
            INSERT INTO cart_item (customer_id, product_id, unit)
            VALUES (?, ?, ?)
        ');

        foreach ($cart as $product_id => $unit){
            $stm->execute([$_user->Customer_id, $product_id, $unit]);
        }
    } else {
        $_SESSION['cart'] = $cart;
    }
}

// Update shopping cart
function update_cart($id, $unit) {
    global $_db;

    $stm = $_db->prepare('SELECT COUNT(*) FROM Product WHERE Product_id = ?');
    $stm->execute([$id]);
    $exists = $stm->fetchColumn() > 0;

    if(!$exists){
        return false;
    }
    
    $cart = get_cart();

    if($unit >= 1 && $unit <= 10){
        $cart[$id] = $unit;
    }
    else{
        unset($cart[$id]);
    }

    ksort($cart);
    set_cart($cart);
    return true;
}

// ========================================
// FILE UPLOAD FUNCTIONS
// ========================================

function get_file($field_name) {
    // Check if a file was uploaded via form
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file uploaded
    }

    // Return the uploaded file info
    return $_FILES[$field_name];
}

// ========================================
// DATABASE FUNCTIONS (PDO)
// ========================================

// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// ========================================
// EMAIL FUNCTIONS (Placeholder)
// ========================================

// Send email (implement with your preferred mail library)
function sendEmail($to, $subject, $message) {
    // For production, use PHPMailer or similar
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@elexstore.com" . "\r\n";
    
    // In production, use actual mail sending
    // return mail($to, $subject, $message, $headers);
    
    // For demo purposes
    return true;
}

// Send password reset email
function sendPasswordResetEmail($email, $username, $reset_link) {
    $subject = "Password Reset Request - ELEX Store";
    $message = "
    <html>
    <head>
        <title>Password Reset Request</title>
    </head>
    <body>
        <h2>Hello $username,</h2>
        <p>We received a request to reset your password.</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='$reset_link'>$reset_link</a></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you didn't request this, please ignore this email.</p>
        <br>
        <p>Best regards,<br>ELEX Store Team</p>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// Send email verification email
function sendVerificationEmail($email, $username, $verification_link) {
    $subject = "Verify Your Email - ELEX Store";
    $message = "
    <html>
    <head>
        <title>Verify Your Email</title>
    </head>
    <body>
        <h2>Welcome $username!</h2>
        <p>Please verify your email address by clicking the link below:</p>
        <p><a href='$verification_link'>$verification_link</a></p>
        <p>This link will expire in 24 hours.</p>
        <br>
        <p>Best regards,<br>ELEX Store Team</p>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// ========================================
// SECURITY HEADERS
// ========================================

// Set security headers
function setSecurityHeaders() {
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    if ($_SERVER['HTTPS'] ?? false) {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

// ========================================
// INPUT SANITIZATION
// ========================================

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ========================================
// REDIRECT FUNCTIONS WITH MESSAGES
// ========================================

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// ========================================
// GLOBAL CONSTANTS AND VARIABLES
// ========================================

$_units = array_combine(range(1,10), range(1,10));

// ========================================
// INITIALIZATION
// ========================================

// Auto-check session timeout and remember me
if (isset($_SESSION['user_id'])) {
    if (!checkSessionTimeout()) {
        destroyUserSession();
        header("Location: login.php?timeout=1");
        exit();
    }
    regenerateSessionIfNeeded();
} else {
    // Check remember me cookie
    checkRememberMe($connection);
}

// Set security headers
setSecurityHeaders();

// Get client IP
$client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Check if IP is blocked (for login pages)
$current_file = basename($_SERVER['PHP_SELF']);
if (in_array($current_file, ['login.php', 'register.php', 'forgot_password.php'])) {
    if (isIPBlocked($connection, $client_ip)) {
        die("Your IP address has been blocked due to too many failed attempts. Please contact support.");
    }
}

// Generate CSRF token for forms
$csrf_token = generateCSRFToken();

// Note: $_user variable should be set by your authentication system
// If you have a customer object from your existing system, you can set it here
if (isset($_SESSION['customer_id'])) {
    // For existing customer system compatibility
    $stm = $_db->prepare("SELECT * FROM Customer WHERE Customer_id = ?");
    $stm->execute([$_SESSION['customer_id']]);
    $_user = $stm->fetch();
}

// ========================================
// DATABASE CLOSE FUNCTION (Call at end of pages if needed)
// ========================================

function closeDatabaseConnection($connection) {
    if ($connection) {
        mysqli_close($connection);
    }
}
?>