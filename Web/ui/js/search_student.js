document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search_student_id');
    const alertBox = document.getElementById('payment-alert');

    if (!searchInput) return;

    searchInput.addEventListener('input', async () => {
        const studentId = searchInput.value.trim();
        const studentSection = document.getElementById('student-section');
        const paymentSection = document.getElementById('payment-section');
        const feesTable = document.getElementById('fees-table');
        const totalDueEl = document.getElementById('total-due');
        const payButton = document.getElementById('pay-button');
        const paymentform = document.getElementById('payment-form');

        if (studentId.length !== 8) {
            // Reset UI
            studentSection?.classList.add('d-none');
            paymentSection?.classList.add('d-none');
            feesTable.innerHTML = '<tr><td colspan="4" class="text-center">Data is not available at the moment.</td></tr>';
            totalDueEl.textContent = '0₫';
            alertBox.classList.add('d-none');
            payButton.disabled = true;
            return;
        }

        try {
            const data = await apiFetch('search_student', { 
                method: 'POST', 
                body: { search_student_id: studentId }
            });

            const { student, fees, total_due, has_pending_transaction } = data.data;

            if (!student || !fees || total_due === undefined) throw new Error('Invalid API response');

            // Fill student info
            document.getElementById('student-name').value = student.full_name || '';
            document.getElementById('student-email').value = student.email || '';
            document.getElementById('student-phone').value = student.phone || '';
            document.getElementById('target_student_id').value = student.student_id || '';

            studentSection.classList.remove('d-none');
            paymentSection.classList.remove('d-none');

            // Fill fees table
            feesTable.innerHTML = '';
            if (fees.length === 0) {
                feesTable.innerHTML = '<tr><td colspan="4" class="text-center">No unpaid fees</td></tr>';
            } else {
                fees.forEach(fee => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${fee.semester || ''}</td>
                        <td>${fee.school_year || ''}</td>
                        <td>${Number(fee.amount || 0).toLocaleString('vi-VN')}₫</td>
                        <td>${fee.due_date ? new Date(fee.due_date).toLocaleString('vi-VN') : ''}</td>
                    `;
                    feesTable.appendChild(row);
                });
            }

            totalDueEl.textContent = `${Number(total_due || 0).toLocaleString('vi-VN')}₫`;

            let rawBalance = document.getElementById('user-balance').value;
            let userBalance = parseFloat(rawBalance.replace(/\./g, '').replace(/₫/g, '').trim());
            let totalDue = parseFloat((total_due || '0').toString().replace(/\./g, '').replace(/,/g, '.'));

            if (userBalance < totalDue) {
                paymentform.classList.add('d-none');
                alertBox.textContent = 'Insufficient balance to pay this tuition.';
                alertBox.classList.remove('d-none');
            } else if (totalDue === 0) {
                paymentform.classList.add('d-none');
                feesTable.innerHTML = '<tr><td colspan="4" class="text-center">🎉 All tuition fees are already paid!</td></tr>';

            }
            else if (has_pending_transaction) {
                paymentform.classList.add('d-none');
                alertBox.textContent = 'This student already has a pending transaction.';
                alertBox.classList.remove('d-none');
            } else {
                paymentform.classList.remove('d-none');
                payButton.disabled = false;
                alertBox.classList.add('d-none');
            }


        } catch (err) {
            console.error(err);
            alertBox.textContent = 'No student found with this ID. Please check and try again.';
            alertBox.classList.remove('d-none');
            setTimeout(() => alertBox.classList.add('d-none'), 5000);
            studentSection.classList.add('d-none');
            paymentSection.classList.add('d-none');
            feesTable.innerHTML = '<tr><td colspan="4" class="text-center">Data is not available at the moment.</td></tr>';
            totalDueEl.textContent = '0₫';
        }
    });
});
