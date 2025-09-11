<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
session_start();
include '../db.php';

// Suppress warnings/notices to prevent HTML output
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Check for POST request and authenticated user
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Lấy dữ liệu JSON từ body
$input = json_decode(file_get_contents('php://input'), true);

$csrf_token = $input['csrf_token'] ?? null;
$icon = $input['icon'] ?? null;

// Validate CSRF token
if (!$csrf_token) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'CSRF token not found in request']);
    exit;
}
if ($csrf_token !== $_SESSION['csrf_token']) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF']);
    exit;
}

try {
    // Validate icon
    if (!$icon) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'No icon provided']);
        exit;
    }

    $allowed_icons = ['default.png', 'ava_a.png', 'ava_b.png', 'ava_c.png'];
    if (!in_array($icon, $allowed_icons)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid icon']);
        exit;
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE customer SET user_icon = ? WHERE user_id = ?");
    $stmt->execute([$icon, $_SESSION['user_id']]);

    ob_end_clean();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}