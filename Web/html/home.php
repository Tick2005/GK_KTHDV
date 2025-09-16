<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
include 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</head>
<body >
    <main>
        <div class="container mt-4">
    <div class="user-info shadow-lg text-center">
        <img id="user-icon" src="" alt="User Icon" class="icon-preview">
        <form method="POST" class="icon-container">
            <input type="hidden" id="csrf_token" name="csrf_token"
       value="<?php echo isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : ''; ?>">


            <input type="hidden" name="update_icon" value="1">
            <select name="icon" class="icon-select">

                <!-- Options will be populated dynamically via JavaScript or server-side -->
            </select>
        </form>
        <p id="user-name">Hi, <strong id="user-name"></strong></p>
        <p id="user-email">Email: <strong id="user-email"></strong></p>
        <p id="user-phone">Phone number: <strong id="user-phone"></strong></p>
        <div class="user-balance mt-3">
            <p class="text-white">Account Balance:</p>
            <span id="balance" class="fw-bold fs-4 text-white" data-original="0₫">*********</span>
            <button id="toggle-balance" class="btn btn-link text-white">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <div class="row home-container mt-4 justify-content-center">
        <div class="col-md-4 nav-item card shadow m-2">
            <img src="./img/Pay-tuition-icon.png" alt="Pay Tuition">
            <a href="/GK_KTHDV/Web/html/payment.php" class="btn btn-primary w-100">Pay Now</a>
        </div>
        <div class="col-md-4 nav-item card shadow m-2">
            <img src="./img/History-icon.png" alt="Transaction History">
            <a href="/GK_KTHDV/Web/html/transactions.php" class="btn btn-primary w-100">View Transactions</a>
        </div>
    </div>
    <div id="skeleton" style="display: block;">Loading...</div>
    <div id="content" style="display: none;">
        <!-- Nội dung sẽ được load bằng JavaScript -->
    </div>
</div>
    </main>
    
<?php include 'footer.php'; ?>
    <script src="../script.js"></script>
</body>
</html>