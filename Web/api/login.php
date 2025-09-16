<?php
header('Content-Type: application/json');
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username']);
$password = $input['password'];
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE username = ?");
    $stmt->execute([$username]);
    $customer = $stmt->fetch();
    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        throw new Exception('Invalid credentials');
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $customer['user_id'];
    $_SESSION['full_name'] = $customer['full_name'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo json_encode(['success' => true, 'csrf_token' => $_SESSION['csrf_token'], 'user' => $customer]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>