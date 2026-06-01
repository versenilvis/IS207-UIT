<?php
/**
 * Admin dashboard page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// kiểm tra quyền admin phía PHP
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: home.php");
    exit();
}

$section = $_GET['section'] ?? 'overview';
$action = $_GET['action'] ?? '';
$test_id = $_GET['test_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrepHub Admin Dashboard</title>
    <?php include './components/metadata.php'; ?>
    <link href="../styles/adminStyle.css?v=<?= time() ?>" rel="stylesheet">
    <?php if ($section === 'tests' && ($action === 'create' || $action === 'edit')): ?>
        <link href="../styles/questionsStyle.css" rel="stylesheet">
    <?php endif; ?>
    <!-- tải thư viện chart.js cdn vẽ biểu đồ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
</head>
<body>

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

    <!-- vùng hiển thị nội dung chính -->
    <main class="main-content">

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

        <!-- phân hệ: đề thi -->
        <section id="section-tests" class="section-content <?php echo $section === 'tests' ? 'active' : ''; ?>">
            <?php if ($action === 'create' || $action === 'edit'): ?>
                <div class="page-header">
                    <div class="page-title-container">
                        <div class="breadcrumbs">
                            <span>Quản lý đề thi</span>
                            <i class="bx bx-chevron-right"></i>
                            <span><?php echo $action === 'create' ? 'Tạo mới' : 'Biên soạn'; ?></span>
                        </div>
                        <h1 class="page-title"><?php echo $action === 'create' ? 'Tạo Đề Thi Mới' : 'Biên Soạn Câu Hỏi'; ?></h1>
                    </div>
                    <a href="admin.php?section=tests" class="btn-primary" style="background-color: var(--text-secondary);">
                        <i class="bx bx-chevron-left"></i> Quay lại danh sách
                    </a>
                </div>

                <div class="container-wrapper">
                    <!-- form tạo đề thi -->
                    <?php include('./components/questions/test-form.php'); ?>

                    <!-- cấu hình đề và phần thi -->
                    <?php include('./components/questions/test-config.php'); ?>

                    <!-- các nút hành động -->
                    <?php include('./components/questions/action-buttons.php'); ?>

                    <!-- vùng chứa danh sách câu hỏi -->
                    <div id="questions-container"></div>
                </div>

                <!-- các mẫu câu hỏi ẩn -->
                <?php include('./components/questions/question-templates.php'); ?>
            <?php else: ?>
                <div class="page-header">
                    <div class="page-title-container">
                        <div class="breadcrumbs">
                            <span>Quản lý</span>
                            <i class="bx bx-chevron-right"></i>
                            <span>Đề thi</span>
                        </div>
                        <h1 class="page-title">Danh Sách Đề Thi</h1>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-primary" style="background-color: var(--accent-orange); cursor: pointer;" id="openImportModalBtn">
                            <i class="bx bx-upload"></i> Import Đề từ HTML
                        </button>
                    </div>
                </div>

                <div class="filter-toolbar">
                    <input type="text" id="search-tests" class="search-input" placeholder="Tìm kiếm theo tiêu đề...">
                    <select id="filter-tests-premium" class="select-filter">
                        <option value="">Tất cả phân loại</option>
                        <option value="Premium">Premium</option>
                        <option value="Thường">Thường</option>
                    </select>
                    <select id="filter-tests-status" class="select-filter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Hoạt động">Hoạt động</option>
                        <option value="Tạm ẩn">Tạm ẩn</option>
                    </select>
                </div>

                <div class="table-container" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tiêu đề</th>
                                <th>Phân loại</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th style="text-align: right;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="testTableBody">
                            <!-- hiển thị bằng JS -->
                        </tbody>
                    </table>
                </div>

                <!-- modal sửa thông tin đề thi -->
                <div class="modal-overlay" id="editModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Chỉnh Sửa Đề Thi</h3>
                            <button class="close-btn" id="closeModalBtn">&times;</button>
                        </div>
                        <form id="editForm">
                            <div class="modal-body">
                                <input type="hidden" id="edit_id">
                                <div class="form-group">
                                    <label>Tiêu đề</label>
                                    <input type="text" id="edit_title" required>
                                </div>
                                <div class="checkbox-group-wrapper">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="edit_premium">
                                        <label for="edit_premium">Premium</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="edit_active">
                                        <label for="edit_active">Hoạt động</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-danger" id="btnDelete" style="margin-right: auto;">Xóa</button>
                                <button type="button" class="btn-primary" style="background-color: var(--text-secondary);" id="cancelModalBtn">Hủy</button>
                                <button type="submit" class="btn-primary">Lưu lại</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- modal import đề thi từ html -->
                <div class="modal-overlay" id="importModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Import Đề Thi từ HTML</h3>
                            <button class="close-btn" id="closeImportModalBtn">&times;</button>
                        </div>
                        <form id="importForm" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Loại hình đề thi</label>
                                    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                        <label style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; margin-bottom: 0;">
                                            <input type="radio" name="import_type" value="single" checked style="width: auto; height: auto; margin: 0;">
                                            <span>Đề Đơn (Nghe, Đọc hoặc đề gộp sẵn)</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; margin-bottom: 0;">
                                            <input type="radio" name="import_type" value="split" style="width: auto; height: auto; margin: 0;">
                                            <span>Đề Ghép (Listening + Reading tách rời)</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="single-files-group">
                                    <div class="form-group">
                                        <label>File HTML đề thi <span style="color: var(--accent-red);">*</span></label>
                                        <input type="file" id="import_exam_file" name="exam_file" accept=".html" required>
                                        <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đề thi lưu dạng "Web Page, Complete" (.html)</small>
                                    </div>
                                    <div class="form-group" style="margin-top: 12px;">
                                        <label>File HTML đáp án <span style="color: var(--accent-red);">*</span></label>
                                        <input type="file" id="import_answer_file" name="answer_file" accept=".html" required>
                                        <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đáp án tương ứng lưu dạng .html</small>
                                    </div>
                                </div>

                                <div id="split-files-group" style="display: none;">
                                    <div class="form-group">
                                        <label>File HTML Listening đề thi <span style="color: var(--accent-red);">*</span></label>
                                        <input type="file" id="import_listening_file" name="listening_file" accept=".html">
                                        <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đề thi Listening lưu dạng .html</small>
                                    </div>
                                    <div class="form-group" style="margin-top: 12px;">
                                        <label>File HTML Listening đáp án <span style="color: var(--accent-red);">*</span></label>
                                        <input type="file" id="import_listening_answer_file" name="listening_answer_file" accept=".html">
                                        <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đáp án Listening lưu dạng .html</small>
                                    </div>
                                    <div class="form-group" style="margin-top: 12px;">
                                        <label>File HTML Reading đề thi <span style="color: var(--accent-red);">*</span></label>
                                        <input type="file" id="import_reading_file" name="reading_file" accept=".html">
                                        <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đề thi Reading lưu dạng .html</small>
                                    </div>
                                    <div class="form-group" style="margin-top: 12px;">
                                        <label>File HTML Reading đáp án <span style="color: var(--accent-red);">*</span></label>
                                        <input type="file" id="import_reading_answer_file" name="reading_answer_file" accept=".html">
                                        <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đáp án Reading lưu dạng .html</small>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 12px;">
                                    <label>File ZIP hình ảnh / âm thanh đề thi (không bắt buộc)</label>
                                    <input type="file" id="import_media_file" name="media_file" accept=".zip">
                                    <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp nén .zip chứa thư mục media của trang đề thi</small>
                                </div>
                                <div class="form-group" style="margin-top: 12px;">
                                    <label>File ZIP hình ảnh / âm thanh đáp án (không bắt buộc)</label>
                                    <input type="file" id="import_media_answer_file" name="media_answer_file" accept=".zip">
                                    <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp nén .zip chứa thư mục media của trang đáp án</small>
                                </div>
                                <div class="checkbox-group-wrapper" style="margin-top: 16px;">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="import_premium" name="is_premium" value="1">
                                        <label for="import_premium">Đặt là đề thi Premium</label>
                                    </div>
                                </div>
                                <div id="importLoading" style="display: none; text-align: center; margin-top: 15px; color: var(--accent-blue); font-weight: 600;">
                                    <i class="bx bx-loader-alt bx-spin" style="margin-right: 5px;"></i> Đang giải nén & import đề thi, vui lòng đợi...
                                </div>
                                <div id="importMessage" style="display: none; margin-top: 15px; padding: 10px; border-radius: 6px; font-size: 13px;"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-primary" style="background-color: var(--text-secondary);" id="cancelImportModalBtn">Hủy</button>
                                <button type="submit" class="btn-primary" id="btnSubmitImport">Xử lý & Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </section>

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

            <!-- danh sách người dùng hiển thị dưới dạng thẻ premium theo thiết kế mockup -->
            <div class="cards-grid" id="userCardsGrid">
                <!-- hiển thị bằng JS -->
            </div>

            <div class="pagination-wrapper" id="users-pagination">
                <!-- phân trang người dùng -->
            </div>
        </section>

        <!-- phân hệ: lượt thi -->
        <section id="section-attempts" class="section-content <?php echo $section === 'attempts' ? 'active' : ''; ?>">
            <div class="page-header">
                <div class="page-title-container">
                    <div class="breadcrumbs">
                        <span>Quản lý</span>
                        <i class="bx bx-chevron-right"></i>
                        <span>Lượt thi</span>
                    </div>
                    <h1 class="page-title">Lịch Sử Làm Bài</h1>
                </div>
            </div>

            <div class="table-container" style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Người làm</th>
                            <th>Bài thi</th>
                            <th>Số câu đúng</th>
                            <th>Tổng điểm</th>
                            <th>Thời gian làm</th>
                            <th>Tiến trình thi thử</th>
                            <th>Ngày nộp</th>
                        </tr>
                    </thead>
                    <tbody id="attemptTableBody">
                        <!-- hiển thị bằng JS -->
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="attempts-pagination">
                <!-- phân trang lượt thi -->
            </div>
        </section>

        <!-- phân hệ: doanh thu -->
        <section id="section-revenue" class="section-content <?php echo $section === 'revenue' ? 'active' : ''; ?>">
            <div class="page-header">
                <div class="page-title-container">
                    <div class="breadcrumbs">
                        <span>Dòng tiền</span>
                        <i class="bx bx-chevron-right"></i>
                        <span>Doanh thu</span>
                    </div>
                    <h1 class="page-title">Doanh Thu & Lịch Sử Giao Dịch</h1>
                </div>
            </div>

            <div class="stats-grid grid-4">
                <div class="stat-card theme-green">
                    <div class="stat-info">
                        <h3>Doanh thu tháng này</h3>
                        <p id="revenue-stat-month">...</p>
                    </div>
                    <div class="stat-icon">
                        <i class="bx bx-trending-up"></i>
                    </div>
                </div>
                <div class="stat-card theme-blue">
                    <div class="stat-info">
                        <h3>Doanh thu mọi thời gian</h3>
                        <p id="revenue-stat-alltime">...</p>
                    </div>
                    <div class="stat-icon">
                        <i class="bx bx-chart-line"></i>
                    </div>
                </div>
                <div class="stat-card theme-orange">
                    <div class="stat-info">
                        <h3>Giao dịch thành công</h3>
                        <p id="revenue-stat-success-count">...</p>
                    </div>
                    <div class="stat-icon">
                        <i class="bx bx-badge-check"></i>
                    </div>
                </div>
                <div class="stat-card theme-red">
                    <div class="stat-info">
                        <h3>Đã hoàn tiền</h3>
                        <p id="revenue-stat-refund-amount">...</p>
                        <span id="revenue-stat-refund-count" style="font-size: 12px; color: #b91c1c; font-weight: 500; display: block; margin-top: 4px;">...</span>
                    </div>
                    <div class="stat-icon">
                        <i class="bx bx-refresh-ccw"></i>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>

            <div class="table-container" style="overflow-x: auto; margin-bottom: 32px;">
                <h2 style="font-size: 15px; font-weight: 600; padding: 16px 20px 0 20px;">Phân tích doanh thu theo gói dịch vụ</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Gói dịch vụ</th>
                            <th>Lượt mua thành công</th>
                            <th>Doanh thu gộp</th>
                            <th>Lượt hoàn tiền</th>
                            <th>Tổng hoàn tiền</th>
                            <th>Doanh thu ròng</th>
                            <th>Tỷ lệ hoàn tiền</th>
                        </tr>
                    </thead>
                    <tbody id="revenueBreakdownTableBody">
                        <!-- hiển thị bằng JS -->
                    </tbody>
                </table>
            </div>

            <div class="table-container" style="overflow-x: auto;">
                <h2 style="font-size: 15px; font-weight: 600; padding: 16px 20px 0 20px;">Lịch sử giao dịch thành công</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mã giao dịch</th>
                            <th>Khách hàng</th>
                            <th>Gói mua</th>
                            <th>Số tiền</th>
                            <th>Thời hạn</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTableBody">
                        <!-- hiển thị bằng JS -->
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="transactions-pagination">
                <!-- phân trang giao dịch -->
            </div>
        </section>

    </main>

    <!-- điều khiển javascript -->
    <?php if ($section === 'tests' && ($action === 'create' || $action === 'edit')): ?>
        <!-- tải các file script phục vụ form câu hỏi -->
        <script src="../js/questions/state.js"></script>
        <script src="../js/questions/ui.js"></script>
        <script src="../js/questions/api.js"></script>
        <script src="../js/questions/form-fill.js"></script>
        <script src="../js/questions/dom-builder.js"></script>
        <script src="../js/questions/validation.js"></script>
        <script src="../js/questions/utils.js"></script>
        <script src="../js/questions/main.js"></script>
    <?php endif; ?>

    <!-- tải file điều khiển chính dashboard -->
    <script src="../js/admin.js?v=<?= time() ?>"></script>

</body>
</html>
