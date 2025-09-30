<?php
require_once __DIR__ . '/../repository/TransactionRepository.php';
require_once __DIR__ . '/../repository/FeeRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/StudentRepository.php';
require_once __DIR__ . '/../repository/OtpRepository.php';
require_once __DIR__ . '/../service/MailService.php';

class PaymentService {
    private $transactionRepo;
    private $feeRepo;
    private $userRepo;
    private $studentRepo;
    private $otpRepo;
    private $mailService;

    public function __construct($pdo) {
        $this->transactionRepo = new TransactionRepository($pdo);
        $this->feeRepo = new FeeRepository($pdo);
        $this->userRepo = new UserRepository($pdo);
        $this->studentRepo = new StudentRepository($pdo);
        $this->otpRepo = new OtpRepository($pdo);
        $this->mailService = new MailService();
    }

    /**
     * Start a new payment process.
     */
    public function initiatePayment($payerId, $studentId, $agreeTerms) {
        if (!$agreeTerms) throw new Exception('You must agree to the terms and conditions.');
        if (strlen($studentId) !== 8) throw new Exception('Invalid student ID.');

        // Kiểm tra transaction pending
        $pending = $this->transactionRepo->findPendingByStudentId($studentId);
        if ($pending) {
            throw new Exception('Payment is already in process for this student.');
        }

        $fees = $this->feeRepo->getUnpaidFeesByStudentId($studentId);
        if (empty($fees)) throw new Exception('No outstanding tuition fees.');

        $totalAmount = array_reduce($fees, fn($carry, $item) => $carry + (float)$item['amount'], 0);

        $user = $this->userRepo->findById($payerId);
        if ($user['balance'] < $totalAmount) {
            throw new Exception('Insufficient balance to complete payment.');
        }

        // Tạo transaction và OTP
        $feeId = $fees[0]['fee_id'];
        $transactionId = $this->transactionRepo->create($payerId, $feeId, $totalAmount);

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300);
        $this->otpRepo->create($transactionId, $otp, $expiresAt);

        if (!$this->mailService->sendOtpEmail($user['email'], $otp)) {
            throw new Exception('Failed to send OTP.');
        }

        return $transactionId;
    }


    /**
     * Verify OTP and complete the payment.
     */
    public function verifyOtp($payerId, $transactionId, $otpCode) {
        // 1) load transaction
        $transaction = $this->transactionRepo->findById($transactionId);
        if (!$transaction || $transaction['payer_id'] !== $payerId || $transaction['status'] !== 'pending') {
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'Invalid transaction.');
            return ['success' => false, 'message' => 'Invalid transaction.'];
        }

        // 2) get latest OTP for this transaction
        $otp = $this->otpRepo->findByTransactionId($transactionId); // ensure this returns the OTP row
        if (!$otp) {
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'No OTP found.');
            return ['success' => false, 'message' => 'No OTP found.'];
        }

        // 3) already used?
        if (!empty($otp['is_used'])) {
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'OTP already used.');
            return ['success' => false, 'message' => 'OTP already used.'];
        }

        // 4) expired? Use expires_at field (must exist in your otps table)
        // Thay đoạn này (đang sai):
        if (!$otp  || (time() - strtotime($otp['expires_at'])) > 300) {
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'Invalid or expired OTP.');
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }


        // 5) attempts limit
        $attempts = (int)($otp['attempts'] ?? 0);
        if ($attempts >= 3) {
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'Maximum OTP attempts exceeded.');
            return ['success' => false, 'message' => 'Maximum OTP attempts exceeded.'];
        }

        // 6) wrong code -> increment attempts and return attempts_left
        if ($otpCode !== $otp['otp_code']) {
            // increment by otp_id (our repo method expects otp_id)
            $this->otpRepo->incrementAttempts($otp['otp_id']);
            $attemptsLeft = max(0, 3 - ($attempts + 1));
            if ($attemptsLeft === 0) {
                // final failure - mark transaction failed
                $this->transactionRepo->updateStatus($transactionId, 'failed', 'Maximum OTP attempts exceeded.');
            }
            return [
                'success' => false,
                'message' => 'Incorrect OTP.',
                'attempts_left' => $attemptsLeft
            ];
        }

        // 7) OK: check balance
        $balance = $this->userRepo->getBalance($payerId);
        if ($balance < $transaction['amount']) {
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'Insufficient funds.');
            return ['success' => false, 'message' => 'Insufficient funds.'];
        }

        // 8) perform payment: deduct balance, mark fee paid, mark otp used, update transaction success
        $this->userRepo->updateBalance($payerId, $transaction['amount']); // assume this subtracts
        $fee = $this->feeRepo->findById($transaction['fee_id']);
        $student = $this->studentRepo->findById($fee['student_id'] ?? null);

        $note = sprintf(
            "Tuition payment completed for student %s - Semester %s, Year %s",
            $student['full_name'] ?? 'N/A',
            $fee['semester'] ?? 'N/A',
            $fee['school_year'] ?? 'N/A'
        );

        $this->transactionRepo->updateStatus($transactionId, 'success', $note);
        $this->feeRepo->updateStatusToPaid($transaction['fee_id']);
        $this->otpRepo->markAsUsedById($otp['otp_id']);

        $updatedTransaction = $this->transactionRepo->findById($transactionId);
        $user = $this->userRepo->findById($payerId);

        // send confirmation email (wrap in try/catch if you don't want email errors to fail everything)
        try {
            $this->mailService->sendConfirmationEmail(
                $user['email'],
                $updatedTransaction,
                $user,
                $student,
                $fee
            );
        } catch (\Throwable $e) {
            // log but don't fail the whole flow
            error_log("Email send failed: " . $e->getMessage());
        }

        return [
            'success' => true,
            'trans_id' => $updatedTransaction['transaction_id'],
            'user' => $user['full_name'] ?? null,
            'student' => $student['full_name'] ?? null,
            'semester' => $fee['semester'] ?? null,
            'school_year' => $fee['school_year'] ?? null,
            'amount' => $updatedTransaction['amount'] ?? null,
            'date' => $updatedTransaction['created_at'] ?? null,
        ];
    }


    /**
     * Resend OTP for a pending transaction.
     */
    public function resendOtp($payerId, $transactionId) {
        $transaction = $this->transactionRepo->findPendingByIdAndPayer($transactionId, $payerId);
        if (!$transaction) throw new Exception('Invalid or non-pending transaction.');

        $this->otpRepo->markAsUsedByTransactionId($transactionId);
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300);
        $this->otpRepo->create($transactionId, $otp, $expiresAt);

        $user = $this->userRepo->findById($payerId);
        if (!$this->mailService->sendOtpEmail($user['email'], $otp)) {
            throw new Exception('Failed to send OTP.');
        }
        return true;
    }

    /**
     * Retrieve all transactions made by a specific payer.
     */
    public function getTransactionsByPayer($payerId) {
        return $this->transactionRepo->findByPayerId($payerId);
    }

    /**
     * Cancel a pending transaction.
     */
    public function cancelTransaction($payerId, $transactionId) {
        // Cập nhật trạng thái giao dịch luôn thành failed
        $this->transactionRepo->updateStatus(
            $transactionId,
            'failed',
            'User returned to payment page.'
        );

        // Nếu có OTP liên quan, đánh dấu là đã dùng
        $this->otpRepo->markAsUsedByTransactionId($transactionId);

        return [
            'success' => true,
            'message' => 'Transaction marked as failed.',
            'transaction_id' => $transactionId
        ];
    }

}
?>
