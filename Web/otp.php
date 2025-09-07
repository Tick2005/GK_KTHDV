<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$trans_id = filter_input(INPUT_GET, 'transaction_id', FILTER_VALIDATE_INT);
if (!$trans_id) {
    header("Location: home.php?error=Invalid transaction");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT t.*, tf.amount, tf.student_id AS target_student_id 
                           FROM transactions t 
                           JOIN tuitionfees tf ON t.fee_id = tf.fee_id 
                           WHERE t.transaction_id = ? AND t.status = 'pending'");
    $stmt->execute([$trans_id]);
    $trans = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trans) {
        header("Location: payment-fail.php?error=Invalid transaction");
        exit;
    }

    $stmt = $pdo->prepare("SELECT balance, email FROM students WHERE student_id = ?");
    $stmt->execute([$trans['payer_id']]);
    $payer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payer) {
        header("Location: payment-fail.php?error=Invalid payer");
        exit;
    }
} catch (PDOException $e) {
    header("Location: payment-fail.php?error=Database error");
    exit;
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: payment-fail.php?error=Invalid CSRF token");
        exit;
    }

    $otp = implode('', $_POST['otp']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM otps WHERE transaction_id = ? AND is_used = FALSE AND expires_at > NOW()");
        $stmt->execute([$trans_id]);
        $otp_record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($otp_record && $otp_record['otp_code'] === $otp) {
            // Start transaction with row lock for balance to prevent conflicts
            $pdo->beginTransaction();
            try {
                // Lock payer's balance row
                $stmt = $pdo->prepare("SELECT balance FROM students WHERE student_id = ? FOR UPDATE");
                $stmt->execute([$trans['payer_id']]);

                // Update OTP as used
                $stmt = $pdo->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?");
                $stmt->execute([$otp_record['otp_id']]);

                // Update transaction status
                $stmt = $pdo->prepare("UPDATE transactions SET status = 'success', confirmed_at = NOW() WHERE transaction_id = ?");
                $stmt->execute([$trans_id]);

                // Update tuition fee status
                $stmt = $pdo->prepare("UPDATE tuitionfees SET status = 'paid' WHERE fee_id = ?");
                $stmt->execute([$trans['fee_id']]);

                // Deduct balance
                $stmt = $pdo->prepare("UPDATE students SET balance = balance - ? WHERE student_id = ?");
                $stmt->execute([$trans['amount'], $trans['payer_id']]);

                $pdo->commit();

                // Mock send confirmation email
                // In production: mail($payer['email'], "Xác nhận thanh toán", "Giao dịch thành công cho phí ID: " . $trans['fee_id']);

                header("Location: payment-success.php");
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                header("Location: payment-fail.php?error=Database error");
                exit;
            }
        } else {
            // Update transaction to failed
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE transaction_id = ?");
            $stmt->execute([$trans_id]);
            header("Location: payment-fail.php?error=Invalid OTP");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: payment-fail.php?error=Database error");
        exit;
    }
}

// Handle resend OTP
if (isset($_GET['resend']) && $_GET['resend'] === 'true' && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
    try {
        // Invalidate previous OTPs
        $stmt = $pdo->prepare("UPDATE otps SET is_used = TRUE WHERE transaction_id = ?");
        $stmt->execute([$trans_id]);

        // Generate new OTP
        $otp_code = sprintf("%06d", mt_rand(100000, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+300 seconds')); // 5 minutes
        $stmt = $pdo->prepare("INSERT INTO otps (transaction_id, otp_code, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$trans_id, $otp_code, $expires_at]);

        // Mock send OTP email
        // In production: mail($payer['email'], "Mã OTP", "Mã OTP của bạn: $otp_code");

        // Regenerate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: otp.php?transaction_id=$trans_id&message=otp_resent");
        exit;
    } catch (PDOException $e) {
        header("Location: payment-fail.php?error=Database error");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iBanking TDTU - Xác thực OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
    <style>
        .otp-box {
            width: 40px;
            text-align: center;
            font-size: 18px;
        }
        .otp-box:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center mb-4">XÁC THỰC OTP</h2>
            <p class="text-center">Vui lòng nhập mã OTP chúng tôi đã gửi qua email. Mã có giá trị trong 300 giây.</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['message']) && $_GET['message'] === 'otp_resent'): ?>
                <div class="alert alert-success">Mã OTP mới đã được gửi!</div>
            <?php endif; ?>

            <form id="otp-form" method="POST" class="d-flex justify-content-center gap-2">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp[<?php echo $i; ?>]" class="otp-box form-control" maxlength="1" pattern="\d" required>
                <?php endfor; ?>
            </form>
            <button type="submit" form="otp-form" class="btn btn-primary w-100 mt-3">Tiếp tục</button>
            <a href="otp.php?transaction_id=<?php echo $trans_id; ?>&resend=true&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="d-block text-center mt-2">Chưa nhận được mã? Gửi lại</a>
            <p id="otp-timer" class="text-center mt-2">300s</p>
        </div>
    </div>

    <script src="./script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>