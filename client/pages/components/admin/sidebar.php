<!-- thanh sidebar điều hướng -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="bx bx-dashboard" style="color: var(--accent-blue);"></i>
        <span>PrepHub Admin</span>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-item <?php echo $section === 'overview' ? 'active' : ''; ?>" data-section="overview">
            <a class="sidebar-link">
                <div class="sidebar-link-left">
                    <i class="bx bx-home-alt"></i>
                    <span>Tổng quan</span>
                </div>
            </a>
        </li>
        <li class="sidebar-item <?php echo $section === 'tests' ? 'active' : ''; ?>" data-section="tests">
            <a class="sidebar-link">
                <div class="sidebar-link-left">
                    <i class="bx bx-book-open"></i>
                    <span>Quản lý đề thi</span>
                </div>
            </a>
        </li>
        <li class="sidebar-item <?php echo $section === 'users' ? 'active' : ''; ?>" data-section="users">
            <a class="sidebar-link">
                <div class="sidebar-link-left">
                    <i class="bx bx-user"></i>
                    <span>Quản lý user</span>
                </div>
            </a>
        </li>
        <li class="sidebar-item <?php echo $section === 'attempts' ? 'active' : ''; ?>" data-section="attempts">
            <a class="sidebar-link">
                <div class="sidebar-link-left">
                    <i class="bx bx-history"></i>
                    <span>Lịch sử làm bài</span>
                </div>
            </a>
        </li>
        <li class="sidebar-item <?php echo $section === 'revenue' ? 'active' : ''; ?>" data-section="revenue">
            <a class="sidebar-link">
                <div class="sidebar-link-left">
                    <i class="bx bx-wallet"></i>
                    <span>Dòng tiền</span>
                </div>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <!-- thống kê tiến trình mục tiêu -->
        <div class="sidebar-widget">
            <div class="widget-title">Doanh số mục tiêu tháng</div>
            <div class="widget-value" id="widget-revenue-val">0 VND</div>
            <div class="progress-bar-container">
                <div class="progress-bar" id="widget-revenue-progress" style="width: 0%;"></div>
            </div>
        </div>

        <a href="/client/pages/home.php" class="logout-link" style="color: var(--text-secondary); margin-bottom: 12px; display: flex;">
            <i class="bx bx-arrow-back"></i>
            <span>Về trang chủ</span>
        </a>
        <a href="/server/controllers/log-out.php" class="logout-link">
            <i class="bx bx-log-out"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>
