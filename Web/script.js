const API_BASE_URL = '/GK_KTHDV/Web/api/';

async function apiFetch(url, options = {}) {
    const csrf = localStorage.getItem('csrf_token') || '';
    options.method = options.method || 'GET';

    if (options.method !== 'GET' && csrf) {
        options.body = options.body || {};
        options.body.csrf_token = csrf;
    }

    if (options.body) options.body = JSON.stringify(options.body);

    options.headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };

    try {
        // Nếu có query string thì chỉ thêm `.php` trước phần `?`
        let endpoint = url;
        if (!url.includes('?')) {
            endpoint = `${url.replace('/', '_')}.php`;
        } else {
            const [path, query] = url.split('?');
            endpoint = `${path.replace('/', '_')}.php?${query}`;
        }

        const res = await fetch(`${API_BASE_URL}${endpoint}`, options);
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Request failed');
        return data;
    } catch (err) {
        throw new Error(`API Error: ${err.message}`);
    }
}


// Login
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        const body = Object.fromEntries(formData);
        try {
            const data = await apiFetch('login', { method: 'POST', body });
            localStorage.setItem('csrf_token', data.csrf_token);
            window.location.href = '/GK_KTHDV/Web/html/home.php';
        } catch (err) {
            const errorAlert = document.getElementById('error-alert');
            if (errorAlert) {
                errorAlert.textContent = err.message;
                errorAlert.classList.remove('d-none');
            }
        }
    });
}

window.addEventListener('load', async () => {
    if (location.pathname.endsWith('home.php')) {
        try {
            const data = await apiFetch('user');
            const user = data.data;
            const userName = document.querySelector('#user-name strong');
            const userEmail = document.querySelector('#user-email strong');
            const userPhone = document.querySelector('#user-phone strong');
            const balance = document.getElementById('balance');
            const userIcon = document.getElementById('user-icon');
            const iconSelect = document.querySelector('.icon-select');

            // Update user info
            if (userName) userName.textContent = user.full_name;
            if (userEmail) userEmail.textContent = user.email;
            if (userPhone) userPhone.textContent = user.phone;
            if (balance) balance.dataset.original = `${user.balance.toLocaleString('vi-VN')}₫`;
            if (userIcon) userIcon.src = `./img/icon/${user.user_icon}`;

            // Populate icon select options with predefined icons
            if (iconSelect) {
                // Ẩn select gốc và tạo button để trigger box nổi
                iconSelect.style.display = 'none'; // Ẩn select nếu cần giữ để submit form
                const iconButton = document.createElement('button');
                iconButton.className = 'btn btn-primary'; // Sử dụng class từ Bootstrap hoặc style.css
                iconButton.textContent = 'Change Icon';
                iconButton.type = 'button'; // Không submit form
                iconSelect.parentNode.insertBefore(iconButton, iconSelect); // Chèn button trước select

                // Tạo box nổi (dropdown-like)
                const iconBox = document.createElement('div');
                iconBox.style.display = 'none';
                iconBox.style.position = 'absolute';
                iconBox.style.zIndex = '1000';
                iconBox.style.backgroundColor = '#fff';
                iconBox.style.border = '1px solid #ccc';
                iconBox.style.borderRadius = '8px';
                iconBox.style.padding = '10px';
                iconBox.style.maxHeight = '150px'; // Giới hạn chiều cao để hiển thị khoảng 3 icon (tùy chỉnh dựa trên height icon)
                iconBox.style.overflowY = 'auto'; // Thanh kéo cho phần còn lại
                iconBox.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                document.body.appendChild(iconBox); // Thêm vào body để nổi lên trên

                // Populate icons vào box
                const icons = ['default.png', 'ava_a.png', 'ava_b.png', 'ava_c.png', 'ava_d.png', 'ava_e.png', 'ava_f.png', 'ava_g.png', 'ava_h.png'];
                icons.forEach(icon => {
                    const iconItem = document.createElement('div');
                    iconItem.style.cursor = 'pointer';
                    iconItem.style.padding = '5px';
                    iconItem.style.display = 'flex';
                    iconItem.style.alignItems = 'center';
                    iconItem.innerHTML = `<img src="./img/icon/${icon}" alt="${icon}" style="width:30px;height:30px;margin-right:10px;"> ${icon}`;
                    if (user.user_icon === icon) {
                        iconItem.style.backgroundColor = '#e6f0fa'; // Highlight selected mặc định
                    }
                    iconItem.addEventListener('click', async () => {
                        // Cập nhật select value và ẩn box
                        iconSelect.value = icon;
                        iconBox.style.display = 'none';
                        // Optional: Cập nhật preview icon nếu có
                        const previewImg = document.getElementById('user-icon');
                        if (previewImg) previewImg.src = `./img/icon/${icon}`;

                        // Gửi update ngay lập tức đến API
                        try {
                            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
                            await apiFetch('update_icon', {
                                method: 'POST',
                                body: {
                                    icon: icon,
                                    csrf_token: csrfToken
                                }
                            });
                            // Optional: Alert thành công hoặc update UI khác
                            console.log('Icon updated successfully');
                        } catch (err) {
                            console.error('Update icon error:', err.message);
                            alert('Failed to update icon: ' + err.message);
                        }
                    });
                    iconBox.appendChild(iconItem);
                });

                // Event click button để hiển thị box tại vị trí button
                iconButton.addEventListener('click', (e) => {
                    const rect = iconButton.getBoundingClientRect();
                    iconBox.style.top = `${rect.bottom + window.scrollY}px`;
                    iconBox.style.left = `${rect.left + window.scrollX}px`;
                    iconBox.style.display = 'block';
                });

                // Ẩn box khi click ngoài
                document.addEventListener('click', (e) => {
                    if (!iconBox.contains(e.target) && e.target !== iconButton) {
                        iconBox.style.display = 'none';
                    }
                });
            }

            // Hide skeleton and show content
            const skeleton = document.getElementById('skeleton');
            const content = document.getElementById('content');
            if (skeleton) skeleton.style.display = 'none';
            if (content) content.style.display = 'block';
        } catch (err) {
            console.error('Load user error:', err.message);
        }
    }

    // Toggle balance
    const toggleBalance = document.getElementById('toggle-balance');
    if (toggleBalance) {
        toggleBalance.addEventListener('click', (e) => {
            const balance = document.getElementById('balance');
            const icon = e.target.querySelector('i') || e.target; // Handle case where button contains the icon
            if (balance && icon) {
                if (balance.textContent === '*********') {
                    balance.textContent = balance.dataset.original || '0₫';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                } else {
                    balance.textContent = '*********';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
            }
        });
    }
const iconSelect = document.querySelector('.icon-select');
    if (iconSelect) {
        iconSelect.addEventListener('change', async (e) => {
            try {
                // Lấy CSRF token từ hidden input trong trang
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

                // Gửi JSON body thay vì FormData
                await apiFetch('update_icon', {
                    method: 'POST',
                    body: {
                        icon: e.target.value,
                        csrf_token: csrfToken
                    }
                });

                // Cập nhật UI ngay lập tức
                const userIcon = document.getElementById('user-icon');
                if (userIcon) {
                    userIcon.src = `./img/icon/${e.target.value}`;
                }

            } catch (err) {
                console.error('Update icon error:', err.message);
                alert('Failed to update icon: ' + err.message);
            }
        });
    }

    // Logout button (header or nav)
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            await apiFetch('logout', { method: 'POST', body: {} });
            window.location.href = '/GK_KTHDV/Web/html/logout.php';
        } catch (err) {
            alert(err.message);
        }
    });
}

// Countdown on logout.php
const countdownEl = document.getElementById("countdown");
if (countdownEl) {
    let timeLeft = 60;
    const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timer);
            window.location.href = "/GK_KTHDV/Web/html/index.php";
        }
    }, 1000);
}

// "Logout Now" button on logout.php
const logoutNowBtn = document.getElementById("logout-now");
if (logoutNowBtn) {
    logoutNowBtn.addEventListener("click", (e) => {
        e.preventDefault();
        window.location.href = "/GK_KTHDV/Web/html/index.php";
    });
}

});
if (location.pathname.endsWith("payment.php")) {
    window.addEventListener("DOMContentLoaded", async () => {
        // ------------------- Load PAYER -------------------
        try {
            const data = await apiFetch("user");
            const user = data.data;

            document.getElementById("payer-name").value = user.full_name;
            document.getElementById("payer-email").value = user.email;
            document.getElementById("payer-phone").value = user.phone;
            document.getElementById("payer-balance").value =
                user.balance.toLocaleString("vi-VN") + "₫";
        } catch (err) {
            console.error("Load payer error:", err.message);
        }
// ------------------- SEARCH STUDENT AUTO -------------------
const searchInput = document.getElementById("search_student_id");
if (searchInput) {
    searchInput.addEventListener("keyup", async (e) => {
        const studentId = e.target.value.trim();

        if (studentId.length === 8) {
            try {
                const result = await apiFetch(`fees?student_id=${studentId}`);

                const student = result.data.student;
                const fees = result.data.fees || [];
                const total = result.data.total_due || 0;

                // Gán thông tin student
                document.getElementById("student-id").value = student.student_id;
                document.getElementById("student-name").value = student.full_name;
                document.getElementById("student-email").value = student.email;
                document.getElementById("student-phone").value = student.phone;

                // Render học phí
                const table = document.getElementById("fees-table");
                table.innerHTML = "";
                if (fees.length === 0) {
                    table.innerHTML =
                        `<tr><td colspan="4" class="text-center">Không có học phí nào chưa đóng</td></tr>`;
                } else {
                    fees.forEach(f => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${f.semester}</td>
                            <td>${f.school_year}</td>
                            <td>${Number(f.amount).toLocaleString("vi-VN")}₫</td>
                            <td>${f.due_date}</td>
                        `;
                        table.appendChild(row);
                    });
                }

                // Tổng tiền
                document.getElementById("total-due").textContent =
                    total.toLocaleString("vi-VN") + "₫";

                // Hiện phần thông tin & thanh toán
                document.getElementById("student-section").classList.remove("d-none");
                document.getElementById("payment-section").classList.remove("d-none");

                // Lưu target_student_id để thanh toán
                document.getElementById("target_student_id").value = student.student_id;

            } catch (err) {
                alert("Không tìm thấy sinh viên hoặc lỗi tải dữ liệu: " + err.message);
                document.getElementById("student-section").classList.add("d-none");
                document.getElementById("payment-section").classList.add("d-none");
            }
        } else {
            document.getElementById("student-section").classList.add("d-none");
            document.getElementById("payment-section").classList.add("d-none");
        }
    });
}

        // ------------------- PAYMENT -------------------
        const paymentForm = document.getElementById("payment-form");
        if (paymentForm) {
            paymentForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                try {
                    const targetId = document.getElementById("target_student_id")?.value;
                    const agree = document.getElementById("agree_terms")?.checked;

                    if (!targetId) throw new Error("Chưa chọn sinh viên cần đóng học phí");
                    if (!agree) throw new Error("Bạn phải đồng ý với điều khoản trước khi thanh toán");

                    const data = await apiFetch("initiate_payment", {
                        method: "POST",
                        body: {
                            target_student_id: targetId,
                            agree_terms: true
                        }
                    });

                    window.location.href = `/GK_KTHDV/Web/html/otp.php?trans_id=${data.transaction_id}`;

                } catch (err) {
                    const paymentAlert = document.getElementById("payment-alert");
                    if (paymentAlert) {
                        paymentAlert.textContent = err.message;
                        paymentAlert.classList.remove("d-none");
                    } else {
                        alert("Thanh toán thất bại: " + err.message);
                    }
                }
            });
        }
    });
}

// OTP Verification
const otpForm = document.getElementById('otp-form');
if (otpForm) {
    otpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = {
            transaction_id: document.getElementById('trans_id').value,
            otp: document.getElementById('otp').value
        };
        try {
            const data = await apiFetch('verify_otp', { method: 'POST', body });
            const otpAlert = document.getElementById('otp-alert');
            if (otpAlert) {
                otpAlert.textContent = data.message;
                otpAlert.classList.remove('d-none');
            }
            if (data.success) window.location.href = '/GK_KTHDV/Web/html/home.php';
        } catch (err) {
            const otpAlert = document.getElementById('otp-alert');
            if (otpAlert) {
                otpAlert.textContent = err.message;
                otpAlert.classList.remove('d-none');
            }
        }
    });
    const resendOtp = document.getElementById('resend-otp');
    if (resendOtp) {
        resendOtp.addEventListener('click', async () => {
            try {
                const data = await apiFetch('resend_otp', { method: 'POST', body: { transaction_id: document.getElementById('trans_id').value } });
                const otpAlert = document.getElementById('otp-alert');
                if (otpAlert) {
                    otpAlert.textContent = data.message;
                    otpAlert.classList.remove('d-none');
                }
            } catch (err) {
                const otpAlert = document.getElementById('otp-alert');
                if (otpAlert) {
                    otpAlert.textContent = err.message;
                    otpAlert.classList.remove('d-none');
                }
            }
        });
    }
    let time = 300;
    const timer = document.getElementById('otp-timer');
    if (timer) {
        const interval = setInterval(() => {
            time--;
            timer.textContent = time + 's';
            if (time <= 0) clearInterval(interval);
        }, 1000);
    }
}

// Fail message
if (location.pathname.endsWith('payment-fail.php')) {
    const failMessage = document.getElementById('fail-message');
    if (failMessage) {
        const message = new URLSearchParams(location.search).get('message') || 'Đã xảy ra lỗi';
        failMessage.textContent = message;
    }
}

if (location.pathname.endsWith("transactions.php")) {
    window.addEventListener("DOMContentLoaded", async () => {
        try {
            const response = await apiFetch("transactions");
            let transactions = response.data;

            // Render ban đầu
            renderTransactions(transactions);

            // Lấy input date & sort
            const startInput = document.getElementById("start-time");
            const endInput = document.getElementById("end-time");
            const sortSelect = document.getElementById("sort-list");

            // Hàm lọc & sắp xếp
            function applyFilters() {
                const startDate = startInput.value ? new Date(startInput.value) : null;
                const endDate = endInput.value ? new Date(endInput.value) : null;
                const sortValue = sortSelect.value;

                let filtered = transactions.filter(t => {
                    const created = new Date(t.created_at);
                    if (startDate && created < startDate) return false;
                    if (endDate && created > endDate) return false;
                    return true;
                });

                // Sắp xếp theo option
                filtered.sort((a, b) => {
                    if (sortValue === "newest") {
                        return new Date(b.created_at) - new Date(a.created_at);
                    }
                    if (sortValue === "oldest") {
                        return new Date(a.created_at) - new Date(b.created_at);
                    }
                    if (sortValue === "amount-asc") {
                        return Number(a.amount) - Number(b.amount);
                    }
                    if (sortValue === "amount-desc") {
                        return Number(b.amount) - Number(a.amount);
                    }
                    return 0; // không thay đổi
                });

                renderTransactions(filtered);
            }

            // Lắng nghe thay đổi input ngày & sort
            startInput.addEventListener("change", applyFilters);
            endInput.addEventListener("change", applyFilters);
            sortSelect.addEventListener("change", applyFilters);

        } catch (err) {
            console.error("Load error:", err.message);
        }
    });
}


function renderTransactions(transactions) {
    const tableBody = document.getElementById("transactions-body");
    tableBody.innerHTML = "";

    if (transactions.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center">Không có giao dịch nào</td></tr>`;
    } else {
        transactions.forEach(t => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${t.transaction_id}</td>
                <td>${Number(t.amount).toLocaleString("vi-VN")}₫</td>
                <td>${t.note || ""}</td>
                <td>${new Date(t.created_at).toLocaleString("vi-VN")}</td>
                <td> <button 
                        style="
                            padding:4px 10px; 
                            border:none; 
                            border-radius:6px; 
                            color:white; 
                            background-color:${t.status === "success" ? "green" : "red"};
                        ">
                        ${t.status}
                    </button></td>
            `;
            tableBody.appendChild(row);
        });
    }
}







