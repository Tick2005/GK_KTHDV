<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Reset & font */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
}

body {
  background: linear-gradient(135deg, #e6f0fa 0%, #f5f7fa 100%);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Card style */
.card {
  min-width: 500px;
  width: auto;
  border-radius: 15px;
  background: #ffffff;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
  padding: 2rem;
  margin-bottom: 30px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.card h2 {
  color: #005555;
  font-weight: 700;
  margin-bottom: 25px;
  text-align: center;
}

/* Section headers */
.user-info h5, .student-info h5, h5.fee-info {
  text-align: center;
  font-weight: 600;
  color: #ffffff;
  padding: 10px 0;
  border-radius: 8px;
  margin-bottom: 20px;
}

.user-info h5 { background-color: #3b82f6; } /* Blue */
.student-info h5 { background-color: #10b981; } /* Green */
h5.fee-info { background-color: #fbbf24; color: #000; } /* Yellow */

/* Form group inputs */
.form-group input,
#search_student_id,
input[readonly] {
  border-radius: 10px;
  border: 1px solid #d1d5db;
  padding: 10px 14px;
  width: 100%;
  font-size: 1rem;
  background-color: #f9fafb;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.form-group input:focus,
#search_student_id:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 6px rgba(37, 99, 235, 0.2);
}

/* Fee table */
.table {
  border-collapse: separate;
  width: 100%;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.table th {
  background: linear-gradient(100deg, #005555, #007777);
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px;
}
.table td {
  padding: 10px;
  text-align: center;
  border-bottom: 1px solid #ddd;
}
.table-striped tbody tr:nth-of-type(odd) {
  background-color: #f8fafc;
}
.table tbody tr:hover {
  background-color: #e0f2fe;
}

/* Payment terms list */
#payment-form ul {
  padding-left: 20px;
  margin-bottom: 15px;
}
#payment-form li {
  margin-bottom: 8px;
  line-height: 1.5;
}

/* Checkbox & button */
.form-check-input {
  width: 18px;
  height: 18px;
  border-radius: 5px;
}
#payment-form .btn {
  border-radius: 12px;
  padding: 10px 0;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}
#payment-form .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.1);
}

/* Alert */
#payment-alert {
  margin-top: 10px;
  border-radius: 8px;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .card {
    min-width: auto;
    padding: 1.5rem;
    margin: 15px;
  }
  .user-info, .student-info {
    margin-bottom: 20px;
  }
}

    </style>
</head>
<body>
<main>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2>Payment Tuition Fees</h2>
        <div class="row">
            <div class="user-info col-md-6">
                <h5>User Information:</h5>
                <div id="user-section" class="d-none mt-3">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Fullname:</label>
                        <div class="col-sm-10">
                            <input type="text" id="user-fullname" class="form-control mb-2" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Email:</label>
                        <div class="col-sm-10">
                            <input type="text" id="user-email" class="form-control mb-2" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Phone:</label>
                        <div class="col-sm-10">
                           <input type="text" id="user-phone" class="form-control mb-2" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Balance:</label>
                            <div class="col-sm-10">
                           <input type="text" id="user-balance" class="form-control mb-2" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="student-info col-md-6">
                <h5>Student Information:</h5>
                <div class="from-group row">
                    <label class="col-sm-2 col-form-label">Student ID:</label>
                    <div class="col-sm-10">
                        <input type="text" id="search_student_id" placeholder="Enter student ID" maxlength="8" class="form-control">
                    </div>
                </div>

                <div id="student-section" class="d-none">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Student Name:</label>
                        <div class="col-sm-10">
                           <input type="text" id="student-name" class="form-control mb-2" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Email:</label>
                        <div class="col-sm-10">
                           <input type="text" id="student-email" class="form-control mb-2" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Phone:</label>
                        <div class="col-sm-10">
                          <input type="text" id="student-phone" class="form-control mb-2" readonly>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <h5 class="fee-info mt-4">Fees Details:</h5>
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
                <tr><td colspan="4" class="text-center">Data is not available at the moment.</td></tr>
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
                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms">
                    <label class="form-check-label" for="agree_terms">I agree to the terms and conditions</label>
                </div>
                <button type="submit" class="btn btn-danger w-100" id="pay-button">
                  <input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <span id="pay-text">Pay Now</span>
                    <span id="pay-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>

            </form>
        </div>
        <div id="payment-alert" class="alert alert-danger d-none mt-2"></div>
    </div>
</div>
</main>
<?php include 'footer.php'; ?>
<script src="../ui/js/api.js"></script>
<script src="../ui/js/user.js"></script>
<script src="../ui/js/search_student.js"></script>
<script src="../ui/js/payment.js"></script>
</body>
</html>