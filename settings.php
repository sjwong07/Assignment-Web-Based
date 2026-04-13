<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// ==============================
// DATABASE CONFIGURATION
// ==============================

// ✅ DEFINE VARIABLES FIRST
$host = 'localhost';
$username_db = 'root';
$password_db = '';
$dbname = 'dbA';

// ✅ CONNECT DATABASE
$connection = mysqli_connect($host, $username_db, $password_db, $dbname);

// ✅ CHECK CONNECTION
if (!$connection) {
die("Connection failed: " . mysqli_connect_error());
}

// ==============================
// SITE SETTINGS
// ==============================
define('SITE_NAME', 'My Shopping Website');
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

// ==============================
// TIMEZONE
// ==============================
date_default_timezone_set('Asia/Kuala_Lumpur');

?>

