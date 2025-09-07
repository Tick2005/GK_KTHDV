<?php
session_start();
include 'db.php';

// Secure session handling
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// Regenerate session ID every 30 minutes
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$student_id = $_SESSION['student_id'];

try {
    // ✅ Lấy thông tin người nộp (payer)
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $payer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payer) {
        session_destroy();
        header("Location: index.php?error=Invalid user");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Biến dữ liệu hiển thị
$student = null;   // Người được đóng học phí
$fees = [];
$total_due = 0;
$error = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tìm kiếm sinh viên
    if (isset($_POST['search_student_id'])) {
        $search_id = trim($_POST['search_student_id']);
        if (strlen($search_id) !== 8) {
            $error = "MSSV phải có 8 ký tự.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$search_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $stmt = $pdo->prepare("SELECT * FROM tuitionfees 
                                       WHERE student_id = ? AND status = 'unpaid' 
                                       ORDER BY due_date ASC");
                $stmt->execute([$search_id]);
                $fees_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $current_date = new DateTime('now', new DateTimeZone('+07:00'));
                foreach ($fees_raw as $f) {
                    $due_date = new DateTime($f['due_date']);
                    if ($due_date >= $current_date) {
                        $fees[] = $f;
                        $total_due += $f['amount'];
                    }
                }
                if ($total_due <= 0) {
                    $error = "Sinh viên này không có học phí cần thanh toán.";
                }
            } else {
                $error = "Không tìm thấy sinh viên.";
            }
        }
    }

    // 2. Thanh toán toàn bộ
    elseif (isset($_POST['pay_all'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "CSRF token không hợp lệ.";
        } elseif (!isset($_POST['agree_terms'])) {
            $error = "Bạn cần đồng ý với điều khoản.";
        } else {
            $target_id = trim($_POST['target_student_id'] ?? '');

            // Lấy sinh viên mục tiêu
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$target_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $stmt = $pdo->prepare("SELECT * FROM tuitionfees 
                                       WHERE student_id = ? AND status = 'unpaid' 
                                       ORDER BY due_date ASC");
                $stmt->execute([$target_id]);
                $fees_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $current_date = new DateTime('now', new DateTimeZone('+07:00'));
                foreach ($fees_raw as $f) {
                    $due_date = new DateTime($f['due_date']);
                    if ($due_date >= $current_date) {
                        $fees[] = $f;
                        $total_due += $f['amount'];
                    }
                }

                if ($total_due <= 0) {
                    $error = "Không có học phí cần thanh toán.";
                } elseif ($payer['balance'] < $total_due) {
                    // Không đủ tiền
                    header("Location: payment-fail.php?reason=insufficient_funds&amount=$total_due&balance={$payer['balance']}");
                    exit;
                } else {
                    try {
                        $pdo->beginTransaction();

                        // Tạo transaction chính
                        $stmt = $pdo->prepare("INSERT INTO transactions 
                            (payer_id, payee_id, amount, status, created_at) 
                            VALUES (?, ?, ?, 'pending', NOW())");
                        $stmt->execute([$payer['id'], $student['id'], $total_due]);
                        $transaction_id = $pdo->lastInsertId();

                        // Chi tiết học phí
                        foreach ($fees as $f) {
                            $stmt = $pdo->prepare("INSERT INTO transaction_details 
                                (transaction_id, tuition_id, amount) 
                                VALUES (?, ?, ?)");
                            $stmt->execute([$transaction_id, $f['id'], $f['amount']]);
                        }

                        // Tạo OTP
                        $otp_code = rand(100000, 999999);
                        $stmt = $pdo->prepare("INSERT INTO otps 
                            (transaction_id, otp_code, expires_at) 
                            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 MINUTE))");
                        $stmt->execute([$transaction_id, $otp_code]);

                        $pdo->commit();

                        header("Location: otp.php?tid=$transaction_id");
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        header("Location: payment-fail.php?reason=database_error&message=" . urlencode($e->getMessage()));
                        exit;
                    }
                }
            } else {
                $error = "Không tìm thấy sinh viên để thanh toán.";
            }
        }
    }
}

// Regenerate CSRF token mỗi lần load lại
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iBanking TDTU - Nộp học phí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
    <style>
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        .search-box input {
            padding: 12px;
            border: 2px solid #0052cc;
            border-radius: 10px;
            font-size: 1rem;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .search-box input:focus {
            border-color: #003d99;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 82, 204, 0.5);
        }
        .terms-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .terms-box h5 {
            color: #1a3c6d;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .terms-box p {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
        }
        .info-input {
            background: #e9ecef;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: 700;
            color: #1a3c6d;
            border: none;
        }
        .payment-section {
            display: <?php echo ($student && !empty($fees)) ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>
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
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center mb-4">NỘP HỌC PHÍ</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="search-box">
                <form method="POST" id="search-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="text" name="search_student_id" class="form-control" maxlength="8" pattern="[A-Za-z0-9]{8}" title="MSSV phải đúng 8 ký tự" required placeholder="Nhập MSSV (8 ký tự)" oninput="checkLength(this)">
                </form>
            </div>

            <h5>Thông tin người nộp:</h5>
            <div class="mb-3">
                <input type="text" class="form-control info-input" value="Mã sinh viên: <?php echo htmlspecialchars($payer['student_id']); ?>" readonly>
                <input type="text" class="form-control info-input" value="Họ tên: <?php echo htmlspecialchars($payer['full_name']); ?>" readonly>
                <input type="text" class="form-control info-input" value="Email: <?php echo htmlspecialchars($payer['email']); ?>" readonly>
                <input type="text" class="form-control info-input" value="SĐT: <?php echo htmlspecialchars($payer['phone']); ?>" readonly>
                <input type="text" class="form-control info-input" value="Số dư: <?php echo number_format($payer['balance'], 0, ',', '.'); ?>₫" readonly>
            </div>

            <?php if ($student): ?>
                <h5 class="mt-4">Thông tin người được đóng:</h5>
                <div class="mb-3">
                    <input type="text" class="form-control info-input" value="Mã sinh viên: <?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                    <input type="text" class="form-control info-input" value="Họ tên: <?php echo htmlspecialchars($student['full_name']); ?>" readonly>
                    <input type="text" class="form-control info-input" value="Email: <?php echo htmlspecialchars($student['email']); ?>" readonly>
                    <input type="text" class="form-control info-input" value="SĐT: <?php echo htmlspecialchars($student['phone']); ?>" readonly>
                </div>

                <h5>Chi tiết học phí:</h5>
                <?php if (empty($fees)): ?>
                    <div class="alert alert-info">Không có học phí nào chưa đóng hoặc đã quá hạn.</div>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Học kỳ</th>
                                <th>Năm học</th>
                                <th>Số tiền</th>
                                <th>Hạn đóng</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($fees as $f): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($f['semester']); ?></td>
                                <td><?php echo htmlspecialchars($f['school_year']); ?></td>
                                <td><?php echo number_format($f['amount'], 0, ',', '.'); ?>₫</td>
                                <td><?php echo htmlspecialchars($f['due_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="mt-3"><strong>Tổng số tiền cần thanh toán: <?php echo number_format($total_due, 0, ',', '.'); ?>₫</strong></p>

                    <div class="terms-box">
                        <h5>Điều khoản & Điều kiện</h5>
                        <p>Bằng cách thanh toán, bạn đồng ý với các điều khoản dịch vụ của TDTU iBanking. Thanh toán toàn bộ phí một lần, không hoàn tiền. Bạn cam kết rằng thông tin cung cấp là chính xác và chịu trách nhiệm pháp lý nếu có sai sót. Hệ thống có quyền từ chối giao dịch nếu phát hiện gian lận. Điều khoản này áp dụng cho tất cả người dùng và có thể được cập nhật mà không cần thông báo trước. Vui lòng đọc kỹ trước khi đồng ý.</p>
                    </div>

                    <div class="payment-section">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="target_student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">

                            <input type="hidden" name="pay_all" value="1">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">Tôi đồng ý với điều khoản</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Thanh toán toàn bộ</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="./script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkLength(input) {
            if (input.value.length === 8) {
                document.getElementById('search-form').submit();
            }
        }
    </script>
</body>
</html>