<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
$trans_id = isset($_GET['trans_id']) ? $_GET['trans_id'] : '';
if (!$trans_id) {
    header('Location: /Web/ui/transactions.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cancel Transaction</title>
    <link rel="stylesheet" href="../ui/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <main>
        <div class="container mt-3">
            <a href="/Web/ui/transactions.php" class="btn btn-secondary"><img src="./img/Back-icon.png"> Back</a>
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Cancel Transaction</h2>
                <p>Are you sure you want to cancel transaction ID: <strong><?php echo htmlspecialchars($trans_id); ?></strong>?</p>
                <form id="cancel-form">
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" id="transaction_id" name="transaction_id" value="<?php echo htmlspecialchars($trans_id); ?>">
                    <button type="submit" class="btn btn-danger w-100">Confirm Cancel</button>
                    <div id="cancel-alert" class="alert alert-danger d-none mt-2"></div>
                </form>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../ui/js/api.js"></script>
    <script src="../ui/js/cancel_transaction.js"></script>
</body>
</html>