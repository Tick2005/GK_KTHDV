<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location:/Web/ui/index.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.3/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Card */
.card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    animation: fadeIn 0.5s ease-in-out;
    background-color: #ffffff;
    padding: 2rem;
}

/* Heading */
h2 {
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Filter section */
.search-time {
    margin-bottom: 25px;
}
.search-time table td {
    padding: 8px;
}
.search-time input,
.search-time select {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 6px 12px;
    transition: border-color 0.3s, box-shadow 0.3s;
}
.search-time input:focus,
.search-time select:focus {
    border-color: #28a745;
    box-shadow: 0 0 5px rgba(40,167,69,0.3);
}

/* Table */
.table-transactions {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.95rem;
}
.table-transactions th {
    background: #28a745;
    color: #fff;
    text-align: center;
    font-weight: 600;
    border-radius: 8px 8px 0 0;
    padding: 12px 8px;
}
.table-transactions td {
    vertical-align: middle;
    text-align: center;
    padding: 10px 8px;
    border-bottom: 1px solid #dee2e6;
}

/* Stripe & Hover effect */
.table-transactions tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}
.table-transactions tbody tr:hover {
    background-color: #e9f7ef;
    transform: translateX(2px);
    transition: 0.2s;
}

/* Status badges */
.status-success {
    background: #d4edda;
    color: #155724;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 500;
}
.status-pending {
    background: #fff3cd;
    color: #856404;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 500;
}
.status-failed {
    background: #f8d7da;
    color: #721c24;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 500;
}

/* Back button */
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #6c757d;
    border-color: #6c757d;
    border-radius: 12px;
    padding: 6px 14px;
    font-weight: 500;
    transition: background-color 0.3s, transform 0.2s;
}
.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
}

/* Icon in button */
.btn-secondary img,
.btn-secondary i {
    width: 18px;
    height: 18px;
}

/* Fade in animation */
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(15px);}
    to {opacity: 1; transform: translateY(0);}
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-time .row {
        flex-direction: column;
    }
    .search-time .col-md-4 {
        width: 100%;
    }
    .table-transactions th,
    .table-transactions td {
        font-size: 0.85rem;
        padding: 8px 4px;
    }
}

    </style>
</head>
<body>
    <main>
        <div class="container mt-5">
            <a href="/Web/ui/payment.php" class="btn btn-secondary mb-3">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            <div class="card shadow-lg p-4">
                <h2 class="mb-4"><i class="fa fa-list"></i> Transaction History</h2>
                
                <!-- Filter & Sort -->
                <div class="search-time">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="start-time" class="form-label">From date:</label>
                            <input type="date" id="start-time" name="start-time" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="end-time" class="form-label">To date:</label>
                            <input type="date" id="end-time" name="end-time" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="sort-list" class="form-label">Sort by:</label>
                            <select id="sort-list" name="sort-list" class="form-select">
                                <option value="newest">Newest time</option>
                                <option value="oldest">Oldest time</option>
                                <option value="amount-asc">Ascending Amount</option>
                                <option value="amount-desc">Descending Amount</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="table-responsive mt-4">
                    <table class="table table-transactions" id="transactions-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Created at</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-body">
                            <!-- Data load bằng JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="../ui/js/api.js"></script>
    <script src="../ui/js/transactions.js"></script>
</body>
</html>
