<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4 login-container">
            <div class="row align-items-center">
                <div class="col-md-6 left">
                    <div class="logo-title">
                        <img src="./img/Logo-TDTU.png" alt="Logo TDTU" class="logo">
                        <h2 class="title">iBanking TDTU</h2>
                    </div>
                </div>
                <div class="col-md-6 right">
                    <form id="login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group mb-4">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Nhập tên đăng nhập">
                        </div>
                        <div class="form-group mb-4">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Nhập mật khẩu">
                        </div>
                        <div id="error-alert" class="alert alert-danger d-none"></div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../script.js"></script>
</body>
</html>