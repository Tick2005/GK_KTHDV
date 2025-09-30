<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $userService = new UserService($pdo);
    $balance = $userService->getBalance($_SESSION['user_id']);
    echo json_encode(['success' => true, 'data' => ['balance' => (float)$balance]]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>