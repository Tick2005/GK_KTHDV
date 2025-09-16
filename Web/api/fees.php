<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$sid = $_GET['student_id'] ?? '';
if (strlen($sid) !== 8) {
    echo json_encode(['success' => false, 'message' => 'Invalid student_id']);
    exit;
}

try {
    // Lấy thông tin sinh viên
    $stmt = $pdo->prepare("SELECT student_id, full_name, email, phone FROM students WHERE student_id = ?");
    $stmt->execute([$sid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) throw new Exception('Student not found');

    // Lấy học phí chưa đóng
    $stmt = $pdo->prepare("SELECT * FROM tuitionfees WHERE student_id = ? AND status = 'unpaid' ORDER BY due_date ASC");
    $stmt->execute([$sid]);
    $fees_raw = $stmt->fetchAll();

    $fees = [];
    $total_due = 0;
    $current_date = new DateTime();

    foreach ($fees_raw as $f) {
        $due_date = new DateTime($f['due_date']);
        if ($due_date >= $current_date) {
            $fees[] = $f;
            $total_due += (float)$f['amount'];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'student' => $student,
            'fees' => $fees,
            'total_due' => $total_due
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}