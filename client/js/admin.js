// điều khiển dashboard quản trị
let allTests = [];
let usersState = { page: 1, limit: 10, total: 0, search: '', role: '', status: '' };
let attemptsState = { page: 1, limit: 10, total: 0 };
let transactionsState = { page: 1, limit: 10, total: 0 };
let loadedSections = new Set();
let revenueChartInstance = null;
const revenueTarget = 2000000; // doanh thu mục tiêu 2 triệu VND cho thanh tiến trình sidebar

// định dạng tiền tệ sang VND
function formatVND(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

// định dạng ngày tháng sang DD/MM/YYYY
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// định dạng ngày giờ sang DD/MM/YYYY HH:MM:SS
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
}

// định dạng giây sang MM:SS
function formatTimeSpent(seconds) {
    if (!seconds) return '00:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

// tải dữ liệu khi tab được kích hoạt
async function loadSectionData(section) {
    if (loadedSections.has(section)) return;

    if (section === 'overview') {
        await loadOverviewStats();
    } else if (section === 'tests') {
        const hasForm = document.getElementById('questions-container') !== null;
        if (!hasForm) {
            await loadTestsList();
        }
    } else if (section === 'users') {
        await loadUsersList(1);
    } else if (section === 'attempts') {
        await loadAttemptsList(1);
    } else if (section === 'revenue') {
        await loadRevenueData();
        await loadTransactionsList(1);
    }

    loadedSections.add(section);
}

// cập nhật tiến trình mục tiêu ở sidebar
function updateSidebarWidget(currentMonthRevenue) {
    const revenueValEl = document.getElementById('widget-revenue-val');
    const revenueProgressEl = document.getElementById('widget-revenue-progress');
    
    if (revenueValEl && revenueProgressEl) {
        const percent = Math.min(100, Math.round((currentMonthRevenue / revenueTarget) * 100));
        revenueValEl.textContent = `${formatVND(currentMonthRevenue)} / ${formatVND(revenueTarget)}`;
        revenueProgressEl.style.width = `${percent}%`;
        
        // thay đổi màu sắc tiến trình dựa trên tỉ lệ đạt được
        revenueProgressEl.className = 'progress-bar';
        if (percent >= 100) {
            revenueProgressEl.classList.add('green');
        } else if (percent >= 50) {
            revenueProgressEl.classList.add('orange');
        } else {
            revenueProgressEl.classList.add('red');
        }
    }
}

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

// tải danh sách đề thi
async function loadTestsList() {
    try {
        const response = await fetch('/api/tests');
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        
        if (result.success && result.data && Array.isArray(result.data)) {
            allTests = result.data;
            filterTests();
        }
    } catch (error) {
        console.error('error loading tests:', error);
    }
}

// lọc danh sách đề thi tại client
function filterTests() {
    const searchInput = document.getElementById('search-tests');
    const premiumFilter = document.getElementById('filter-tests-premium');
    const statusFilter = document.getElementById('filter-tests-status');
    if (!searchInput) return;

    const searchText = searchInput.value.toLowerCase().trim();
    const premiumValue = premiumFilter.value;
    const statusValue = statusFilter.value;

    const filtered = allTests.filter(test => {
        const titleMatch = test.title.toLowerCase().includes(searchText);
        
        let premiumMatch = true;
        if (premiumValue === 'Premium') {
            premiumMatch = test.is_premium === true || parseInt(test.is_premium) === 1;
        } else if (premiumValue === 'Thường') {
            premiumMatch = test.is_premium === false || parseInt(test.is_premium) === 0;
        }

        let statusMatch = true;
        if (statusValue === 'Hoạt động') {
            statusMatch = parseInt(test.is_active) === 1;
        } else if (statusValue === 'Tạm ẩn') {
            statusMatch = parseInt(test.is_active) === 0;
        }

        return titleMatch && premiumMatch && statusMatch;
    });

    renderTestsTable(filtered);
}

// hiển thị danh sách đề thi
function renderTestsTable(tests) {
    const tbody = document.getElementById('testTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (tests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không tìm thấy bài thi nào</td></tr>';
        return;
    }

    tests.forEach(test => {
        const isPremium = test.is_premium === true || parseInt(test.is_premium) === 1;
        const isActive = parseInt(test.is_active) === 1;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${test.title}</strong></td>
            <td><span class="badge ${isPremium ? 'premium' : 'standard'}">${isPremium ? 'Premium' : 'Thường'}</span></td>
            <td><span class="badge ${isActive ? 'active' : 'warning'}">${isActive ? 'Hoạt động' : 'Chờ duyệt'}</span></td>
            <td>${formatDate(test.created_at)}</td>
            <td style="text-align: right;">
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    ${!isActive ? `
                        <button class="btn-primary approve-test-btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--accent-green);" data-uuid="${test.uuid}">
                            Duyệt
                        </button>
                    ` : ''}
                    <a href="admin.php?section=tests&action=edit&test_id=${test.uuid}" class="btn-primary" style="padding: 6px 12px; font-size: 12px;">
                        <i class="bx bx-edit-alt"></i> Câu hỏi
                    </a>
                    <button class="btn-primary edit-info-btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--accent-orange);" 
                            data-uuid="${test.uuid}" data-title="${test.title}" data-premium="${isPremium ? '1' : '0'}" data-active="${isActive ? '1' : '0'}">
                        Thông tin
                    </button>
                    <button class="btn-danger delete-test-btn" style="padding: 6px 12px; font-size: 12px;" data-uuid="${test.uuid}">
                        Xóa
                    </button>
                </div>
            </td>
        `;

        // kích đúp để biên soạn câu hỏi
        row.addEventListener('dblclick', () => {
            window.location.href = `admin.php?section=tests&action=edit&test_id=${test.uuid}`;
        });

        tbody.appendChild(row);
    });

    // gắn sự kiện cho các nút hành động
    document.querySelectorAll('.edit-info-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const data = e.target.dataset;
            openEditModal(data.uuid, data.title, data.premium === '1', data.active === '1');
        });
    });

    document.querySelectorAll('.delete-test-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const uuid = e.target.dataset.uuid;
            deleteTest(uuid);
        });
    });

    document.querySelectorAll('.approve-test-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const uuid = e.target.dataset.uuid;
            approveTest(uuid);
        });
    });
}

// phê duyệt và kích hoạt đề thi
async function approveTest(uuid) {
    if (!confirm('Bạn có chắc chắn muốn duyệt và kích hoạt đề thi này?')) return;
    try {
        const response = await fetch(`/api/admin/tests/${uuid}/activate`, {
            method: 'PUT'
        });
        const result = await response.json();
        if (result.success) {
            alert('Duyệt đề thi thành công');
            loadTestsList();
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('error approving test:', error);
        alert('Có lỗi xảy ra khi phê duyệt đề');
    }
}

// mở modal chỉnh sửa nhanh
function openEditModal(uuid, title, isPremium, isActive) {
    const modal = document.getElementById('editModal');
    document.getElementById('edit_id').value = uuid;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_premium').checked = isPremium;
    document.getElementById('edit_active').checked = isActive;
    modal.classList.add('show');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.classList.remove('show');
}

// xóa đề thi bằng uuid
async function deleteTest(uuid) {
    if (confirm("Bạn có chắc chắn muốn xóa bài thi này không? Toàn bộ câu hỏi liên quan sẽ bị xóa vĩnh viễn.")) {
        try {
            const response = await fetch(`/api/tests/${uuid}`, { method: 'DELETE' });
            const result = await response.json();
            if (result.success) {
                alert("Đã xóa bài thi thành công");
                await loadTestsList();
            } else {
                alert("Lỗi: " + result.message);
            }
        } catch (error) {
            console.error('error deleting test:', error);
            alert("Lỗi khi gửi yêu cầu xóa bài thi");
        }
    }
}

// tải danh sách người dùng phân trang
async function loadUsersList(page) {
    usersState.page = page;
    const search = usersState.search;
    const role = usersState.role;
    const status = usersState.status;
    
    try {
        const response = await fetch(`/api/admin/users?page=${page}&limit=${usersState.limit}&q=${encodeURIComponent(search)}&role=${role}&status=${status}`);
        const result = await response.json();
        
        if (result.success) {
            renderUsersCards(result.data);
            
            // cập nhật thống kê người dùng
            const stats = result.stats;
            document.getElementById('user-stat-total').textContent = stats.total_users;
            document.getElementById('user-stat-new').textContent = stats.new_users_month;
            document.getElementById('user-stat-inactive').textContent = stats.inactive_users_7d;

            usersState.total = result.pagination.total;
            renderPagination('users-pagination', result.pagination, loadUsersList);
        }
    } catch (error) {
        console.error('error loading users:', error);
    }
}

// hiển thị danh sách người dùng dạng thẻ (mockup design)
function renderUsersCards(users) {
    const grid = document.getElementById('userCardsGrid');
    if (!grid) return;

    grid.innerHTML = '';
    if (users.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1 / -1;" class="text-center text-muted">Không tìm thấy người dùng nào</div>';
        return;
    }

    users.forEach(user => {
        const fullName = `${user.first_name} ${user.last_name}`;
        const isPremium = parseInt(user.is_premium) === 1;
        const isBanned = parseInt(user.is_banned) === 1;
        
        // tiến độ làm đề thi
        const attempted = parseInt(user.user_tests_attempted) || 0;
        const total = parseInt(user.total_active_tests) || 1;
        const percentage = Math.min(100, Math.round((attempted / total) * 100));

        // màu sắc tiến trình học tập
        let progressClass = 'red';
        if (percentage >= 80) progressClass = 'green';
        else if (percentage >= 40) progressClass = 'orange';

        // nhãn hiển thị gói premium
        let planBadge = '<span class="badge inactive">Thường</span>';
        if (isPremium) {
            planBadge = `<span class="badge success">${user.premium_plan || 'Pro'}</span>`;
        }

        const card = document.createElement('div');
        card.className = 'user-card';
        card.innerHTML = `
            <div>
                <div class="card-top">
                    <div class="user-avatar">
                        ${user.first_name[0] || 'U'}
                    </div>
                    <div class="user-meta-info">
                        <div class="user-meta-name">${fullName}</div>
                        <div class="user-meta-role">${user.role === 'admin' ? 'Quản trị viên' : 'Học viên'} ${planBadge}</div>
                    </div>
                </div>

                <div class="progress-section">
                    <div class="progress-header">
                        <span>Tiến trình học tập</span>
                        <strong>${attempted}/${total} đề (${percentage}%)</strong>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar ${progressClass}" style="width: ${percentage}%;"></div>
                    </div>
                </div>
                
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                    <div>Email: <code>${user.email}</code></div>
                    <div style="margin-top: 4px;">Ngày đăng ký: ${formatDate(user.created_at)}</div>
                </div>
            </div>

            <div class="card-footer">
                <span class="badge ${isBanned ? 'failed' : 'success'}">
                    <i class="bx ${isBanned ? 'bx-block' : 'bx-check-circle'}" style="margin-right: 4px;"></i> 
                    ${isBanned ? 'Khóa' : 'Hoạt động'}
                </span>
                
                <div style="display: flex; gap: 6px; align-items: center;">
                    <select class="action-select role-select" style="padding: 4px 6px; font-size: 12px;" data-id="${user.id}">
                        <option value="user" ${user.role === 'user' ? 'selected' : ''}>Học viên</option>
                        <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Quản trị</option>
                    </select>
                    <button class="btn-primary ban-btn" style="padding: 5px 10px; font-size: 11px; background-color: ${isBanned ? 'var(--accent-green)' : 'var(--accent-red)'};" data-id="${user.id}" data-banned="${isBanned ? '0' : '1'}">
                        ${isBanned ? 'Bỏ' : 'Khóa'}
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });

    // gắn sự kiện thay đổi trực tuyến
    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', async (e) => {
            const userId = e.target.dataset.id;
            const newRole = e.target.value;
            await updateUserField(userId, { role: newRole });
        });
    });

    document.querySelectorAll('.ban-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const userId = e.target.dataset.id;
            const newBanned = parseInt(e.target.dataset.banned);
            const msg = newBanned === 1 ? "Bạn có chắc chắn muốn khóa tài khoản này không?" : "Bạn muốn bỏ khóa tài khoản này?";
            if (confirm(msg)) {
                await updateUserField(userId, { is_banned: newBanned });
            }
        });
    });
}

// cập nhật vai trò hoặc trạng thái khóa qua API
async function updateUserField(userId, data) {
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            await loadUsersList(usersState.page);
        } else {
            alert("Lỗi: " + result.message);
        }
    } catch (error) {
        console.error('error updating user status:', error);
    }
}

// tải danh sách lượt làm bài phân trang
async function loadAttemptsList(page) {
    attemptsState.page = page;
    try {
        const response = await fetch(`/api/admin/attempts?page=${page}&limit=${attemptsState.limit}`);
        const result = await response.json();
        if (result.success) {
            renderAttemptsTable(result.data);
            attemptsState.total = result.pagination.total;
            renderPagination('attempts-pagination', result.pagination, loadAttemptsList);
        }
    } catch (error) {
        console.error('error loading attempts:', error);
    }
}

// hiển thị danh sách lượt làm bài
function renderAttemptsTable(attempts) {
    const tbody = document.getElementById('attemptTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (attempts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không tìm thấy bài thi thử nào</td></tr>';
        return;
    }

    attempts.forEach(attempt => {
        const isPremium = parseInt(attempt.is_premium) === 1 || attempt.premium_plan !== null;
        const correctText = `L: ${attempt.listening_correct} | R: ${attempt.reading_correct}`;
        const scoreText = `L: ${attempt.listening_score} | R: ${attempt.reading_score} | T: ${attempt.total_score}`;
        
        // tính toán tỷ lệ tiến trình làm bài
        const attempted = parseInt(attempt.user_tests_attempted) || 0;
        const total = parseInt(attempt.total_active_tests) || 1;
        const percentage = Math.min(100, Math.round((attempted / total) * 100));

        // màu tiến trình nhỏ trong cột bảng
        let progressClass = 'red';
        if (percentage >= 80) progressClass = 'green';
        else if (percentage >= 40) progressClass = 'orange';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div>
                    <div style="font-weight: 600;">${attempt.first_name} ${attempt.last_name}</div>
                    <div style="font-size: 11px; color: var(--text-secondary);">${attempt.email}</div>
                    ${isPremium ? '<span class="badge success" style="font-size: 9px; padding: 2px 4px; margin-top: 4px;">Pro</span>' : ''}
                </div>
            </td>
            <td><strong>${attempt.title}</strong></td>
            <td>${correctText}</td>
            <td><strong style="color: var(--accent-blue);">${attempt.total_score}</strong> <span style="font-size: 11px; color: var(--text-secondary);">(${scoreText})</span></td>
            <td>${formatTimeSpent(attempt.time_spent)}</td>
            <td style="min-width: 140px;">
                <div>
                    <div class="progress-bar-container" style="height: 4px;">
                        <div class="progress-bar ${progressClass}" style="width: ${percentage}%;"></div>
                    </div>
                    <span style="font-size: 10.5px; color: var(--text-secondary);">${attempted}/${total} đề (${percentage}%)</span>
                </div>
            </td>
            <td>${formatDateTime(attempt.created_at)}</td>
        `;
        tbody.appendChild(row);
    });
}

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

// hiển thị cấu phần phân trang
function renderPagination(containerId, pagination, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const { page, limit, total } = pagination;
    const totalPages = Math.ceil(total / limit);

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = `
        <div class="pagination-info">
            Hiển thị từ ${(page - 1) * limit + 1} đến ${Math.min(page * limit, total)} trong tổng số ${total} bản ghi
        </div>
        <div class="pagination-buttons">
            <button class="page-btn" ${page === 1 ? 'disabled' : ''} data-page="${page - 1}">
                <i class="bx bx-chevron-left"></i>
            </button>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
            html += `<button class="page-btn ${i === page ? 'active' : ''}" data-page="${i}">${i}</button>`;
        } else if (i === page - 3 || i === page + 3) {
            html += `<span style="padding: 0 4px; display: inline-flex; align-items: center; color: var(--text-secondary);">...</span>`;
        }
    }

    html += `
            <button class="page-btn" ${page === totalPages ? 'disabled' : ''} data-page="${page + 1}">
                <i class="bx bx-chevron-right"></i>
            </button>
        </div>
    `;

    container.innerHTML = html;

    // gắn sự kiện chuyển trang
    container.querySelectorAll('.page-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const btnTarget = e.currentTarget;
            if (btnTarget.hasAttribute('disabled')) return;
            const targetPage = parseInt(btnTarget.dataset.page);
            onPageChange(targetPage);
        });
    });
}

// logic chuyển tab
function setupTabSwitching() {
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    const sections = document.querySelectorAll('.section-content');

    sidebarItems.forEach(item => {
        item.addEventListener('click', () => {
            const sectionName = item.dataset.section;
            if (!sectionName) return;

            // không chuyển trang nếu đang soạn đề để tránh mất dữ liệu
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            if (action && sectionName !== 'tests') {
                if (!confirm("Các thay đổi chưa lưu trên đề thi sẽ bị mất, bạn có chắc chắn muốn chuyển trang?")) {
                    return;
                }
            }

            // cập nhật đường dẫn URL
            history.pushState(null, '', `admin.php?section=${sectionName}`);

            // chuyển đổi trạng thái active
            sidebarItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            sections.forEach(s => s.classList.remove('active'));
            const targetSection = document.getElementById(`section-${sectionName}`);
            if (targetSection) {
                targetSection.classList.add('active');
            }

            // tải dữ liệu cho tab
            loadSectionData(sectionName);
        });
    });
}

// khởi tạo trạng thái tài liệu
document.addEventListener("DOMContentLoaded", () => {
    setupTabSwitching();

    // phân tích tab mặc định từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialSection = urlParams.get('section') || 'overview';
    
    // tải dữ liệu cho tab mặc định
    loadSectionData(initialSection);

    // cập nhật doanh số mục tiêu ở sidebar bằng dữ liệu thực tế ngay khi tải trang
    fetch('/api/admin/revenue')
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                updateSidebarWidget(result.data.current_month);
            }
        })
        .catch(err => console.error('error loading target widget:', err));

    // thiết lập đóng modal nếu đang ở danh sách đề thi
    const editForm = document.getElementById('editForm');
    if (editForm) {
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const modal = document.getElementById('editModal');

        const closeModal = () => closeEditModal();
        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        // gửi form chỉnh sửa đề thi
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const uuid = document.getElementById('edit_id').value;
            const title = document.getElementById('edit_title').value;
            const isPremium = document.getElementById('edit_premium').checked ? 1 : 0;
            const isActive = document.getElementById('edit_active').checked ? 1 : 0;

            try {
                const response = await fetch(`/api/tests/${uuid}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: title,
                        is_premium: isPremium,
                        is_active: isActive
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert("Đã lưu thông tin đề thi thành công");
                    closeEditModal();
                    await loadTestsList();
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                console.error('error updating test metadata:', error);
            }
        });
    }

    // tìm kiếm và lọc đề thi
    const searchTests = document.getElementById('search-tests');
    if (searchTests) {
        searchTests.addEventListener('input', filterTests);
        document.getElementById('filter-tests-premium').addEventListener('change', filterTests);
        document.getElementById('filter-tests-status').addEventListener('change', filterTests);
    }

    // tìm kiếm người dùng và các bộ lọc dropdown
    const searchUsers = document.getElementById('search-users');
    const filterUsersRole = document.getElementById('filter-users-role');
    const filterUsersStatus = document.getElementById('filter-users-status');

    function handleUsersFilterChange() {
        usersState.search = searchUsers ? searchUsers.value.trim() : '';
        usersState.role = filterUsersRole ? filterUsersRole.value : '';
        usersState.status = filterUsersStatus ? filterUsersStatus.value : '';
        loadUsersList(1);
    }

    if (searchUsers) {
        let debounceTimer;
        searchUsers.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(handleUsersFilterChange, 300);
        });
    }
    if (filterUsersRole) filterUsersRole.addEventListener('change', handleUsersFilterChange);
    if (filterUsersStatus) filterUsersStatus.addEventListener('change', handleUsersFilterChange);

    // thiết lập modal import đề thi
    const openImportModalBtn = document.getElementById('openImportModalBtn');
    const importModal = document.getElementById('importModal');
    const closeImportModalBtn = document.getElementById('closeImportModalBtn');
    const cancelImportModalBtn = document.getElementById('cancelImportModalBtn');
    const importForm = document.getElementById('importForm');
    const importLoading = document.getElementById('importLoading');
    const importMessage = document.getElementById('importMessage');

    const importRadios = document.querySelectorAll('input[name="import_type"]');
    const singleFilesGroup = document.getElementById('single-files-group');
    const splitFilesGroup = document.getElementById('split-files-group');
    const importExamFile = document.getElementById('import_exam_file');
    const importAnswerFile = document.getElementById('import_answer_file');
    const importListeningFile = document.getElementById('import_listening_file');
    const importListeningAnswerFile = document.getElementById('import_listening_answer_file');
    const importReadingFile = document.getElementById('import_reading_file');
    const importReadingAnswerFile = document.getElementById('import_reading_answer_file');

    const handleImportTypeChange = () => {
        const selectedType = document.querySelector('input[name="import_type"]:checked')?.value || 'single';
        if (selectedType === 'single') {
            if (singleFilesGroup) singleFilesGroup.style.display = 'block';
            if (splitFilesGroup) splitFilesGroup.style.display = 'none';
            if (importExamFile) importExamFile.required = true;
            if (importAnswerFile) importAnswerFile.required = true;
            if (importListeningFile) importListeningFile.required = false;
            if (importListeningAnswerFile) importListeningAnswerFile.required = false;
            if (importReadingFile) importReadingFile.required = false;
            if (importReadingAnswerFile) importReadingAnswerFile.required = false;
        } else {
            if (singleFilesGroup) singleFilesGroup.style.display = 'none';
            if (splitFilesGroup) splitFilesGroup.style.display = 'block';
            if (importExamFile) importExamFile.required = false;
            if (importAnswerFile) importAnswerFile.required = false;
            if (importListeningFile) importListeningFile.required = true;
            if (importListeningAnswerFile) importListeningAnswerFile.required = true;
            if (importReadingFile) importReadingFile.required = true;
            if (importReadingAnswerFile) importReadingAnswerFile.required = true;
        }
    };

    importRadios.forEach(radio => radio.addEventListener('change', handleImportTypeChange));

    const openImportModal = () => {
        if (importModal) {
            importModal.classList.add('show');
            if (importForm) importForm.reset();
            handleImportTypeChange();
            if (importMessage) {
                importMessage.style.display = 'none';
                importMessage.className = 'import-message-box';
                importMessage.textContent = '';
            }
            if (importLoading) importLoading.style.display = 'none';
        }
    };

    const closeImportModal = () => {
        if (importModal) importModal.classList.remove('show');
    };

    if (openImportModalBtn) openImportModalBtn.addEventListener('click', openImportModal);
    if (closeImportModalBtn) closeImportModalBtn.addEventListener('click', closeImportModal);
    if (cancelImportModalBtn) cancelImportModalBtn.addEventListener('click', closeImportModal);
    if (importModal) {
        importModal.addEventListener('click', (e) => {
            if (e.target === importModal) closeImportModal();
        });
    }

    if (importForm) {
        importForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (importLoading) importLoading.style.display = 'block';
            if (importMessage) importMessage.style.display = 'none';

            const formData = new FormData(importForm);

            try {
                const response = await fetch('/api/admin/import', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (importLoading) importLoading.style.display = 'none';
                
                if (importMessage) {
                    importMessage.style.display = 'block';
                    if (result.success) {
                        importMessage.className = 'import-message-box success';
                        importMessage.textContent = result.message || 'Import đề thi thành công';
                        importForm.reset();
                        await loadTestsList();
                        // đóng modal sau 2 giây thành công
                        setTimeout(closeImportModal, 2000);
                    } else {
                        importMessage.className = 'import-message-box error';
                        importMessage.textContent = result.message || 'Có lỗi xảy ra khi import đề thi';
                    }
                }
            } catch (error) {
                console.error('error importing exam:', error);
                if (importLoading) importLoading.style.display = 'none';
                if (importMessage) {
                    importMessage.style.display = 'block';
                    importMessage.className = 'import-message-box error';
                    importMessage.textContent = 'Lỗi kết nối máy chủ hoặc quá tải dung lượng file';
                }
            }
        });
    }
});
