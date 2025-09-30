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
$username = trim($input['username'] ?? '');
$fullName = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');

if ($csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $userService = new UserService($pdo);
    $userService->updateProfile($_SESSION['user_id'], $username, $fullName, $email, $phone);
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
