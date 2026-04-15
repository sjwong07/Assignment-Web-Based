<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

if ($_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_POST['user_id'] ?? null;

$stmt = $pdo->prepare("SELECT is_blocked FROM user WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$new_status = $user['is_blocked'] ? 0 : 1;

$stmt = $pdo->prepare("UPDATE user SET is_blocked = ? WHERE user_id = ?");
$stmt->execute([$new_status, $user_id]);

echo json_encode([
    'success' => true,
    'new_status' => $new_status,
    'message' => $new_status ? 'User blocked successfully' : 'User unblocked successfully'
]);
?>