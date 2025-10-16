<?php
// PaymentService.php (ĐÃ SỬA LỖI LOGIC VÀ TÁCH OTP SERVICE)
require_once __DIR__ . '/../repository/TransactionRepository.php';
require_once __DIR__ . '/../repository/FeeRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/StudentRepository.php';
require_once __DIR__ . '/../service/OtpService.php'; // Sử dụng OtpService
require_once __DIR__ . '/../service/MailService.php'; // Vẫn cần MailService để gửi email xác nhận

class PaymentService {
    private $pdo;
    private $transactionRepo;
    private $feeRepo;
    private $userRepo;
    private $studentRepo;
    private $otpService; // Khai báo OtpService
    private $mailService;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->transactionRepo = new TransactionRepository($pdo);
        $this->feeRepo = new FeeRepository($pdo);
        $this->userRepo = new UserRepository($pdo);
        $this->studentRepo = new StudentRepository($pdo);
        $this->otpService = new OtpService($pdo); // Khởi tạo OtpService
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

        // Get ALL unpaid fees
        $fees = $this->feeRepo->getUnpaidFeesByStudentId($studentId);
        if (empty($fees)) throw new Exception('No outstanding tuition fees.');

        $totalAmount = 0;
        $feeIds = [];
        $feeTitles = [];
        foreach ($fees as $fee) {
            $totalAmount += (float)$fee['amount'];
            $feeIds[] = $fee['fee_id'];
            $feeTitles[] = "Semester: {$fee['semester']},School Year: {$fee['school_year']},Amount: {$fee['amount']}, Due-date: {$fee['due_date']}";
        }
        
        if ($totalAmount <= 0) throw new Exception('Total outstanding amount is zero.');
        
        $user = $this->userRepo->findById($payerId);
        if ($user['balance'] < $totalAmount) {
            throw new Exception('Insufficient balance to complete payment.');
        }

        // Tạo transaction
        $feeId = $feeIds[0]; 
        $noteData = [
            'fee_ids' => $feeIds,
            'fee_titles' => $feeTitles,
            'student_id' => $studentId,
        ];
        $noteJson = json_encode($noteData, JSON_UNESCAPED_UNICODE); 
        $transactionId = $this->transactionRepo->create($payerId, $feeId, $totalAmount, 'pending', $noteJson);

        // TẠO VÀ GỬI OTP (Gọi OtpService)
        try {
            $this->otpService->createAndSendNewOtp($transactionId, $payerId);
        } catch (Exception $e) {
            // Nếu gửi mail/tạo OTP lỗi, cần FAIL transaction
            $this->transactionRepo->updateStatus($transactionId, 'failed', 'OTP creation/send failed: ' . $e->getMessage());
            throw new Exception('Failed to send OTP.');
        }

        return $transactionId;
    }


    public function verifyOtp($payerId, $transactionId, $otpCode) {
        // [FIX] QUẢN LÝ TRANSACTION TẠI TẦNG SERVICE (Đã bỏ beginTransaction ở Controller)
        $this->pdo->beginTransaction(); 
        try {
            $transaction = $this->transactionRepo->findPendingByIdAndPayer($transactionId, $payerId);
            
            if (!$transaction) {
                $this->pdo->rollBack(); 
                return [
                    'success' => false, 
                    'message' => 'Invalid, completed, or non-pending transaction. This may be due to a duplicate request or race condition.', 
                    'is_final_fail' => true 
                ];
            }
            
            // Lấy danh sách phí cần thanh toán từ cột note (JSON)
            $noteData = json_decode($transaction['note'] ?? '{}', true);
            $feeIdsToUpdate = $noteData['fee_ids'] ?? [];

            if (empty($feeIdsToUpdate) || !is_array($feeIdsToUpdate)) {
                 $this->transactionRepo->updateStatus($transactionId, 'failed', 'Missing fee list in transaction note.');
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Missing fee list in transaction note.'];
            }

            // [FIX] 1.1) Khóa row tài khoản người nộp tiền
            $this->userRepo->lockForUpdate($payerId);

            // [FIX] 1.2) Khóa TẤT CẢ các row khoản phí
            $this->feeRepo->lockMultipleFeesForUpdate($feeIdsToUpdate);

            // 2-6) Kiểm tra OTP (Gọi OtpService)
            $otpResult = $this->otpService->verifyOtpCode($transactionId, $otpCode);
            
            if (!$otpResult['success']) {
                if ($otpResult['attempts_left'] === 0) {
                    // Nếu hết lượt, transaction được đánh dấu failed (vì không gọi commit ở trên)
                    $this->transactionRepo->updateStatus($transactionId, 'failed', $otpResult['message']);
                }
                // [FIX] COMMIT để lưu lại số lần nhập sai hoặc ROLLBACK nếu transaction đã bị fail
                $this->pdo->commit(); 
                return $otpResult; // Trả về lỗi OTP (sai mã, hết hạn, hết lượt)
            }
            
            $otp = $otpResult['otp'];

            // 7) OK: check balance
            $balance = $this->userRepo->getBalance($payerId);
            if ($balance < $transaction['amount']) {
                $this->transactionRepo->updateStatus($transactionId, 'failed', 'Insufficient funds.');

                $this->pdo->rollBack();

                return [
                    'success' => false,
                    'is_final_fail' => true, 
                    'message' => 'Insufficient funds.'
            ];

            }

            $balance_updated = $this->userRepo->updateBalance($payerId, $transaction['amount']); 

            if (!$balance_updated) {
                $this->pdo->rollBack();
                $this->transactionRepo->updateStatus(
                    $transaction['transaction_id'], 
                    'failed', 
                    'Payment failed: Insufficient balance or database update failed.'
                );
                return [
                    'success' => false, 
                    'is_final_fail' => true,
                    'message' => 'Payment failed: Insufficient balance to complete the transaction.'
                ];

            }
            
            // [FIX] Cập nhật TẤT CẢ các khoản phí thành Paid bằng BATCH UPDATE
            $this->feeRepo->updateMultipleStatusesToPaid($feeIdsToUpdate); 
            // [FIX] Lấy chi tiết phí đã trả
            $paidFeesDetails = $this->feeRepo->findFeesByIds($feeIdsToUpdate);

            // Lấy thông tin sinh viên
            $studentId = $noteData['student_id'] ?? null;
            $student = $this->studentRepo->findById($studentId);

            // [FIX] Cập nhật note CHI TIẾT
            $feeTitles = $noteData['fee_titles'] ?? [];
            $noteContent = implode(', ', $feeTitles);
            
            $note = sprintf(
                "Payment completed for student %s. Total %d fees: %s",
                $student['full_name'] ?? 'N/A',
                count($feeIdsToUpdate),
                $noteContent
            );

            $this->transactionRepo->updateStatus($transactionId, 'success', $note);
            $this->otpService->markOtpAsUsed($otp['otp_id']); // Gọi OtpService

            $updatedTransaction = $this->transactionRepo->findById($transactionId);
            $user = $this->userRepo->findById($payerId);

            // Gửi email xác nhận (Giữ nguyên gọi MailService)
            try {
                $this->mailService->sendConfirmationEmail(
                    $user['email'],
                    $updatedTransaction,
                    $user,
                    $student,
                    $paidFeesDetails
                );
            } catch (\Throwable $e) {
                error_log("Email send failed: " . $e->getMessage());
            }

            // GIAO DỊCH THÀNH CÔNG
            $this->pdo->commit();
            return [
                'success' => true,
                'trans_id' => $updatedTransaction['transaction_id'],
                'user' => $user['full_name'] ?? null,
                'student' => $student['full_name'] ?? null,
                'amount' => $updatedTransaction['amount'] ?? null,
                'date' => $updatedTransaction['created_at'] ?? null,
                'paid_fees' => $paidFeesDetails
            ];
        } catch (\Throwable $e) {
             if ($this->pdo->inTransaction()) {
                 $this->pdo->rollBack();
             }
             throw $e;
        }
    }


    public function resendOtp($payerId, $transactionId) {
        $transaction = $this->transactionRepo->findPendingByIdAndPayer($transactionId, $payerId);
        if (!$transaction) throw new Exception('Invalid or non-pending transaction.');

        // TẠO VÀ GỬI OTP MỚI (Gọi OtpService)
        $this->otpService->createAndSendNewOtp($transactionId, $payerId);
        
        return true;
    }

    public function cancelTransaction($payerId, $transactionId) {
        $this->transactionRepo->updateStatus(
            $transactionId,
            'failed',
            'User returned to payment page.'
        );

        $this->otpService->markOtpAsUsedByTransactionId($transactionId); 

        return [
            'success' => true,
            'message' => 'Transaction marked as failed.',
            'transaction_id' => $transactionId
        ];
    }
    // Trong PaymentService.php

    /**
     * Retrieve all transactions made by a specific payer.
     * @param int $payerId
     * @return array
     */
    public function getTransactionsByPayer($payerId) {
        // LƯU Ý: Đã sửa lỗi chính tả $tran thành $trans nếu bạn có lỗi này
        $transactions = $this->transactionRepo->findByPayerId($payerId);
        
        // Luôn trả về thành công và kèm theo dữ liệu (có thể là mảng rỗng)
        return [
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions 
        ];
    }
}
?>