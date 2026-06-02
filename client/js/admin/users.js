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
            renderUsersTable(result.data);
            
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

// hiển thị danh sách người dùng dưới dạng bảng
function renderUsersTable(users) {
    const tbody = document.getElementById('userTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không tìm thấy người dùng nào</td></tr>';
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

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="user-avatar" style="width: 32px; height: 32px; font-size: 13px; display: flex; align-items: center; justify-content: center; background-color: var(--accent-blue); color: #ffffff; border-radius: 50%; font-weight: 600;">
                        ${user.first_name[0] || 'U'}
                    </div>
                    <strong>${fullName}</strong>
                </div>
            </td>
            <td><code>${user.email}</code></td>
            <td><span class="badge info">${user.role === 'admin' ? 'Quản trị viên' : 'Học viên'}</span></td>
            <td>${planBadge}</td>
            <td style="min-width: 140px;">
                <div>
                    <div class="progress-bar-container" style="height: 6px; margin-bottom: 4px;">
                        <div class="progress-bar ${progressClass}" style="width: ${percentage}%;"></div>
                    </div>
                    <span style="font-size: 11px; color: var(--text-secondary); font-weight: 500;">${attempted}/${total} đề (${percentage}%)</span>
                </div>
            </td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <span class="badge ${isBanned ? 'failed' : 'success'}">
                    <i class="bx ${isBanned ? 'bx-block' : 'bx-check-circle'}" style="margin-right: 4px;"></i> 
                    ${isBanned ? 'Khóa' : 'Hoạt động'}
                </span>
            </td>
            <td style="text-align: right;">
                <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                    <select class="action-select role-select" style="padding: 4px 6px; font-size: 12px;" data-id="${user.id}">
                        <option value="user" ${user.role === 'user' ? 'selected' : ''}>Học viên</option>
                        <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Quản trị</option>
                    </select>
                    <button class="btn-primary ban-btn" style="padding: 5px 10px; font-size: 11px; background-color: ${isBanned ? 'var(--accent-green)' : 'var(--accent-red)'};" data-id="${user.id}" data-banned="${isBanned ? '0' : '1'}">
                        ${isBanned ? 'Bỏ' : 'Khóa'}
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    // gắn sự kiện thay đổi trực tuyến
    tbody.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', async (e) => {
            const userId = e.target.dataset.id;
            const newRole = e.target.value;
            await updateUserField(userId, { role: newRole });
        });
    });

    tbody.querySelectorAll('.ban-btn').forEach(btn => {
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

// thiết lập sự kiện khi DOM sẵn sàng
document.addEventListener("DOMContentLoaded", () => {
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
});
