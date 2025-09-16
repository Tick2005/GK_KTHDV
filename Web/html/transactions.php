<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /GK_KTHDV/Web/html/index.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <main>
            <div class="container mt-5">
                <a href="/GK_KTHDV/Web/html/home.php" class="btn btn-secondary"><img src="./img/Back-icon.png"></img> Back</a>
                <div class="card shadow-lg p-4">
                    <h2>Transactions</h2>
                        <div class="search-time">
                            <table class="table">
                            <thead>
                                <tr>
                                    <th><label for="start-time">From date:</label></th>
                                    <th><label for="end-time">To date:</label></th>
                                    <th><label for="sort-list">Sort by:</label></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="date" id="start-time" name="start-time"></td>
                                    <td><input type="date" id="end-time" name="end-time"></td>
                                    <td>
                                        <select id="sort-list" name="sort-list">
                                            <option value="newest">Newest time</option>
                                            <option value="oldest">Oldest time</option>
                                            <option value="amount-asc">Ascending Amount</option>
                                            <option value="amount-desc">Descending Amount</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <table class="table table-transactions" id="transactions-table">
                            <thead>
                                <tr>
                                    <th id="transaction-id">Transaction ID</th>
                                    <th id="amount">Amount</th>
                                    <th id="note">Description</th>
                                    <th id="created-at">Created at</th>
                                </tr>
                            </thead>
                            <tbody id="transactions-body">
                            </tbody>
                        </table>
                </div>
            </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="../script.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</body>
</html>