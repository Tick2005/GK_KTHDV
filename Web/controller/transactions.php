<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/PaymentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $paymentService = new PaymentService($pdo);
    $transactions = $paymentService->getTransactionsByPayer($_SESSION['user_id']);
    echo json_encode(['success' => true, 'data' => $transactions]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>