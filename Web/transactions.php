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

    // Fetch transactions for the current student
    $stmt = $pdo->prepare("SELECT t.*, tf.semester, tf.school_year, tf.description 
                           FROM transactions t 
                           JOIN tuitionfees tf ON t.fee_id = tf.fee_id 
                           WHERE t.payer_id = ? 
                           ORDER BY t.created_at DESC");
    $stmt->execute([$student_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iBanking TDTU - Lịch sử giao dịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center mb-4">LỊCH SỬ GIAO DỊCH</h2>


            <?php if (empty($transactions)): ?>
                <div class="alert alert-info">Không có giao dịch nào.</div>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Mã giao dịch</th>
                            <th>Học kỳ</th>
                            <th>Năm học</th>
                            <th>Mô tả</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['transaction_id']); ?></td>
                                <td><?php echo htmlspecialchars($t['semester']); ?></td>
                                <td><?php echo htmlspecialchars($t['school_year']); ?></td>
                                <td><?php echo htmlspecialchars($t['description']); ?></td>
                                <td><?php echo number_format($t['amount'], 0, ',', '.'); ?>₫</td>
                                <td>
                                    <?php if ($t['status'] === 'success'): ?>
                                        <span class="badge bg-success">Thành công</span>
                                    <?php elseif ($t['status'] === 'failed'): ?>
                                        <span class="badge bg-danger">Thất bại</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="home.php" class="btn btn-primary">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>