document.addEventListener('DOMContentLoaded', () => {
    if (location.pathname.endsWith('transactions.php')) {
        const startInput = document.getElementById('start-time');
        const endInput = document.getElementById('end-time');
        const sortSelect = document.getElementById('sort-list');

        async function loadTransactions() {
            try {
                const data = await apiFetch('transactions');
                const innerData = data.data; 

                // 📢 ĐIỀU CHỈNH: Lấy mảng data cấp 2 một cách an toàn
                const transactions = (innerData && innerData.data && Array.isArray(innerData.data)) 
                    ? innerData.data 
                    : [];
                
                // Ghi log lỗi nếu đối tượng cấp 1 tồn tại nhưng báo lỗi
                if (!innerData || !innerData.success) {
                    console.error("API error:", innerData?.message || "Invalid response structure.");
                }
                function applyFilters() {
                    const startDate = startInput.value ? new Date(startInput.value) : null;
                    const endDate = endInput.value ? new Date(endInput.value) : null;
                    const sortValue = sortSelect.value;

                    const filtered = transactions.filter(t => {
                        const created = new Date(t.created_at);
                        if (startDate && created < startDate) return false;
                        if (endDate && created > endDate) return false;
                        return true;
                    });

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
                        return 0;
                    });

                    renderTransactions(filtered);
                }

                startInput.addEventListener("change", applyFilters);
                endInput.addEventListener("change", applyFilters);
                sortSelect.addEventListener("change", applyFilters);

                applyFilters();
            } catch (err) {
                console.error("Load error:", err.message);
            }
        }

        function renderTransactions(transactions) {
            const tableBody = document.getElementById("transactions-body");
            tableBody.innerHTML = "";

            if (transactions.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No transactions available.</td></tr>`;
            } else {
                transactions.forEach(t => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${t.transaction_id}</td>
                        <td>${Number(t.amount).toLocaleString("vi-VN")}₫</td>
                        <td>${t.note || ""}</td>
                        <td>${new Date(t.created_at).toLocaleString("vi-VN")}</td>
                        <td>
                            <button 
                                style="
                                    padding:4px 10px; 
                                    border:none; 
                                    border-radius:6px; 
                                    color:white; 
                                    background-color:${
                                        t.status === 'success' ? '#28a745' :
                                        t.status === 'failed'
                                        ? '#dc3545' :
                                        '#ffc107'
                                    };
                                "
                            >
                                ${t.status}
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        }

        loadTransactions();
    }
});