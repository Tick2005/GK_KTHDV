<?php
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

    // Update status to paid for a single fee
    public function updateStatusToPaid($feeId) {
        $stmt = $this->pdo->prepare('UPDATE tuitionfees SET status = "paid" WHERE fee_id = ?');
        return $stmt->execute([$feeId]);
    }

    // Update status to paid for multiple fees
    public function updateMultipleStatusToPaid(array $feeIds) {
        if (empty($feeIds)) return false;
        $placeholders = implode(',', array_fill(0, count($feeIds), '?'));
        $sql = "UPDATE tuitionfees SET status = 'paid' WHERE fee_id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($feeIds);
    }

    public function findById($feeId) {
        $stmt = $this->pdo->prepare('SELECT * FROM tuitionfees WHERE fee_id = ?');
        $stmt->execute([$feeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về 1 record hoặc null
    }

    // Khóa row khoản phí để tránh hai giao dịch đồng thời cho cùng một khoản phí
    public function lockFeeForUpdate($feeId) {
        $stmt = $this->pdo->prepare('SELECT fee_id FROM tuitionfees WHERE fee_id = ? FOR UPDATE');
        $stmt->execute([$feeId]);
        return $stmt->fetch();
    }
}
?>
