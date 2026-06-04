<!-- phân hệ: tổng quan -->
<section id="section-overview" class="section-content <?php echo $section === 'overview' ? 'active' : ''; ?>">
    <div class="page-header">
        <div class="page-title-container">
            <div class="breadcrumbs">
                <span>Quản trị</span>
                <i class="bx bx-chevron-right"></i>
                <span>Tổng quan</span>
            </div>
            <h1 class="page-title">Tổng Quan Dashboard</h1>
        </div>
    </div>
    
    <div class="stats-grid grid-4">
        <div class="stat-card theme-dark">
            <div class="stat-info">
                <h3>Tổng số User</h3>
                <p id="stat-total-users">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-group"></i>
            </div>
        </div>
        <div class="stat-card theme-orange">
            <div class="stat-info">
                <h3>Tổng số Đề thi</h3>
                <p id="stat-total-tests">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-book-content"></i>
            </div>
        </div>
        <div class="stat-card theme-green">
            <div class="stat-info">
                <h3>Tổng doanh thu</h3>
                <p id="stat-total-revenue">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-credit-card"></i>
            </div>
        </div>
        <div class="stat-card theme-blue">
            <div class="stat-info">
                <h3>User đã mua gói</h3>
                <p id="stat-total-purchased">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-credit-card"></i>
            </div>
        </div>
    </div>

    <div class="table-container" style="background-color: var(--bg-card); padding: 24px;">
        <h2 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Hướng dẫn & phím tắt quản trị</h2>
        <p style="margin-bottom: 12px; line-height: 1.6; color: var(--text-secondary);">Chào mừng bạn đến với trang quản trị hệ thống PrepHub. Bạn có thể sử dụng các chức năng sau:</p>
        <ul style="margin-left: 20px; line-height: 1.8; color: var(--text-secondary);">
            <li><strong>Quản lý đề thi</strong>: xem danh sách, cập nhật thông tin nhanh hoặc nhấn đúp chuột vào hàng để biên soạn chi tiết câu hỏi</li>
            <li><strong>Quản lý user</strong>: xem thông tin học viên dạng thẻ premium, cập nhật vai trò hoặc cấm hoạt động nhanh</li>
            <li><strong>Lịch sử làm bài</strong>: theo dõi các bài thi thử mà học viên đã nộp để đánh giá tiến trình học tập</li>
            <li><strong>Dòng tiền</strong>: xem báo cáo doanh thu dưới dạng biểu đồ cột và chi tiết các giao dịch mua gói dịch vụ</li>
        </ul>
    </div>
</section>
