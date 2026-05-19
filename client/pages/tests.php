<?php
session_start();
//Kiểm tra xem user có đăng nhập hay chưa
//Tránh việc lên URL gõ tests.php là ra trang này
require_once '../../server/middleware/auth.php'; 
requireAuth();
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
    <?php include './components/navBar.php'; ?>
    <div class="page">
        <div class="hero">
            <h1>Danh sách đề thi</h1>
            <p>Luyện thi TOEIC với bộ đề đa dạng</p>
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
            <div class="section-label">Miễn phí</div>
            <!-- Lưới hiển thị đề thi -->
            <div class="test-grid grid-free">
                <!-- Ô đề thi FREE------------------------------------------------------>
                
            </div>
            <div class="section-label">Premium</div>
            <!-- Lưới hiển thị đề thi -->
            <div class="test-grid grid-premium">
                <!-- Ô đề thi PREMIUM------------------------------------------------------>

            </div>
        </div>
    </div>

    <!-- INCLUDE FOOTER FILE -->
    <?php include './components/footer.php'; ?>

    <script src="../js/tests.js"></script>
</body>

</html>