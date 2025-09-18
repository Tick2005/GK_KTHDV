<?php
session_start();
$trans_id = isset($_GET['trans_id']) ? $_GET['trans_id'] : '';
if ($trans_id) {
    // Lấy student_id từ transaction nếu chưa có trong session
    if (!isset($_SESSION['student_id'])) {
        include_once '../db.php';
        $stmt = $pdo->prepare('SELECT t.fee_id, f.student_id FROM transactions t JOIN tuitionfees f ON t.fee_id = f.fee_id WHERE t.transaction_id = ?');
        $stmt->execute([$trans_id]);
        $row = $stmt->fetch();
        if ($row && isset($row['student_id'])) {
            $_SESSION['student_id'] = $row['student_id'];
        } else {
            header('Location: /GK_KTHDV/Web/html/index.php');
            exit;
        }
    }
} else if (!isset($_SESSION['student_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <form id="otp-form">
        <input type="text" name="otp" id="otp" maxlength="6" required>
        <input type="hidden" name="transaction_id" id="trans_id" value="<?php echo htmlspecialchars($trans_id); ?>">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit" class="btn btn-success">Verify OTP</button>
        <button type="button" id="resend-otp" class="btn btn-secondary">Resend OTP</button>
        <div id="otp-timer">300s</div>
        <div id="otp-alert" class="alert d-none"></div>
    </form>
    <a href="/GK_KTHDV/Web/html/home.php" class="btn btn-secondary">Back to Home</a>
    <script src="../script.js"></script>
</body>
</html>
