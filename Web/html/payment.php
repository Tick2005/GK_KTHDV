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
    <title>Payment</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .terms-box h5 {
            color: #1a3c6d;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .terms-box p {
            font-size: 0.9rem;
            color: #444;
            line-height: 1.5;
            margin-bottom: 0;
        }
        .info-input {
            background: #e9ecef;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #1a3c6d;
            border: none;
        }
    </style>
</head>
<body>
<main>
<div class="container mt-3">
    <a href="/GK_KTHDV/Web/html/home.php" class="btn btn-secondary"><img src="./img/Back-icon.png"></img> Back</a>
    <div class="card shadow-lg p-4">
        <h2 class="text-center mb-4">Pay Tuiton Fee</h2>

        <!-- Form tìm sinh viên -->
<div class="search-box mb-4">
    <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <input type="text" id="search_student_id" class="form-control"
           maxlength="8" pattern="[A-Za-z0-9]{8}" required
           placeholder="Enter Student ID ">
</div>


        <!-- Người nộp -->
        <h5>Payer information:</h5>
        <input type="text" id="payer-name" class="form-control mb-2" readonly>
        <input type="text" id="payer-email" class="form-control mb-2" readonly>
        <input type="text" id="payer-phone" class="form-control mb-2" readonly>
        <input type="text" id="payer-balance" class="form-control mb-2" readonly>

<div id="student-section" class="mt-4 d-none">
    <h5>Student information:</h5>
    <input type="text" id="student-id" class="form-control mb-2" readonly>
    <input type="text" id="student-name" class="form-control mb-2" readonly>
    <input type="text" id="student-email" class="form-control mb-2" readonly>
    <input type="text" id="student-phone" class="form-control mb-2" readonly>

    <h5 class="mt-4">Fes Details:</h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Semester</th>
                <th>School year</th>
                <th>Amount</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody id="fees-table">
            <tr><td colspan="4" class="text-center">Chưa có dữ liệu</td></tr>
        </tbody>
    </table>
    <p><strong>Total amount: <span id="total-due">0₫</span></strong></p>

<div id="payment-section" class="d-none">
                <form id="payment-form">
                    <input type="hidden" id="target_student_id" name="target_student_id">
                    <h5>Payment Terms</h5>
                    <ul class="mb-0">
                        <li>Students are responsible for verifying the accuracy of tuition information before confirming payment.</li>
                        <li>Once completed, the payment will be recorded in the system and is non-refundable.</li>
                        <li>In case of payment failure, please retry or contact the finance office for assistance.</li>
                        <li>The university is not responsible for errors caused by providing incorrect information.</li>
                    </ul>

                    <div class="form-check mb-2 mt-2">
                        <input class="form-check-input" type="checkbox" id="agree_terms">
                        <label class="form-check-label" for="agree_terms">I agree to the terms and conditions</label>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Pay Now</button>
                    <div id="payment-alert" class="alert alert-danger d-none mt-2"></div>
                </form>
            </div>
</div>
    </div>
</div>
</main>
<?php include 'footer.php'; ?>
<script src="../script.js"></script>
</body>
</html>
