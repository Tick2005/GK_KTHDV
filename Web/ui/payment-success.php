<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
$result = $_SESSION['payment_result'] ?? null;

// SỬA LỖI: Kiểm tra $result có thành công và có dữ liệu không
if (!$result || $result['success'] !== true) {
    header("Location: /Web/ui/payment-fail.php");
    exit;
}
echo '<script>console.log(' . json_encode($result) . ');</script>';

// Hàm định dạng tiền tệ
function format_vnd($amount) {
    return number_format((float)$amount, 0, ',', '.') . '₫';
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            background: #fff;
            padding: 40px 30px;
            animation: fadeIn 0.5s ease-in-out;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            animation: popIn 0.5s ease-in-out;
        }
        .success-title {
            color: #28a745;
            font-weight: bold;
            font-size: 1.8rem;
        }
        .success-message {
            color: #6c757d;
            margin: 10px 0 20px;
        }
        .btn-action {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            transition: 0.3s;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes popIn {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <main>
        <div class="col-md-8 d-flex align-items-center justify-content-center" style="min-height: 80vh; margin: auto;">
            <div class="success-card text-center w-100">
                <img src="./img/Success-icon.png" alt="Success" class="success-icon">
                <h2 class="success-title">Payment Successful!</h2>
                <p class="success-message">Thank you for completing your transaction.</p>
                
                <div class="text-start mt-4">
                    <h5>Transaction Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($result['trans_id'] ?? 'N/A') ?></p>
                            <p><strong>Payer:</strong> <?= htmlspecialchars($result['user'] ?? 'N/A') ?></p>
                            <p><strong>Student:</strong> <?= htmlspecialchars($result['student'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Amount Paid:</strong> <?= format_vnd($result['amount'] ?? 0) ?></p>
                            <p><strong>Date:</strong> <?= htmlspecialchars($result['date'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mt-4">
                    <h5>Paid Fee Items</h5>
                    <?php if (!empty($result['paid_fees'])): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Amount</th>
                                        <th>Semester</th>
                                        <th>School Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['paid_fees'] as $fee): ?>
                                        <tr>
                                            <td><?= format_vnd($fee['amount'] ?? 0) ?></td> 
                                            <td><?= htmlspecialchars($fee['semester'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($fee['school_year'] ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No individual fee details found.</p>
                    <?php endif; ?>
                </div>      
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="/Web/ui/payment.php" class="btn btn-primary btn-action">💳 Make Another Payment</a>
                    <a href="/Web/ui/transactions.php" class="btn btn-outline-success btn-action">📄 View Transactions</a>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/api.js"></script>
    <script src="./js/payment.js"></script>
    <script src="./js/transactions.js"></script>
</body>
</html>