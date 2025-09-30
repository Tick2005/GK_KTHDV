<?php
require_once __DIR__ . '/../database/db.php';

class StudentRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($studentId) {
        $stmt = $this->pdo->prepare('SELECT student_id, full_name, email, phone FROM students WHERE student_id = ?');
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }
}
?>