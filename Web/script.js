setTimeout(function() {
    document.getElementById('skeleton')?.style.display = 'none';
    document.getElementById('content')?.style.display = 'block';
}, 1200);

// Xử lý otp.html (giữ nguyên phần này)
const otpInputs = document.querySelectorAll('.otp-box');
window.onload = function() {
    otpInputs[0]?.focus();
};

otpInputs.forEach((input, idx) => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length === 1 && idx < otpInputs.length - 1) {
            otpInputs[idx + 1].focus();
        }
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && idx > 0) {
            otpInputs[idx - 1].focus();
        }
        if (
            e.key.length === 1 &&
            !/[0-9]/.test(e.key) &&
            e.key !== 'Backspace' &&
            e.key !== 'Tab'
        ) {
            e.preventDefault();
        }
    });
});

document.getElementById('otp-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const otp = Array.from(otpInputs).map(i => i.value).join('');
    if (otp.length === 6) {
        if (otp === '111111') {
            window.location.href = 'payment-success.php';
        } else {
            window.location.href = 'payment-fail.php';
        }
    } else {
        alert('Vui lòng nhập đủ 6 số OTP!');
    }
});

let time = 300;
const timer = document.getElementById('otp-timer');
const interval = setInterval(() => {
    time--;
    timer.textContent = time + 's';
    if (time <= 0) clearInterval(interval);
}, 1000);

// Xử lý transactions.html (giữ nguyên phần demo)
const transactionsDemo = [
    { date: '2024-03-10', content: 'Nạp tiền', amount: 500000, semester: '20241' },
    { date: '2024-04-15', content: 'Thanh toán học phí', amount: -300000, semester: '20241' },
    { date: '2024-09-05', content: 'Nạp tiền', amount: 700000, semester: '20242' },
    { date: '2025-02-20', content: 'Thanh toán học phí', amount: -350000, semester: '20251' },
];

function renderTransactions(semester) {
    const tbody = document.querySelector('#transactions-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const filtered = transactionsDemo.filter(t => t.semester === semester);
    if (filtered.length === 0) {
        tbody.innerHTML = `<tr>
            <td colspan="3" style="text-align:center;color:#888;">Không có giao dịch nào cho học kỳ này.</td>
        </tr>`;
    } else {
        filtered.forEach(t => {
            const row = `<tr>
                <td>${t.date}</td>
                <td>${t.content}</td>
                <td>${t.amount.toLocaleString('vi-VN')}</td>
            </tr>`;
            tbody.innerHTML += row;
        });
    }
}

document.getElementById('semester-select')?.addEventListener('change', function() {
    renderTransactions(this.value);
});

renderTransactions(document.getElementById('semester-select')?.value);