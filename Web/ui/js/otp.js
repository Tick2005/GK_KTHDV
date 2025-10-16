// otp.js (ĐÃ SỬA LOGIC XỬ LÝ KHI HẾT LƯỢT THỬ)
document.addEventListener('DOMContentLoaded', () => {
    const otpForm = document.getElementById('otp-form');
    const resendBtn = document.getElementById('resend-otp');
    const cancelBtn = document.getElementById('cancel-transaction');
    const alertBox = document.getElementById('otp-alert');
    const otpTimer = document.getElementById('otp-timer');

    // Hàm show alert với timeout
    function showAlert(message, type = 'danger', duration = 5000) {
        alertBox.textContent = message;
        alertBox.classList.remove('d-none', 'alert-danger', 'alert-success');
        alertBox.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');

        if (duration > 0) {
            setTimeout(() => alertBox.classList.add('d-none'), duration);
        }
    }

    // Hàm set loading cho button
    function setLoading(btn, isLoading, loadingText = 'Loading...') {
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalText = btn.textContent;
            btn.textContent = loadingText;
        } else {
            btn.disabled = false;
            btn.textContent = btn.dataset.originalText || btn.textContent;
        }
    }

    // Xử lý submit OTP
    if (otpForm) {
        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = otpForm.querySelector('button[type="submit"]');
            setLoading(submitBtn, true, 'Verifying...');

            const formData = new FormData(otpForm);
            const body = Object.fromEntries(formData);
            // Thêm transaction_id vào body nếu nó không có trong form
            const transId = document.getElementById('trans_id').value;
            body.transaction_id = transId;

            const fullOtp = Array.from(inputs).map(input => input.value).join('');
            const transaction_id = document.getElementById('trans_id').value;
            const csrf_token = document.getElementById('csrf_token').value;

            try {
                const data = await apiFetch('verify_otp', { 
                    method: 'POST', 
                    body: { otp: fullOtp, transaction_id, csrf_token }
                });

                if (!data.success) {
                    // Hiển thị thông báo lỗi
                    showAlert(data.message || 'OTP verification failed', 'danger');

                    if (data.is_final_fail || data.attempts_left === 0) { 
                        window.location.href = `/Web/ui/payment-fail.php`;
                    }
                    
                    return;
                }

                window.location.href = `/Web/ui/payment-success.php`;

            } catch (err) {
                console.error('Verify OTP error:', err);
                window.location.href = `/Web/ui/payment-fail.php`;
            } finally {
                setLoading(submitBtn, false);
            }
        });
    }

    // Resend OTP
    if (resendBtn) {
        resendBtn.addEventListener('click', async () => {
            setLoading(resendBtn, true, 'Sending...');
            const transaction_id = document.getElementById('trans_id').value;
            const csrf_token = document.getElementById('csrf_token').value; // Thêm CSRF cho an toàn

            try {
                const data = await apiFetch('resend_otp', { method: 'POST', body: { transaction_id, csrf_token } });
                if (data.success) {
                    showAlert('New OTP has been sent. Timer reset.', 'success');
                     // Reset timer
                    let timeLeft = 300;
                } else {
                     showAlert(data.message, 'danger');
                }
            } catch (err) {
                showAlert(err.message, 'danger');
            } finally {
                setLoading(resendBtn, false);
            }
        });
    }

    // Cancel transaction
    if (cancelBtn) {
        cancelBtn.addEventListener('click', async () => {
            setLoading(cancelBtn, true, 'Cancelling...');
            const transaction_id = document.getElementById('trans_id').value;
            const csrf_token = document.getElementById('csrf_token').value;

            try {
                const data = await apiFetch('cancel_transaction', { 
                    method: 'POST',
                    body: { transaction_id, csrf_token }
                });

                if (data.success) {
                    // Chuyển về trang thanh toán
                    window.location.href = '/Web/ui/payment.php';
                } else {
                    showAlert(data.message || 'Cannot cancel transaction', 'danger');
                }
            } catch (err) {
                showAlert(err.message, 'danger');
            } finally {
                setLoading(cancelBtn, false);
            }
        });
    }

    // OTP Timer
    if (otpTimer) {
        let timeLeft = 300;
        const timer = setInterval(() => {
            timeLeft--;
            otpTimer.textContent = `${timeLeft}s`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                otpTimer.textContent = 'OTP expired. Please resend.';
                if (resendBtn) resendBtn.disabled = false;
            }
        }, 1000);
    }
});