<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
$trans_id = isset($_GET['trans_id']) ? $_GET['trans_id'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="alert alert-success">
        <h2>Thanh toán thành công!</h2>
        <p>Mã giao dịch: <?php echo htmlspecialchars($trans_id); ?></p>
        <p>Chi tiết giao dịch đã được gửi đến email của bạn.</p>
    </div>
    <a href="/GK_KTHDV/Web/html/home.php" class="btn btn-secondary">Back to Home</a>
    <script src="../script.js"></script>
</body>
</html>