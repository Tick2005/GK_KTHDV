<?php
session_start();
include 'db.php';

// Secure session handling
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// Regenerate session ID periodically (every 30 minutes)
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
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

// Generate CSRF token for logout
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle icon update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_icon'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token!";
    } else {
        $new_icon = $_POST['icon'];
        $stmt = $pdo->prepare("UPDATE students SET student_icon = ? WHERE student_id = ?");
        $stmt->execute([$new_icon, $student_id]);
        header("Location: home.php");
        exit;
    }
}

// List icon mẫu
$icons = ['default.png', 'ava_a.png', 'ava_b.png', 'ava_c.png'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iBanking TDTU - Trang chủ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="./style.css">
</head>
<body class="home">
    <header class="header bg-primary text-white p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <img src="img/Logo-TDTU.png" alt="Logo TDTU" class="logo">
            <h2 class="m-0">iBanking TDTU</h2>
        </div>
        <form action="logout.php" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit" class="btn btn-danger" id="logout-btn">Đăng xuất</button>
        </form>
    </header>

    <div class="container mt-4">
        <div class="user-info shadow-lg text-center">
                <img src="img/icon/<?php echo htmlspecialchars($student['student_icon']); ?>" alt="Ảnh đại diện" class="icon-preview">
                <form method="POST" class="icon-container">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="update_icon" value="1">
                    <select name="icon" class="icon-select">
                        <?php foreach ($icons as $ic): ?>
                            <option value="<?php echo htmlspecialchars($ic); ?>" <?php if ($student['student_icon'] == $ic) echo 'selected'; ?>><?php echo htmlspecialchars($ic); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="update-btn">Cập nhật ảnh</button>
                </form>
                <p>Xin chào, <strong><?php echo htmlspecialchars($student['full_name']); ?></strong></p>
                <p>Email: <strong><?php echo htmlspecialchars($student['email']); ?></strong></p>
                <p>SĐT: <strong><?php echo htmlspecialchars($student['phone']); ?></strong></p>
            <div class="user-balance mt-3">
                <p class="text-white">Số dư tài khoản:</p>
                <span id="balance" class="fw-bold fs-4 text-white" data-original="<?php echo number_format($student['balance'], 0, ',', '.'); ?>₫">
                    *********
                </span>
                <button id="toggle-balance" class="btn btn-link text-white">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>
        </div>

        <div class="row home-container mt-4 justify-content-center">
            <div class="col-md-4 nav-item card shadow m-2">
                <img src="img/Pay-tuition-icon.png" alt="Nộp học phí">
                <a href="payment.php" class="btn btn-primary w-100">Nộp học phí</a>
            </div>
            <div class="col-md-4 nav-item card shadow m-2">
                <img src="img/History-icon.png" alt="Lịch sử giao dịch">
                <a href="transactions.php" class="btn btn-primary w-100">Lịch sử giao dịch</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logout-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Xác nhận</h5>
                </div>
                <div class="modal-body">Bạn chắc chắn muốn thoát?</div>
                <div class="modal-footer">
                    <button type="submit" form="logout-form" id="yes-btn" class="btn btn-primary">Yes</button>
                    <button id="no-btn" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./script.js"></script>
</body>
</html>