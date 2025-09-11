<?php
header('Content-Type: application/json');
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
    $stmt = $pdo->prepare("SELECT * FROM otps WHERE transaction_id = ? AND is_used = FALSE AND expires_at > NOW() AND otp_code = ?");
    $stmt->execute([$trans_id, $otp]);
    if (!$otp_record = $stmt->fetch()) throw new Exception('Invalid or expired OTP');
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
    $pdo->rollBack();
    $stmt = $pdo->prepare("UPDATE transactions SET status = 'failed', note = ? WHERE transaction_id = ?");
    $stmt->execute([$e->getMessage(), $trans_id]);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>