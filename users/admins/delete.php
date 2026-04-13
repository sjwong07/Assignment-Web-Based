<?php
session_start();
require_once '../../config/database.php';

$user_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ? AND role = 'admin'");
$stmt->execute([$user_id]);

header('Location: index.php');
exit();
?>