<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

// Hàm gửi OTP thực tế bằng PHPMailer
function send_email_otp($email, $otp) {
    require_once __DIR__ . '/../src/PHPMailer.php';
    require_once __DIR__ . '/../src/SMTP.php';
    require_once __DIR__ . '/../src/Exception.php';
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    try {
        // Cấu hình SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'soagk1tdtu@gmail.com';
        $mail->Password = 'wveg zevy kdya rxbv';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('soagk1tdtu@gmail.com', 'iBanking TDTU');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Mã xác thực OTP giao dịch học phí';
        $mail->Body = '<p>Mã OTP của bạn là: <b>' . htmlspecialchars($otp) . '</b></p><p>Mã có hiệu lực trong 5 phút.</p>';

        if (!$mail->send()) {
            return false;
        }
        return true;
    } catch (\Exception $e) {
        return false;
    }
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
if (!$input['agree_terms']) {
    echo json_encode(['success' => false, 'message' => 'Must agree terms']);
    exit;
}
$target_id = $input['target_student_id'];
if (strlen($target_id) !== 8) {
    echo json_encode(['success' => false, 'message' => 'Invalid target_id']);
    exit;
}
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT * FROM tuitionfees WHERE student_id = ? AND status = 'unpaid'");
    $stmt->execute([$target_id]);
    $fees = $stmt->fetchAll();
    if (empty($fees)) throw new Exception('No unpaid fees');
    $total_amount = 0;
    foreach ($fees as $fee) {
        $total_amount += (float)$fee['amount'];
    }
    // Tạo trans cho fee đầu tiên, nhưng amount tổng (adjust nếu cần per fee)
    $stmt = $pdo->prepare("INSERT INTO transactions (fee_id, payer_id, amount, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$fees[0]['fee_id'], $_SESSION['user_id'], $total_amount]);
    $trans_id = $pdo->lastInsertId();
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', time() + 300);
    $stmt = $pdo->prepare("INSERT INTO otps (transaction_id, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$trans_id, $otp, $expires]);
    $stmt = $pdo->prepare("SELECT email FROM customer WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $email = $stmt->fetchColumn();
    if (!send_email_otp($email, $otp)) throw new Exception('Failed to send OTP');
    $pdo->commit();
    // Trả về cả OTP để kiểm tra (chỉ dùng cho debug, sau này cần xoá)
    echo json_encode(['success' => true, 'transaction_id' => $trans_id, 'otp_debug' => $otp]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
