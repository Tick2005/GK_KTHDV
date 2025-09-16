<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

// Hàm gửi OTP (giả định)
function send_email_otp($email, $otp) {
    return true;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if ($input['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}
$trans_id = $input['transaction_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND payer_id = ? AND status = 'pending'");
    $stmt->execute([$trans_id, $_SESSION['user_id']]);
    if (!$trans = $stmt->fetch()) throw new Exception('Invalid transaction');
    $stmt = $pdo->prepare("UPDATE otps SET is_used = TRUE WHERE transaction_id = ?");
    $stmt->execute([$trans_id]);
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', time() + 300);
    $stmt = $pdo->prepare("INSERT INTO otps (transaction_id, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$trans_id, $otp, $expires]);
    $stmt = $pdo->prepare("SELECT email FROM customer WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $email = $stmt->fetchColumn();
    if (!send_email_otp($email, $otp)) throw new Exception('Failed to send OTP');
    echo json_encode(['success' => true, 'message' => 'OTP resent']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>