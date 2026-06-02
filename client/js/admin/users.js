

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

        let avatarHtml = '';
        if (user.avatar) {
            avatarHtml = `<img src="${user.avatar}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border-color);">`;
        } else {
            avatarHtml = `
                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 13px; display: flex; align-items: center; justify-content: center; background-color: var(--accent-blue); color: #ffffff; border-radius: 50%; font-weight: 600; flex-shrink: 0; border: 1px solid var(--border-color);">
                    ${user.first_name[0] || 'U'}
                </div>`;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="text-align: center; vertical-align: middle;">
                ${parseInt(user.id) === window.adminUserId ? '' : `
                    <input type="checkbox" class="user-select-checkbox" data-id="${user.id}" style="cursor: pointer; width: 16px; height: 16px; margin: 0;">
                `}
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    ${avatarHtml}
                    <strong>${fullName}</strong>
                </div>
            </td>
            <td><code>${user.email}</code></td>
            <td><span class="badge ${user.role === 'admin' ? 'warning' : 'secondary'}">${user.role === 'admin' ? 'Quản trị viên' : 'Học viên'}</span></td>
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
                    <button class="btn-primary delete-btn" style="padding: 5px 10px; font-size: 11px; background-color: var(--accent-red); border-color: var(--accent-red); ${parseInt(user.id) === window.adminUserId ? 'visibility: hidden;' : ''}" data-id="${user.id}">
                        Xóa
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    // gắn sự kiện thay đổi chọn checkbox
    tbody.querySelectorAll('.user-select-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkActionsToolbar);
    });

    // cập nhật lại trạng thái toolbar mỗi khi vẽ lại bảng
    updateBulkActionsToolbar();

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
            const confirmed = await showConfirmModal(
                newBanned === 1 ? "Khóa tài khoản" : "Mở khóa tài khoản",
                msg,
                newBanned === 1 ? 'danger' : 'warning'
            );
            if (confirmed) {
                await updateUserField(userId, { is_banned: newBanned });
            }
        });
    });

    tbody.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const userId = parseInt(e.target.dataset.id);
            if (userId === window.adminUserId) return;
            const confirmed = await showConfirmModal(
                "Xóa tài khoản",
                "Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản người dùng này không? Tất cả lịch sử làm bài và giao dịch sẽ bị xóa và hành động này không thể hoàn tác",
                "danger",
                "Xác nhận xóa"
            );
            if (confirmed) {
                await deleteUser(userId);
            }
        });
    });
}

// cập nhật trạng thái hiển thị của thanh bulk actions
function updateBulkActionsToolbar() {
    const checkboxes = document.querySelectorAll('.user-select-checkbox');
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    const toolbar = document.getElementById('bulk-actions-toolbar');
    const countEl = document.getElementById('selected-users-count');
    const selectAllCheckbox = document.getElementById('select-all-users');

    if (!toolbar) return;

    if (checked.length > 0) {
        toolbar.style.display = 'flex';
        countEl.textContent = `Đã chọn ${checked.length} học viên`;
    } else {
        toolbar.style.display = 'none';
    }

    // cập nhật trạng thái checkbox chọn tất cả
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
    }
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
            await showAlertModal("Lỗi", result.message, "error");
        }
    } catch (error) {
        console.error('error updating user status:', error);
    }
}

// xóa người dùng qua API
async function deleteUser(userId) {
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        if (result.success) {
            await showAlertModal("Thành công", result.message || 'Xóa tài khoản thành công', "success");
            await loadUsersList(usersState.page);
        } else {
            await showAlertModal("Lỗi", result.message, "error");
        }
    } catch (error) {
        console.error('error deleting user:', error);
        await showAlertModal("Lỗi", "Có lỗi xảy ra khi xóa tài khoản", "error");
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

    // xử lý chọn tất cả học viên
    const selectAllCheckbox = document.getElementById('select-all-users');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (e) => {
            const checked = e.target.checked;
            document.querySelectorAll('.user-select-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            updateBulkActionsToolbar();
        });
    }

    // xử lý sự kiện click cho các nút thao tác hàng loạt
    const bulkLockBtn = document.getElementById('bulk-lock-btn');
    const bulkUnlockBtn = document.getElementById('bulk-unlock-btn');
    const bulkAdminBtn = document.getElementById('bulk-admin-btn');
    const bulkUserBtn = document.getElementById('bulk-user-btn');

    const handleBulkAction = async (payload, actionText) => {
        const checkedBoxes = document.querySelectorAll('.user-select-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.dataset.id));
        if (ids.length === 0) return;

        const confirmed = await showConfirmModal(
            "Thao tác hàng loạt",
            `Bạn có chắc chắn muốn ${actionText} ${ids.length} tài khoản đã chọn?`,
            payload.is_banned === 1 ? 'danger' : 'warning'
        );
        if (!confirmed) {
            return;
        }

        try {
            const response = await fetch('/api/admin/users', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ids: ids,
                    ...payload
                })
            });
            const result = await response.json();
            if (result.success) {
                await showAlertModal("Thành công", result.message || 'Thao tác hàng loạt thành công', "success");
                const selectAllCheckbox = document.getElementById('select-all-users');
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                await loadUsersList(usersState.page);
            } else {
                await showAlertModal("Lỗi", result.message, "error");
            }
        } catch (error) {
            console.error('error executing bulk action:', error);
            await showAlertModal("Lỗi", "Có lỗi xảy ra khi thực hiện thao tác hàng loạt", "error");
        }
    };

    if (bulkLockBtn) bulkLockBtn.addEventListener('click', () => handleBulkAction({ is_banned: 1 }, 'khóa'));
    if (bulkUnlockBtn) bulkUnlockBtn.addEventListener('click', () => handleBulkAction({ is_banned: 0 }, 'mở khóa'));
    if (bulkAdminBtn) bulkAdminBtn.addEventListener('click', () => handleBulkAction({ role: 'admin' }, 'nâng lên admin'));
    if (bulkUserBtn) bulkUserBtn.addEventListener('click', () => handleBulkAction({ role: 'user' }, 'hạ xuống học viên'));
});
