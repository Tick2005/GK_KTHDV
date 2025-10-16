<?php
require_once __DIR__ . '/../database/db.php';

class TransactionRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($payerId, $feeId, $amount, $status = 'pending', $note = '') {
        $stmt = $this->pdo->prepare('INSERT INTO transactions (payer_id, fee_id, amount, status, note, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$payerId, $feeId, $amount, $status, $note]);
        return $this->pdo->lastInsertId();
    }

    public function updateStatus($transactionId, $status, $note = null) {
        try {
            if ($note !== null) {
                $stmt = $this->pdo->prepare('UPDATE transactions SET status = ?, note = ? WHERE transaction_id = ?');
                return $stmt->execute([$status, $note, $transactionId]);
            } else {
                $stmt = $this->pdo->prepare('UPDATE transactions SET status = ? WHERE transaction_id = ?');
                return $stmt->execute([$status, $transactionId]);
            }
        } catch (Exception $e) {
            error_log("Failed to update transaction status: " . $e->getMessage());
            return false;
        }
    }

    public function findById($transactionId) {
        $stmt = $this->pdo->prepare('SELECT * FROM transactions WHERE transaction_id = ?');
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }

    public function findByPayerId($payerId) {
        $stmt = $this->pdo->prepare('SELECT * FROM transactions WHERE payer_id = ? ORDER BY created_at DESC');
        $stmt->execute([$payerId]);
        return $stmt->fetchAll();
    }

    public function findPendingByIdAndPayer($transactionId, $payerId) {
        $stmt = $this->pdo->prepare('SELECT * FROM transactions WHERE transaction_id = ? AND payer_id = ? AND status = "pending"');
        $stmt->execute([$transactionId, $payerId]);
        return $stmt->fetch();
    }

     public function findPendingByStudentId($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT t.*
            FROM transactions t
            JOIN tuitionfees f ON t.fee_id = f.fee_id
            WHERE f.student_id = :student_id
              AND t.status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>