<?php
require_once __DIR__ . '/../repository/StudentRepository.php';
require_once __DIR__ . '/../repository/FeeRepository.php';

class StudentService {
    private $studentRepo;
    private $feeRepo;

    public function __construct($pdo) {
        $this->studentRepo = new StudentRepository($pdo);
        $this->feeRepo = new FeeRepository($pdo);
    }

    public function searchStudent($studentId) {
        if (strlen($studentId) !== 8) {
            throw new Exception('Invalid student ID');
        }
        $student = $this->studentRepo->findById($studentId);
        if (!$student) {
            throw new Exception('Student not found');
        }
        $fees = $this->feeRepo->getUnpaidFeesByStudentId($studentId);
        $totalDue = array_reduce($fees, function($carry, $item) {
            return $carry + (float)$item['amount'];
        }, 0);
        return ['student' => $student, 'fees' => $fees, 'total_due' => $totalDue];
    }
}
?>