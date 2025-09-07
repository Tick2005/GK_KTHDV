<?php
session_start();
include 'db.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token!";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Vui lòng nhập đầy đủ thông tin!";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM students WHERE username = ?");
                $stmt->execute([$username]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$student) {
                    $error = "Tên đăng nhập không tồn tại!";
                } elseif (!password_verify($password, $student['password_hash'])) {
                    $error = "Mật khẩu sai!";
                } else {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);
                    $_SESSION['student_id'] = $student['student_id'];
                    $_SESSION['full_name'] = $student['full_name'];
                    // Generate new CSRF token
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header("Location: home.php");
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iBanking TDTU - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4 login-container">
            <div class="row">
                <div class="col-md-6 left text-center">
                    <img src="img/Logo-TDTU.png" alt="Logo TDTU">
                    <h2>iBanking TDTU</h2>
                </div>
                <div class="col-md-6 right">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>