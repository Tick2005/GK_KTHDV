document.addEventListener('DOMContentLoaded', () => {
    const cancelForm = document.getElementById('cancel-form');
    if (cancelForm) {
        cancelForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(cancelForm);
            const body = Object.fromEntries(formData);

            try {
                await apiFetch('cancel_transaction', { method: 'POST', body });
                window.location.href = '/Web/ui/transactions.php';
            } catch (err) {
                const alert = document.getElementById('cancel-alert');
                alert.textContent = err.message;
                alert.classList.remove('d-none');
            }
        });
    }
});