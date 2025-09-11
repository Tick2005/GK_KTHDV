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