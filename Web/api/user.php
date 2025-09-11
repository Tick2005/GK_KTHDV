<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, phone, balance, user_icon 
                           FROM customer WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $customer]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}