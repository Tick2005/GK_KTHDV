<?php
header('Content-Type: application/json; charset=utf-8');
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
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $pdo->beginTransaction();
    $paymentService = new PaymentService($pdo);
    $paymentService->cancelTransaction($_SESSION['user_id'], $transId);
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Transaction cancelled successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>