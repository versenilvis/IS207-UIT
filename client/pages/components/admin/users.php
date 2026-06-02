<!-- phân hệ: người dùng -->
<section id="section-users" class="section-content <?php echo $section === 'users' ? 'active' : ''; ?>">
    <div class="page-header">
        <div class="page-title-container">
            <div class="breadcrumbs">
                <span>Quản lý</span>
                <i class="bx bx-chevron-right"></i>
                <span>Học viên</span>
            </div>
            <h1 class="page-title">Quản Lý Học Viên</h1>
        </div>
    </div>

    <div class="stats-grid grid-3">
        <div class="stat-card theme-blue">
            <div class="stat-info">
                <h3>Tổng User</h3>
                <p id="user-stat-total">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-group"></i>
            </div>
        </div>
        <div class="stat-card theme-green">
            <div class="stat-info">
                <h3>User mới tháng này</h3>
                <p id="user-stat-new">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-user-plus"></i>
            </div>
        </div>
        <div class="stat-card theme-orange">
            <div class="stat-info">
                <h3>Không hoạt động 7 ngày</h3>
                <p id="user-stat-inactive">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-user-x"></i>
            </div>
        </div>
    </div>

    <div class="filter-toolbar">
        <input type="text" id="search-users" class="search-input" placeholder="Tìm theo tên hoặc email...">
        <select id="filter-users-role" class="select-filter">
            <option value="">Tất cả vai trò</option>
            <option value="user">Học viên</option>
            <option value="admin">Quản trị viên</option>
        </select>
        <select id="filter-users-status" class="select-filter">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Hoạt động</option>
            <option value="banned">Bị khóa</option>
        </select>
    </div>

    <!-- thanh thao tác hàng loạt -->
    <div class="bulk-actions-toolbar" id="bulk-actions-toolbar" style="display: none;">
        <span class="selected-count" id="selected-users-count">Đã chọn 0 học viên</span>
        <div class="bulk-buttons">
            <button class="bulk-btn bulk-btn-admin" id="bulk-admin-btn">
                <i class="bx bx-shield"></i> Lên Admin
            </button>
            <button class="bulk-btn bulk-btn-user" id="bulk-user-btn">
                <i class="bx bx-user"></i> Xuống Học viên
            </button>
            <button class="bulk-btn bulk-btn-lock" id="bulk-lock-btn">
                <i class="bx bx-block"></i> Khóa tài khoản
            </button>
            <button class="bulk-btn bulk-btn-unlock" id="bulk-unlock-btn">
                <i class="bx bx-check-circle"></i> Mở khóa
            </button>
        </div>
    </div>

    <!-- danh sách người dùng hiển thị dưới dạng bảng -->
    <div class="table-container" style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center; vertical-align: middle;"><input type="checkbox" id="select-all-users" style="cursor: pointer; width: 16px; height: 16px; margin: 0;"></th>
                    <th>Học viên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Gói dịch vụ</th>
                    <th>Tiến trình học tập</th>
                    <th>Ngày đăng ký</th>
                    <th>Trạng thái</th>
                    <th style="text-align: right; min-width: 160px;">Hành động</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <!-- hiển thị bằng JS -->
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper" id="users-pagination">
        <!-- phân trang người dùng -->
    </div>
</section>
