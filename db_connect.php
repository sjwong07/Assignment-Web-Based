<?php
// db_connect.php
$host = 'localhost';
$dbname = 'your_database_name';
$username_db = 'your_database_user';
$password_db = 'your_database_password';

$connection = mysqli_connect($host, $username_db, $password_db, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>