<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header bg-primary text-white p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <img src="./img/Logo-TDTU.png" alt="Logo TDTU" class="logo">
            <h2 class="m-0">iBanking TDTU</h2>
        </div>
        <form action="/GK_KTHDV/Web/api/logout.php" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit" class="btn btn-danger" id="logout-btn">Đăng xuất</button>
        </form>
    </header>
    <script src="../script.js"></script>
</body>
</html>