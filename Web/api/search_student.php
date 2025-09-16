<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Lấy dữ liệu JSON từ body
    $data = json_decode(file_get_contents('php://input'), true);
    $searchId = $data['search_student_id'] ?? '';

    if (!$searchId) {
        echo json_encode(['success' => false, 'message' => 'Missing student ID']);
        exit;
    }

    // Tìm thông tin sinh viên
    $stmt = $pdo->prepare("SELECT student_id, full_name, email, phone FROM students WHERE student_id = ?");
    $stmt->execute([$searchId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }

    // Lấy danh sách học phí chưa đóng
    $stmt = $pdo->prepare("SELECT semester, school_year, amount, due_date 
                           FROM tuitionfees 
                           WHERE student_id = ? AND status = 'unpaid'");
    $stmt->execute([$searchId]);
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_due = 0;
    foreach ($fees as $f) {
        $total_due += $f['amount'];
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