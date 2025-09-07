<?php
session_start();
include 'db.php';

// Secure session handling
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        session_destroy();
        header("Location: index.php?error=Invalid user");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$reason = $_GET['reason'] ?? 'unknown';
$amount = $_GET['amount'] ?? 0;
$message = $_GET['message'] ?? 'Có lỗi xảy ra trong quá trình thanh toán.';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iBanking TDTU - Thanh toán thất bại</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4 text-center">
            <h2 class="text-danger mb-4">THANH TOÁN THẤT BẠI</h2>

            <div class="mb-3">
                <h5>Thông tin người dùng:</h5>
                <input type="text" class="form-control info-input" value="Mã sinh viên: <?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                <input type="text" class="form-control info-input" value="Họ tên: <?php echo htmlspecialchars($student['full_name']); ?>" readonly>
                <input type="text" class="form-control info-input" value="Số dư: <?php echo number_format($student['balance'], 0, ',', '.'); ?>₫" readonly>
            </div>

            <?php if ($reason === 'insufficient_funds'): ?>
                <div class="alert alert-danger">
                    Số dư không đủ để thanh toán số tiền <?php echo number_format($amount, 0, ',', '.'); ?>₫. Vui lòng nạp thêm tiền!
                </div>
            <?php elseif ($reason === 'database_error'): ?>
                <div class="alert alert-danger">
                    Lỗi hệ thống: <?php echo htmlspecialchars($message); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    Thanh toán thất bại do lỗi không xác định. Vui lòng thử lại!
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="payment.php" class="btn btn-primary">Thử lại</a>
                <a href="home.php" class="btn btn-secondary ms-2">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>