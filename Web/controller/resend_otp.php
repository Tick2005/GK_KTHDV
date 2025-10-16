<?php
// resend_otp.php (GIỮ NGUYÊN)
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/PaymentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$transId = $input['transaction_id'] ?? 0;


try {
    $paymentService = new PaymentService($pdo);
    $paymentService->resendOtp($_SESSION['user_id'], $transId);
    echo json_encode(['success' => true, 'message' => 'OTP resent']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>