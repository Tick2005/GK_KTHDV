<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$csrfToken = $input['csrf_token'] ?? null;
$icon = $input['icon'] ?? null;

if ($csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}
$icon = trim($icon);
if (empty($icon) || !preg_match('/^[a-zA-Z0-9_-]+\.(png|jpg|jpeg|svg)$/', $icon)) {
     throw new Exception("Invalid icon file name or format.");
}
try {
    $userService = new UserService($pdo);
    $userService->updateIcon($_SESSION['user_id'], $icon);
    echo json_encode(['success' => true,'message'=>'Icon updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>