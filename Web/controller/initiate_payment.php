<?php
ob_start();
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/PaymentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    ob_clean();
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
    $paymentService = new PaymentService($pdo);
    // Hàm này đã tạo và gửi OTP, và trả về ID giao dịch.
    $transId = $paymentService->initiatePayment($_SESSION['user_id'], $studentId, $agreeTerms);
    
    // 📢 BƯỚC QUAN TRỌNG: Lưu ID giao dịch vào Session.
    // Dùng một key độc lập, ví dụ 'current_trans_id'
    $_SESSION['current_trans_id'] = $transId; 

    ob_clean();
    // 📢 KHÔNG CẦN TRẢ VỀ transaction_id cho client nữa.
    echo json_encode(['success' => true]); 
    exit;
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>