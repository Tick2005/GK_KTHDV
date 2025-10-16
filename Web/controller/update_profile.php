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
if (empty($username) || empty($fullName) || empty($email) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'All profile fields are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}
if (!preg_match('/^\d{10,11}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format.']);
    exit;
}
try {
    $userService = new UserService($pdo);
    $userService->updateProfile($_SESSION['user_id'], $username, $fullName, $email, $phone);
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
