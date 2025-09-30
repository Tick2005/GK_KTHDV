document.addEventListener('DOMContentLoaded', () => {
    const paymentForm = document.getElementById('payment-form');
    const alertBox = document.getElementById('payment-alert');
    const payButton = document.getElementById('pay-button');
    const payText = document.getElementById('pay-text');
    const paySpinner = document.getElementById('pay-spinner');

    if (!paymentForm) return;

    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        alertBox.classList.add('d-none');
        alertBox.textContent = '';

        // Show spinner
        payText.classList.add('d-none');
        paySpinner.classList.remove('d-none');
        payButton.disabled = true;

        const formData = new FormData(paymentForm);
        const body = Object.fromEntries(formData);

        if (!body.agree_terms) {
            alertBox.textContent = 'You must agree to the terms and conditions';
            alertBox.classList.remove('d-none');
            setTimeout(() => alertBox.classList.add('d-none'), 5000);
            payText.classList.remove('d-none');
            paySpinner.classList.add('d-none');
            payButton.disabled = false;
            return;
        }

        try {
            const data = await apiFetch('initiate_payment', { method: 'POST', body });

            if (!data.success) {
                alertBox.textContent = data.message || 'Payment failed';
                alertBox.classList.remove('d-none');
                setTimeout(() => alertBox.classList.add('d-none'), 5000);
                payText.classList.remove('d-none');
                paySpinner.classList.add('d-none');
                payButton.disabled = true; // lock nút nếu lỗi
                return;
            }

            if (!data.transaction_id) {
                alertBox.textContent = 'Transaction ID missing';
                alertBox.classList.remove('d-none');
                setTimeout(() => alertBox.classList.add('d-none'), 5000);
                payText.classList.remove('d-none');
                paySpinner.classList.add('d-none');
                payButton.disabled = true;
                return;
            }

            // Redirect sang OTP page
            window.location.href = `/Web/ui/otp.php?trans_id=${data.transaction_id}`;

        } catch (err) {
            console.error('Payment error:', err);
            alertBox.textContent = err.message || 'An unexpected error occurred';
            alertBox.classList.remove('d-none');
            payText.classList.remove('d-none');
            paySpinner.classList.add('d-none');
            payButton.disabled = true;
        }
    });
});
