document.addEventListener('DOMContentLoaded', () => {
    const profileForm = document.getElementById('profile-info-form');
    const alertBox = document.getElementById('profile-info-alert');

    if (!profileForm) return;

    function showAlert(message, type = 'success', timeout = 5000) {
        alertBox.textContent = message;
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertBox.classList.add(`alert-${type}`);
        if (timeout) setTimeout(() => alertBox.classList.add('d-none'), timeout);
    }

    profileForm.addEventListener('submit', async e => {
        e.preventDefault();
        alertBox.classList.add('d-none');
        alertBox.textContent = '';

        const formData = new FormData(profileForm);
        const body = Object.fromEntries(formData);

        try {
            const res = await apiFetch('update_profile', { method: 'POST', body });
            if (!res.success) throw new Error(res.message || 'Failed to update profile');
            showAlert('Profile updated successfully', 'success');
        } catch (err) {
            console.error(err);
            showAlert(err.message || 'An unexpected error occurred', 'danger');
        }
    });

    async function loadProfile() {
        try {
            const user = await apiFetch('user', { method: 'GET' });
            if (!user.success || !user.data) throw new Error('User data not found');
            document.getElementById('username').value = user.data.username || '';
            document.getElementById('full_name').value = user.data.full_name || '';
            document.getElementById('email').value = user.data.email || '';
            document.getElementById('phone').value = user.data.phone || '';
        } catch (err) {
            console.error('Load profile error:', err.message);
            showAlert(err.message, 'danger', 0);
        }
    }

    loadProfile();
});
