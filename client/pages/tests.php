<?php
//Kiểm tra xem user có đăng nhập hay chưa
//Tránh việc lên URL gõ tests.php là ra trang này
session_start();
require_once '../../server/middleware/auth.php';
//homeRedirect();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include './components/metadata.php'; ?>
    <title>Danh sách đề thi</title>
    <link rel="stylesheet" href="../styles/testPage.css">
</head>

<body>
    <!-- INCLUDE NAVBAR FILE -->
    <?php include './components/navbar.php'; ?>
    <div class="page">
        <div class="hero">
            <div class="container-fluid">
                <h1>Danh sách đề thi</h1>
                <p>Luyện thi TOEIC với bộ đề đa dạng</p>
            </div>
        </div>
        <div class="content">
            <!-- Thanh filter được gắn ở trên -->
            <div class="toolbar">
                <div class="filter-tabs">
                    <button class="filter-tab active">Tất cả</button>
                    <button class="filter-tab">Listening</button>
                    <button class="filter-tab">Reading</button>
                    <button class="filter-tab">Grammar</button>
                    <button class="filter-tab">Vocabulary</button>
                    <button class="filter-tab">Full Test</button>
                    <button class="filter-tab">Mini Test</button>
                </div>
                <div class="right-tools">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input class="search-input" placeholder="Tìm kiếm đề thi..." />
                    </div>
                    <!-- Thanh menu dropdown -->
                    <select class="sort-select">
                        <option>Mới nhất</option>
                        <option>Phổ biến nhất</option>
                        <option>Điểm TB cao nhất</option>
                    </select>
                </div>
            </div>
            <div class="section-label">Đề thi kiểm tra năng lực</div>
            <!-- Lưới hiển thị đề thi -->
            <div class="test-grid grid-free">
                <!-- Ô đề thi FREE------------------------------------------------------>

            </div>
            <div class="section-label">Đề thi ôn cấp tốc TOEIC 2026</div>
            <!-- Lưới hiển thị đề thi -->
            <div class="test-grid grid-premium">
                <!-- Ô đề thi PREMIUM------------------------------------------------------>

            </div>
            <div class="modal fade" id="confirmExamModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <div class="modal-header" style="border-bottom: 0.5px solid #eaeaea; padding: 1.25rem 1.5rem;">
                            <h5 class="modal-title" style="font-weight: 700; color: #05102B; font-size: 18px;">Xác nhận làm bài</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="padding: 1.5rem; color: #555; font-size: 15px;">
                            Bạn đã sẵn sàng bắt đầu làm bài thi chưa? Thời gian đếm ngược sẽ được tính ngay khi bạn vào trang.
                        </div>
                        <div class="modal-footer" style="border-top: none; padding: 1rem 1.5rem 1.5rem;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #f0f2f5; color: #444; border: none; font-weight: 600; padding: 8px 16px;">Hủy</button>
                            <a href="#" id="btnConfirmStartExam" class="btn btn-primary" style="background: #05102B; color: #fff; border: none; font-weight: 600; padding: 8px 16px;">Bắt đầu ngay</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INCLUDE FOOTER FILE -->
    <?php include './components/footer.php'; ?>
    <script>
        window.isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/tests.js"></script>
</body>

</html>