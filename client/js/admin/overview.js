// tải thống kê tổng quan
async function loadOverviewStats() {
    try {
        const response = await fetch('/api/admin/stats');
        const result = await response.json();
        if (result.success) {
            const data = result.data;
            document.getElementById('stat-total-users').textContent = data.total_users;
            document.getElementById('stat-total-tests').textContent = data.total_tests;
            document.getElementById('stat-total-revenue').textContent = formatVND(data.total_revenue);
            document.getElementById('stat-total-purchased').textContent = data.total_purchased_users;
        }
    } catch (error) {
        console.error('error loading stats:', error);
    }
}
