<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT t.*, tf.semester, tf.school_year, tf.description 
                           FROM transactions t JOIN tuitionfees tf ON t.fee_id = tf.fee_id 
                           WHERE t.payer_id = ? ORDER BY t.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $trans = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $trans]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>