document.addEventListener('DOMContentLoaded', () => {
    const passwordForm = document.getElementById('profile-password-form');
    const alertBox = document.getElementById('profile-pass-alert');

    if (!passwordForm) return;

    passwordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        alertBox.classList.add('d-none');
        alertBox.textContent = '';

        const formData = new FormData(passwordForm);
        const body = Object.fromEntries(formData);

        // Basic validation
        if (body.new_password !== body.confirm_password) {
            alertBox.textContent = 'New password and confirm password do not match';
            alertBox.classList.remove('d-none');
            alertBox.classList.add('alert-danger');
            return;
        }

        try {
            const res = await apiFetch('update_pass', { method: 'POST', body });

            if (!res.success) throw new Error(res.message || 'Failed to update password');

            alertBox.textContent = 'Password updated successfully';
            alertBox.classList.remove('d-none', 'alert-danger');
            alertBox.classList.add('alert-success');

            // Clear password fields
            passwordForm.reset();
        } catch (err) {
            console.error(err);
            alertBox.textContent = err.message || 'An unexpected error occurred';
            alertBox.classList.remove('d-none', 'alert-success');
            alertBox.classList.add('alert-danger');
        }
    });
});
