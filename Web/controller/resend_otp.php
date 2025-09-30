<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/PaymentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$csrfToken = $input['csrf_token'] ?? null;
$transId = $input['transaction_id'] ?? 0;

if ($csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}

try {
    $paymentService = new PaymentService($pdo);
    $paymentService->resendOtp($_SESSION['user_id'], $transId);
    echo json_encode(['success' => true, 'message' => 'OTP resent']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>