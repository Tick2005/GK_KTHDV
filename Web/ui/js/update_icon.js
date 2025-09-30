document.addEventListener('DOMContentLoaded', () => {
    if (!location.pathname.endsWith('profile.php')) return;

    const userIcon = document.getElementById('user-icon');
    const alertBox = document.getElementById('profile-alert');
    if (!userIcon) return;

    async function fetchUser() {
        const res = await apiFetch('user', { method: 'GET' });
        if (!res?.success || !res.data) throw new Error(res?.message || 'User data not found');
        return res.data;
    }

    async function updateUserIcon(icon) {
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || localStorage.getItem('csrf_token') || '';
        return apiFetch('update_icon', { method: 'POST', body: { icon, csrf_token: csrfToken } });
    }

    function showAlert(message, type = 'success', timeout = 5000) {
        if (!alertBox) return;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertBox.classList.add(`alert-${type}`);
        if (timeout) setTimeout(() => alertBox.classList.add('d-none'), timeout);
    }

    /** ==============================
     * Load current icon on page load
     * ============================== */
    async function loadCurrentIcon() {
        try {
            const user = await fetchUser();
            userIcon.src = `/Web/ui/img/icon/${user.user_icon || 'default.png'}`;
        } catch (err) {
            console.error('Load current icon error:', err.message);
            showAlert(err.message, 'danger', 0);
        }
    }

    /** ==============================
     * Icon selection box
     * ============================== */
    const icons = ['default.png', 'ava_a.png', 'ava_b.png', 'ava_c.png', 'ava_d.png', 'ava_e.png', 'ava_f.png', 'ava_g.png', 'ava_h.png'];
    const iconBox = document.createElement('div');
    iconBox.className = 'icon-box';
    Object.assign(iconBox.style, {
        display: 'none', position: 'absolute', zIndex: 1000,
        backgroundColor: '#fff', border: '1px solid #ccc', borderRadius: '8px',
        padding: '10px', maxHeight: '150px', overflowY: 'auto', boxShadow: '0 4px 8px rgba(0,0,0,0.1)'
    });
    document.body.appendChild(iconBox);

    icons.forEach(icon => {
        const iconItem = document.createElement('div');
        iconItem.className = 'icon-option';
        iconItem.style.cssText = 'cursor:pointer; padding:5px; display:flex; align-items:center;';
        const iconName = icon === 'default.png' ? 'Default' : `Ava ${icon.charAt(4).toUpperCase()}`;
        iconItem.innerHTML = `<img src="/Web/ui/img/icon/${icon}" alt="${iconName}" style="width:30px;height:30px;margin-right:10px;"> ${iconName}`;

        iconItem.addEventListener('click', async () => {
            try {
                userIcon.src = `/Web/ui/img/icon/${icon}`;
                iconBox.style.display = 'none';
                await updateUserIcon(icon);
                showAlert(`Icon updated to ${iconName}`, 'success');
            } catch (err) {
                console.error('Update icon error:', err.message);
                showAlert(err.message || 'Failed to update icon', 'danger');
            }
        });

        iconBox.appendChild(iconItem);
    });

    async function highlightCurrentIcon() {
        try {
            const currentIcon = (await fetchUser()).user_icon || 'default.png';
            iconBox.querySelectorAll('.icon-option').forEach(item => {
                item.style.backgroundColor = item.querySelector('img').src.includes(currentIcon) ? '#e6f0fa' : '';
            });
        } catch (err) {
            console.error('Highlight icon error:', err.message);
        }
    }

    userIcon.addEventListener('click', async () => {
        const rect = userIcon.getBoundingClientRect();
        iconBox.style.top = `${rect.bottom + window.scrollY}px`;
        iconBox.style.left = `${rect.left + window.scrollX}px`;
        iconBox.style.display = iconBox.style.display === 'none' ? 'block' : 'none';
        if (iconBox.style.display === 'block') await highlightCurrentIcon();
    });

    document.addEventListener('click', e => {
        if (!iconBox.contains(e.target) && e.target !== userIcon) iconBox.style.display = 'none';
    });

    /** ==============================
     * Init
     * ============================== */
    loadCurrentIcon();
});
