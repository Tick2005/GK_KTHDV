<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../service/StudentService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$studentId = $input['search_student_id'] ?? '';

try {
    $studentService = new StudentService($pdo);
    $data = $studentService->searchStudent($studentId);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>