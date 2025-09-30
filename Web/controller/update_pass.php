<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/UserService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$csrfToken = $input['csrf_token'] ?? '';
$oldPassword = trim($input['old_password'] ?? '');
$newPassword = trim($input['new_password'] ?? '');
$confirmPassword = trim($input['confirm_password'] ?? '');

if ($csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if (!$oldPassword || !$newPassword || !$confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'All password fields are required']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match']);
    exit;
}

try {
    $userService = new UserService($pdo);
    $userService->updatePassword($_SESSION['user_id'], $oldPassword, $newPassword);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
