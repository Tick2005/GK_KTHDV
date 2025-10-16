<?php
require_once __DIR__ . '/../database/db.php';

class OtpRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Tạo OTP mới
    public function create($transactionId, $otpCode, $expiresAt) {
       $stmt = $this->pdo->prepare(
            'INSERT INTO otps (transaction_id, otp_code, expires_at, attempts, is_used) 
            VALUES (?, ?, ?, 0, 0)'
        );

        return $stmt->execute([$transactionId, $otpCode, $expiresAt]);
    }

    // Đánh dấu OTP đã dùng
    public function markAsUsedByTransactionId($transactionId) {
        $stmt = $this->pdo->prepare('UPDATE otps SET is_used = 1 WHERE transaction_id = ?');
        return $stmt->execute([$transactionId]);
    }

    public function markAsUsedById($otpId) {
        $stmt = $this->pdo->prepare('UPDATE otps SET is_used = 1 WHERE otp_id = ?');
        return $stmt->execute([$otpId]);
    }

    // Lấy OTP còn hiệu lực theo mã
    public function findValidByTransactionIdAndCode($transactionId, $otpCode) {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM otps 
            WHERE transaction_id = ? 
            AND otp_code = ? 
            AND expires_at < NOW() 
            AND is_used = 0 
            LIMIT 1'
        );
        $stmt->execute([$transactionId, $otpCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
    public function findByTransactionId($transactionId){
        $stmt = $this->pdo->prepare(
            'SELECT * FROM otps
            WHERE transaction_id=?
            AND expires_at < NOW()
            AND is_used=0
            LIMIT 1'
        );
        $stmt->execute([$transactionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Lấy OTP mới nhất của transaction
    public function findLatestByTransactionId($transactionId) {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM otps 
            WHERE transaction_id = ? 
            ORDER BY otp_id DESC 
            LIMIT 1'
        );
        $stmt->execute([$transactionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    // Tăng attempts khi nhập sai
    public function incrementAttempts($otpId) {
        $stmt = $this->pdo->prepare('UPDATE otps SET attempts = attempts + 1 WHERE otp_id = ?');
        return $stmt->execute([$otpId]);
    }

    // Cập nhật số attempts cụ thể
    public function updateAttempts($otpId, $attempts) {
        $stmt = $this->pdo->prepare('UPDATE otps SET attempts = ? WHERE otp_id = ?');
        return $stmt->execute([$attempts, $otpId]);
    }
}
?>
