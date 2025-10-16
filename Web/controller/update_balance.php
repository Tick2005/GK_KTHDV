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
$csrfToken = $input['csrf_token'] ?? null;
$amount = (float)($input['amount'] ?? 0);

if ($csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    if ($amount <= 0 || !is_numeric($amount)) {
        throw new Exception("Invalid amount: Amount must be a positive number.");
    }

    $userService = new UserService($pdo);
    if (!$userService->topUpBalance($_SESSION['user_id'], $amount)) {
        throw new Exception("Failed to process balance update. Database operation failed.");
    }
    
    echo json_encode(['success' => true, 'message' => 'Balance updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>