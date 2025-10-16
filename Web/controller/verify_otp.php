<?php
// verify_otp.php (ĐÃ SỬA LỖI TRANSACTION KÉP)
header('Content-Type: application/json');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/PaymentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Đọc JSON body
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit;
}

$csrfToken = $input['csrf_token'] ?? null;
$transId   = $input['transaction_id'] ?? null;
$otpCode   = $input['otp'] ?? null;

// Check CSRF
if (!$csrfToken || $csrfToken !== ($_SESSION['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check input
if (!$transId || !$otpCode) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing transaction ID or OTP']);
    exit;
}

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection not initialized");
    }

    $paymentService = new PaymentService($pdo);
    $result = $paymentService->verifyOtp($_SESSION['user_id'], $transId, $otpCode);
    if (!$result['success']) {
        $_SESSION['payment_error'] = $result['message'] ?? 'Transaction failed';
    }
    $_SESSION['payment_result'] = $result;

    // Output JSON cuối cùng
    echo json_encode($result);
    exit; 
} catch (Throwable $e) { 
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode(['success' => false,'is_final_fail' => true, 'message' => "Internal Server Error: " . $e->getMessage()]);
    exit;
}
?>