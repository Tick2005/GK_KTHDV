<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
$message = isset($_GET['message']) ? $_GET['message'] : 'Đã xảy ra lỗi';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div id="fail-message"><?php echo htmlspecialchars($message); ?></div>
    <a href="/GK_KTHDV/Web/html/home.php" class="btn btn-secondary">Back to Home</a>
    <script src="../script.js"></script>
</body>
</html>