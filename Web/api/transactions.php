<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

// Gửi lỗi JSON đúng format
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

if (!isset($_SESSION['user_id'])) {
    sendError('Unauthorized', 401);
}

try {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT 
            transaction_id,
            amount,
            status,
            note,
            created_at
        FROM 
            transactions
        WHERE 
            payer_id = ?
        ORDER BY 
            created_at DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $transactions]);
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}