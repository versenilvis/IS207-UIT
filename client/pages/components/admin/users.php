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

    <!-- danh sách người dùng hiển thị dưới dạng bảng -->
    <div class="table-container" style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
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
