<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
$message = isset($_GET['message']) ? $_GET['message'] : 'An error occurred during the payment process';
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .failure-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            background: #fff;
            padding: 40px 30px;
            animation: fadeIn 0.5s ease-in-out;
        }
        .failure-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            animation: shake 0.6s ease-in-out;
        }
        .failure-title {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.8rem;
        }
        .failure-message {
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
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
    </style>
</head>
<body>
    <main>
        <div class="col-md-6 d-flex align-items-center justify-content-center" style="min-height: 80vh; margin: auto;">
            <div class="failure-card text-center">
                <img src="./img/Fail-icon.png" alt="Failed" class="failure-icon">
                <h2 class="failure-title">Payment Failed</h2>
                <p class="failure-message">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                <p class="text-muted">Please try again or contact customer support for assistance.</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="/Web/ui/payment.php" class="btn btn-primary btn-action">🔄 Try Again</a>
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
