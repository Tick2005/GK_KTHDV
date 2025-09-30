document.addEventListener('DOMContentLoaded', () => {
    const otpForm = document.getElementById('otp-form');
    const resendBtn = document.getElementById('resend-otp');
    const cancelBtn = document.getElementById('cancel-transaction');
    const alertBox = document.getElementById('otp-alert');
    let otpTimer = document.getElementById('otp-timer');

    // Timer variables
    let otpCountdown = null;
    let timeLeft = 300;

    function startOtpTimer() {
        // Clear any existing timer
        if (otpCountdown) clearInterval(otpCountdown);
        timeLeft = 300;
        otpTimer.textContent = `${timeLeft}s`;
        otpCountdown = setInterval(() => {
            timeLeft--;
            otpTimer.textContent = `${timeLeft}s`;
            if (timeLeft <= 0) {
                clearInterval(otpCountdown);
                otpTimer.textContent = 'OTP expired';
                if (resendBtn) resendBtn.disabled = false;
            }
        }, 1000);
    }

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

            try {
                const data = await apiFetch('verify_otp', { method: 'POST', body });

                if (data.success === true) {
                    window.location.href = `/Web/ui/payment-success.php`;
                } else {
                    if (data.attempts_left !== undefined) {
                        showAlert(`Incorrect OTP. You have ${data.attempts_left} attempt(s) left.`, 'danger');
                    } else {
                        showAlert(data.message || 'OTP verification failed', 'danger');
                    }

                    if (data.attempts_left === 0 || data.message?.includes('expired')) {
                        otpForm.querySelectorAll('input, button').forEach(el => el.disabled = true);
                        setTimeout(() => {
                            window.location.href = `/Web/ui/payment-fail.php`;
                        }, 2000);
                    }
                }
            } catch (err) {
                showAlert(err.message, 'danger');
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

            try {
                await apiFetch('resend_otp', { method: 'POST', body: { transaction_id } });
                showAlert('New OTP has been sent', 'success');
                startOtpTimer(); // Reset timer properly
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

    // Start timer on page load
    if (otpTimer) {
        startOtpTimer();
    }
});
