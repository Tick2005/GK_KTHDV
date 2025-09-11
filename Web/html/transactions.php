<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <a href="/GK_KTHDV/Web/html/home.php" class="btn btn-secondary">Back to Home</a>
    <h2>LỊCH SỬ GIAO DỊCH</h2>
    <select id="semester-filter">
        <option value="HK1-2024">Học kỳ 1 - 2024</option>
        <option value="HK2-2024">Học kỳ 2 - 2024</option>
        <option value="HK1-2025">Học kỳ 1 - 2025</option>
    </select>
    <table class="table">
        <thead>
            <tr><th>Ngày</th><th>Nội dung</th><th>Số tiền</th></tr>
        </thead>
        <tbody id="transaction-table"></tbody>
    </table>
    <script src="../script.js"></script>
</body>
</html>