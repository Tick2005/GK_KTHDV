<?php
header('Content-Type: application/json');
// Bắt lỗi PHP toàn cục, log ra file, không hiển thị HTML lỗi
set_exception_handler(function($e) {
    error_log($e);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit();
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno] $errstr in $errfile:$errline");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit();
});
ini_set('display_errors', 0);
ini_set('log_errors', 1);
session_start();
include '../db.php';

function send_email_otp($email, $msg) {
    return true;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}
$trans_id = $input['transaction_id'];
$otp = $input['otp'];
try {
    $stmt = $pdo->prepare("SELECT t.*, s.email FROM transactions t JOIN customer s ON t.payer_id = s.user_id WHERE t.transaction_id = ? AND t.status = 'pending'");
    $stmt->execute([$trans_id]);
    $trans = $stmt->fetch();
    if (!$trans || $trans['payer_id'] !== $_SESSION['user_id']) throw new Exception('Invalid transaction');
    $stmt = $pdo->prepare("SELECT * FROM otps WHERE transaction_id = ? AND otp_code = ?");
    $stmt->execute([$trans_id, $otp]);
    $debug_otp = $stmt->fetch();
    // Kiểm tra từng điều kiện và trả về debug nếu sai
    if (!$debug_otp) {
        throw new Exception('OTP not found for this transaction. Debug: ' . json_encode(['trans_id'=>$trans_id,'otp'=>$otp]));
    }
    if ($debug_otp['is_used']) {
        throw new Exception('OTP already used. Debug: ' . json_encode($debug_otp));
    }
    if (strtotime($debug_otp['expires_at']) <= time()) {
        throw new Exception('OTP expired. Debug: ' . json_encode($debug_otp));
    }
    // Đúng OTP, chưa dùng, chưa hết hạn
    $otp_record = $debug_otp;
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT balance FROM customer WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$trans['payer_id']]);
    $balance = $stmt->fetchColumn();
    if ($balance < $trans['amount']) throw new Exception('Insufficient funds');
    $stmt = $pdo->prepare("UPDATE customer SET balance = balance - ? WHERE user_id = ?");
    $stmt->execute([$trans['amount'], $trans['payer_id']]);
    $stmt = $pdo->prepare("UPDATE transactions SET status = 'success' WHERE transaction_id = ?");
    $stmt->execute([$trans_id]);
    $stmt = $pdo->prepare("UPDATE tuitionfees SET status = 'paid' WHERE fee_id = ?");
    $stmt->execute([$trans['fee_id']]);
    $stmt = $pdo->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?");
    $stmt->execute([$otp_record['otp_id']]);
    send_email_otp($trans['email'], "Transaction successful. Transaction ID: $trans_id");
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Payment successful', 'trans_id' => $trans_id]);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    if (isset($stmt) && isset($trans_id)) {
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'failed', note = ? WHERE transaction_id = ?");
        $stmt->execute([$e->getMessage(), $trans_id]);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
