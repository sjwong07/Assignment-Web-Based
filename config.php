<?php
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
define('BASE_URL', 'http://localhost/shopping/');
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
?>