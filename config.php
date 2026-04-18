<?php
if (defined('BASE_FILE_LOADED')) return;
define('BASE_FILE_LOADED', true);
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ ENABLE ERROR REPORTING (To fix blank pages)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==============================
// DATABASE CONFIGURATION
// ==============================
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$dbname = 'dbA'; // Ensure this matches your phpMyAdmin database name

$connection = mysqli_connect($host, $username_db, $password_db, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// ==============================
// SITE SETTINGS
// ==============================
define('SITE_NAME', 'ELEX Store');
define('BASE_URL', 'http://localhost:8000/');
define('CURRENCY', 'RM');

// ==============================
// HELPER FUNCTIONS
// ==============================
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

date_default_timezone_set('Asia/Kuala_Lumpur');

function check_access() {
    global $connection;
    if (!isset($_SESSION['user_id'])) return false;
    
    $id = $_SESSION['user_id'];
    // Using mysqli_real_escape_string for extra safety
    $safe_id = mysqli_real_escape_string($connection, $id);
    $result = mysqli_query($connection, "SELECT is_blocked FROM user WHERE user_id = '$safe_id'");
    $user = mysqli_fetch_assoc($result);
    
    if ($user && $user['is_blocked'] == 1) {
        session_destroy();
        // Redirect them to login with a message
        header("Location: " . BASE_URL . "login.php?error=blocked");
        exit();
    }
    return true;
}

function encode($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (isset($_SESSION['flash'])) {
            $msg = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $msg;
        }
        return false;
    }

}
?>