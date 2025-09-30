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

    public function updateStatusToPaid($feeId) {
        $stmt = $this->pdo->prepare('UPDATE tuitionfees SET status = "paid" WHERE fee_id = ?');
        return $stmt->execute([$feeId]);
    }

    public function findById($feeId) {
        $stmt = $this->pdo->prepare('SELECT * FROM tuitionfees WHERE fee_id = ?');
        $stmt->execute([$feeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về 1 record hoặc null
    }
}
?>