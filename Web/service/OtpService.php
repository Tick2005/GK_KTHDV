<?php
// OtpService.php
require_once __DIR__ . '/../repository/OtpRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/MailService.php';

class OtpService {
    private $otpRepo;
    private $userRepo;
    private $mailService;

    public function __construct($pdo) {
        $this->otpRepo = new OtpRepository($pdo);
        $this->userRepo = new UserRepository($pdo);
        $this->mailService = new MailService();
    }

    /**
     * Tạo OTP mới cho giao dịch và gửi email.
     * @param int $transactionId
     * @param int $payerId
     * @return string Trả về mã OTP mới
     * @throws Exception
     */
    public function createAndSendNewOtp($transactionId, $payerId) {
        // Vô hiệu hóa OTP cũ (nếu có)
        $this->otpRepo->markAsUsedByTransactionId($transactionId);

        // Tạo OTP mới
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 phút
        $this->otpRepo->create($transactionId, $otpCode, $expiresAt);

        // Lấy email người nộp tiền và gửi
        $user = $this->userRepo->findById($payerId);
        if (!$user) {
            throw new Exception("Payer not found.");
        }

        if (!$this->mailService->sendOtpEmail($user['email'], $otpCode)) {
            throw new Exception('Failed to send OTP email.');
        }

        return $otpCode;
    }

    /**
     * Kiểm tra OTP và trả về thông tin chi tiết của OTP.
     * @param int $transactionId
     * @param string $otpCode
     * @return array
     * @throws Exception
     */
    public function verifyOtpCode($transactionId, $otpCode) {
        $otp = $this->otpRepo->findByTransactionId($transactionId); 
        
        if (!$otp) {
            return ['success' => false, 'message' => 'No OTP found.'];
        }

        if (!empty($otp['is_used']) || strtotime($otp['expires_at']) < time()) { 
            return ['success' => false, 'message' => 'Invalid or expired OTP.', 'attempts_left' => 0];
        }

        $attempts = (int)($otp['attempts'] ?? 0);
        if ($attempts >= 3) {
            return ['success' => false, 'message' => 'Maximum OTP attempts exceeded.', 'attempts_left' => 0];
        }

        // Mã sai: Tăng attempts và tính attempts_left
        if ($otpCode !== $otp['otp_code']) {
            $this->otpRepo->incrementAttempts($otp['otp_id']);
            $attemptsLeft = max(0, 3 - ($attempts + 1));
            // Lưu lại số lần nhập sai
            return [
                'success' => false,
                'message' => 'Incorrect OTP.',
                'attempts_left' => $attemptsLeft
            ];
        }
        
        // Mã đúng
        return ['success' => true, 'otp' => $otp];
    }

    /**
     * Đánh dấu OTP là đã sử dụng.
     * @param int $otpId
     */
    public function markOtpAsUsed($otpId) {
        $this->otpRepo->markAsUsedById($otpId);
    }
    public function markOtpAsUsedByTransactionId($transactionId){
        $this->otpRepo->markAsUsedByTransactionId($transactionId);
    }
}
?>