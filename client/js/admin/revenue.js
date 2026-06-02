// hiển thị bảng phân tích doanh thu theo gói dịch vụ
function renderRevenueBreakdown(breakdown) {
    const tbody = document.getElementById('revenueBreakdownTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (!breakdown || breakdown.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không có dữ liệu phân tích</td></tr>';
        return;
    }

    breakdown.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${item.plan_name}</strong></td>
            <td>${item.success_count}</td>
            <td><strong>${formatVND(item.gross_revenue)}</strong></td>
            <td>${item.refunded_count}</td>
            <td><strong style="color: var(--accent-red);">${item.refunded_count > 0 ? '-' : ''}${formatVND(item.refunded_amount)}</strong></td>
            <td><strong style="color: var(--accent-blue);">${formatVND(item.net_revenue)}</strong></td>
            <td><span class="badge ${item.refund_rate > 10 ? 'failed' : (item.refund_rate > 0 ? 'warning' : 'success')}">${item.refund_rate}%</span></td>
        `;
        tbody.appendChild(row);
    });
}

// tải tóm tắt doanh thu và dữ liệu biểu đồ
async function loadRevenueData() {
    try {
        const response = await fetch('/api/admin/revenue');
        const result = await response.json();
        if (result.success) {
            const data = result.data;
            document.getElementById('revenue-stat-month').textContent = formatVND(data.current_month);
            document.getElementById('revenue-stat-alltime').textContent = formatVND(data.all_time);

            // điền thêm các chỉ số thống kê giao dịch và hoàn tiền
            document.getElementById('revenue-stat-success-count').textContent = `${data.success_count} giao dịch`;
            document.getElementById('revenue-stat-refund-amount').textContent = formatVND(data.refunded_amount);
            document.getElementById('revenue-stat-refund-count').textContent = `${data.refunded_count} giao dịch`;

            // vẽ bảng phân tích doanh thu
            renderRevenueBreakdown(data.breakdown);

            // cập nhật sidebar mục tiêu dòng tiền
            updateSidebarWidget(data.current_month);

            // đợi thư viện chart.js tải xong
            if (window.Chart) {
                renderRevenueChart(data.chart);
            } else {
                setTimeout(() => {
                    if (window.Chart) renderRevenueChart(data.chart);
                }, 1000);
            }
        }
    } catch (error) {
        console.error('error loading revenue data:', error);
    }
}

// vẽ biểu đồ doanh thu
function renderRevenueChart(chartData) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    if (revenueChartInstance) {
        revenueChartInstance.destroy();
    }

    const labels = chartData.map(item => item.label);
    const totals = chartData.map(item => parseInt(item.total));

    revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu ròng (VND)',
                data: totals,
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: 'rgb(37, 99, 235)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return formatVND(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatVND(context.raw);
                        }
                    }
                }
            }
        }
    });
}

// tải lịch sử giao dịch phân trang
async function loadTransactionsList(page) {
    transactionsState.page = page;
    try {
        const response = await fetch(`/api/admin/transactions?page=${page}&limit=${transactionsState.limit}`);
        const result = await response.json();
        if (result.success) {
            renderTransactionsTable(result.data);
            transactionsState.total = result.pagination.total;
            renderPagination('transactions-pagination', result.pagination, loadTransactionsList);
        }
    } catch (error) {
        console.error('error loading transactions:', error);
    }
}

// hiển thị danh sách giao dịch
function renderTransactionsTable(transactions) {
    const tbody = document.getElementById('transactionTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Không có giao dịch nào</td></tr>';
        return;
    }

    transactions.forEach(tx => {
        const fullName = `${tx.first_name} ${tx.last_name}`;
        const isRefunded = tx.status === 'refunded';
        const priceStyle = isRefunded ? 'color: var(--accent-red);' : 'color: var(--accent-green);';
        const priceSign = isRefunded ? '-' : '';
        const statusBadge = isRefunded ? ' <span class="badge failed">Đã hoàn tiền</span>' : '';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><code>${tx.tx_id}</code></td>
            <td>
                <div>
                    <div style="font-weight: 600;">${fullName}</div>
                    <div style="font-size: 11px; color: var(--text-secondary);">${tx.email}</div>
                </div>
            </td>
            <td><span class="badge info">${tx.plan_name}</span>${statusBadge}</td>
            <td><strong style="${priceStyle}">${priceSign}${formatVND(tx.price)}</strong></td>
            <td>Thanh toán theo ${tx.period}</td>
            <td>${formatDateTime(tx.created_at)}</td>
        `;
        tbody.appendChild(row);
    });
}
