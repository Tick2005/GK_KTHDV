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
$studentId = $input['target_student_id'] ?? '';
$agreeTerms = $input['agree_terms'] ?? false;

if ($csrfToken !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}

try {
    $pdo->beginTransaction();
    $paymentService = new PaymentService($pdo);
    $transId = $paymentService->initiatePayment($_SESSION['user_id'], $studentId, $agreeTerms);
    $pdo->commit();
    echo json_encode(['success' => true, 'transaction_id' => $transId]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>