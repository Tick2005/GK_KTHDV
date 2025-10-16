<?php
// FeeRepository.php (ĐÃ BỔ SUNG CÁC HÀM BATCH)
require_once __DIR__ . '/../database/db.php';

class FeeRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUnpaidFeesByStudentId($studentId) {
        $stmt = $this->pdo->prepare('SELECT * FROM tuitionfees WHERE student_id = ? AND status = "unpaid" ORDER BY due_date ASC');
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // [HÀM MỚI] Khóa NHIỀU row khoản phí để tránh xung đột
    public function lockMultipleFeesForUpdate(array $feeIds) {
        if (empty($feeIds)) return;
        $placeholders = implode(',', array_fill(0, count($feeIds), '?'));
        // SELECT... FOR UPDATE là cơ chế khóa chính
        $stmt = $this->pdo->prepare("SELECT fee_id FROM tuitionfees WHERE fee_id IN ($placeholders) FOR UPDATE");
        $stmt->execute($feeIds);
    }
    
    // [HÀM MỚI] Cập nhật trạng thái của NHIỀU khoản phí thành 'paid' trong một query (Batch Update).
    public function updateMultipleStatusesToPaid(array $feeIds) {
        if (empty($feeIds)) return true;
        $placeholders = implode(',', array_fill(0, count($feeIds), '?'));
        
        $stmt = $this->pdo->prepare("UPDATE tuitionfees SET status = 'paid' WHERE fee_id IN ($placeholders)");
        return $stmt->execute($feeIds);
    }

    // [HÀM MỚI] Lấy chi tiết NHIỀU khoản phí theo ID.
    public function findFeesByIds(array $feeIds) {
        if (empty($feeIds)) return [];
        $placeholders = implode(',', array_fill(0, count($feeIds), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM tuitionfees WHERE fee_id IN ($placeholders)");
        $stmt->execute($feeIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Các hàm cũ:
    public function updateStatusToPaid($feeId) {
        $stmt = $this->pdo->prepare('UPDATE tuitionfees SET status = "paid" WHERE fee_id = ?');
        return $stmt->execute([$feeId]);
    }

    public function findById($feeId) {
        $stmt = $this->pdo->prepare('SELECT * FROM tuitionfees WHERE fee_id = ?');
        $stmt->execute([$feeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về 1 record hoặc null
    }

    public function lockFeeForUpdate($feeId) {
        $stmt = $this->pdo->prepare('SELECT fee_id FROM tuitionfees WHERE fee_id = ? FOR UPDATE');
        $stmt->execute([$feeId]);
        return $stmt->fetch();
    }
}
?>