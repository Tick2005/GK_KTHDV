<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if ($input['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}
session_destroy();
echo json_encode(['success' => true]);
?>