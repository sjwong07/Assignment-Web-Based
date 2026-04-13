<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

$user_id = $_POST['user_id'] ?? 0;
$current_status = $_POST['current_status'] ?? 0;
$new_status = $current_status ? 0 : 1;

$stmt = $pdo->prepare("UPDATE user SET is_blocked = ? WHERE user_id = ? AND role = 'member'");
$stmt->execute([$new_status, $user_id]);

echo json_encode(['success' => true]);
?>