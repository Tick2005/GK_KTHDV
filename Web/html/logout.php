<?php
session_start();

// Hủy session
$_SESSION = [];
session_destroy();

// Tạo token mới nếu cần (optional, để tránh reuse session cũ)
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng xuất</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <div class="logout-box">
    <h2>You have successfully signed out</h2>
    <p>You will be automatically redirected to the homepage in <span id="countdown" class="countdown">60</span> seconds.</p>
    <div class="mt-3">
    <a href="/GK_KTHDV/Web/html/index.php" class="btn btn-secondary"><img src="./img/Logout-icon.png">Logout Now</a>
    </div>
  </div>
   <script src="../script.js"></script>
</body>
</html>
