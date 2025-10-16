document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const errorAlert = document.getElementById('error-alert'); // <- khai báo alert ở đây

    if (!loginForm || !errorAlert) return;

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Ẩn alert trước khi submit
        errorAlert.classList.add('d-none');
        errorAlert.textContent = '';

        const formData = new FormData(loginForm);
        const body = Object.fromEntries(formData);

        try {
            const data = await apiFetch('login', { method: 'POST', body });

            if (!data.success) {
                errorAlert.textContent = data.message || 'Invalid username or password';
                errorAlert.classList.remove('d-none');
                setTimeout(() => errorAlert.classList.add('d-none'), 3000);
                return; // Không redirect nếu login thất bại
            }

            // Lưu CSRF token nếu có
            if (data.csrf_token) localStorage.setItem('csrf_token', data.csrf_token);

            // Redirect khi login thành công
            window.location.href = '/Web/ui/payment.php';
        } catch (err) {
            errorAlert.textContent = err.message || 'Login failed';
            errorAlert.classList.remove('d-none');
            setTimeout(() => errorAlert.classList.add('d-none'), 5000);
        }
    });
});
