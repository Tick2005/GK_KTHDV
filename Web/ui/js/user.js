
document.addEventListener('DOMContentLoaded', () => {
    async function loadUserInfo() {
        const userSection = document.getElementById('user-section');
        try {
            const data = await apiFetch('user', { method: 'GET' });
            const user = data.data; 
            
           const fullNameInput = document.getElementById('user-fullname');
           const emailInput = document.getElementById('user-email');
           const phoneInput = document.getElementById('user-phone');
           const balanceInput = document.getElementById('user-balance');
           balanceInput.value = `${Number(user.balance).toLocaleString('vi-VN')}₫`;
           phoneInput.value = user.phone;
           fullNameInput.value = user.full_name;
           emailInput.value = user.email;
            userSection.classList.remove('d-none');
        } catch (err) {
            userSection.classList.add('d-none');
            console.error('Failed to fetch user data:', err.message);
            const alert = document.getElementById('payment-alert');
            alert.textContent = 'Failed to load user information';
            alert.classList.remove('d-none');
        }
    }
    loadUserInfo();
});
