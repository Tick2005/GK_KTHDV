<?php
session_start();
$trans_id = isset($_GET['trans_id']) ? $_GET['trans_id'] : '';
if ($trans_id) {
    if (!isset($_SESSION['student_id'])) {
        require_once __DIR__ . '/../database/db.php';
        require_once __DIR__ . '/../repository/TransactionRepository.php';
        require_once __DIR__ . '/../repository/FeeRepository.php';
        $transRepo = new TransactionRepository($pdo);
        $feeRepo = new FeeRepository($pdo);
        $trans = $transRepo->findById($trans_id);
        if ($trans) {
            $fee = $feeRepo->findById($trans['fee_id']);
            if ($fee && isset($fee['student_id'])) {
                $_SESSION['student_id'] = $fee['student_id'];
            } else {
                header('Location: /Web/ui/index.php');
                exit;
            }
        } else {
            header('Location: /Web/ui/index.php');
            exit;
        }
    }
} else if (!isset($_SESSION['student_id'])) {
    header('Location: /Web/ui/index.php');
    exit;
}
if (!isset($_SESSION['user_id'])) {
    header('Location:/Web/ui/index.php');
    exit;
}
include 'header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <style>
body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  margin: 0;
  font-family: 'Inter', sans-serif;
}

/* Main chiếm không gian giữa header và footer */
main {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #e6f0fa 0%, #f5f7fa 100%);
  padding: 20px;
}

/* OTP Form */
#otp-form {
  background: #ffffff;
  padding: 2rem 2.5rem;
  border-radius: 15px;
  box-shadow: 0 12px 25px rgba(0,0,0,0.15);
  text-align: center;
  max-width: 400px;
  width: 100%;
  transition: transform 0.2s, box-shadow 0.2s;
}
#otp-form:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 35px rgba(0,0,0,0.18);
}

/* OTP Inputs */
.otp-wrap {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-bottom: 20px;
}
.otp-input {
  width: 50px;
  height: 50px;
  text-align: center;
  font-size: 1.5rem;
  border: 2px solid #d1d5db;
  border-radius: 10px;
  background-color: #f9fafb;
  transition: all 0.2s ease-in-out;
}
.otp-input:focus {
  border-color: #28a745;
  box-shadow: 0 0 6px rgba(40, 167, 69, 0.4);
  outline: none;
}

/* Buttons */
.btn {
  border-radius: 10px;
  padding: 10px 18px;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
  margin: 5px;
}
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.1);
}
.btn-success {
  background-color: #28a745;
  border-color: #28a745;
}
.btn-success:hover {
  background-color: #218838;
  border-color: #1e7e34;
}
.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
}
.btn-secondary:hover {
  background-color: #5a6268;
  border-color: #545b62;
}

/* Timer */
#otp-timer {
  font-weight: 600;
  color: #dc3545;
  margin-top: 12px;
  font-size: 1rem;
}

/* Alert box */
#otp-alert {
  margin-top: 15px;
  padding: 10px 15px;
  border-radius: 8px;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 480px) {
  .otp-wrap { gap: 8px; }
  .otp-input { width: 40px; height: 40px; font-size: 1.2rem; }
  #otp-form { padding: 1.5rem 1.8rem; }
}

</style>
</head>
<body>
  <main>
      <form id="otp-form" method="post" action="verify_otp.php">
        <div class="otp-wrap" id="otpWrap">
            <!-- JS sẽ render 6 ô input ở đây -->
        </div>
        <!-- input ẩn để gửi OTP đã ghép -->
        <input type="hidden" name="otp" id="otp">
        <input type="hidden" name="transaction_id" id="trans_id" value="<?php echo htmlspecialchars($trans_id); ?>">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        
        <button type="submit" class="btn btn-success">Verify OTP</button>
        <button type="button" id="resend-otp" class="btn btn-secondary">Resend OTP</button>
        <div id="otp-timer">300s</div>
        <div id="otp-alert" class="alert d-none"></div>
        <button type="button" id="cancel-transaction" class="btn btn-secondary">Back to Payment</button>

    </form>
    
  </main>
    
    <?php include 'footer.php'; ?>
    <script src="../ui/js/api.js"></script>
    <script>
  const OTP_LENGTH = 6;
  const otpWrap = document.getElementById('otpWrap');
  const hiddenOtp = document.getElementById('otp');

  // render input
  for (let i = 0; i < OTP_LENGTH; i++) {
    const input = document.createElement('input');
    input.type = 'text';
    input.maxLength = 1;
    input.className = 'otp-input';
    input.inputMode = 'numeric';
    otpWrap.appendChild(input);
  }

  const inputs = otpWrap.querySelectorAll('.otp-input');

  inputs.forEach((el, idx) => {
    el.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, ''); // chỉ số
      e.target.value = v;

      if (v && idx < OTP_LENGTH - 1) {
        inputs[idx + 1].focus();
      }
    });

    el.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace') {
        if (!el.value && idx > 0) {
          inputs[idx - 1].focus();
        }
      }
    });

    el.addEventListener('paste', (e) => {
      e.preventDefault();
      const paste = (e.clipboardData || window.clipboardData).getData('text');
      const numbers = paste.replace(/\D/g, '').split('');
      numbers.forEach((num, i) => {
        if (idx + i < OTP_LENGTH) {
          inputs[idx + i].value = num;
        }
      });
      const nextIndex = Math.min(idx + numbers.length, OTP_LENGTH - 1);
      inputs[nextIndex].focus();
    });
  });

  // khi submit form, ghép otp lại
  document.getElementById('otp-form').addEventListener('submit', (e) => {
    let code = '';
    inputs.forEach(i => code += i.value);
    hiddenOtp.value = code;
  });
</script>
    <script src="../ui/js/otp.js"></script>
</body>
</html>
