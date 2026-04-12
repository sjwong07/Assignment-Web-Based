<?php
// COPY THIS FILE to database.php and change the database name

$host = 'localhost';
$db = 'YOUR_DATABASE_NAME';  // CHANGE THIS to your database name
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>