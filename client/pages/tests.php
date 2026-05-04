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
            <p>Luyện thi TOEIC với bộ đề đa dạng — từ mini test đến full test chuẩn quốc tế</p>
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
            <div class="test-grid">
                <!-- Ô đề thi FREE-->
                <div class="test-card">
                    <!-- Title đề thi -->
                    <div class="card-top">
                        <h3 class="card-title">TOEIC Grammar Mini Test</h3>
                        <span class="badge-free">Miễn phí</span>
                    </div>
                    <!-- Hiển thị thời gian và số câu -->
                    <div class="card-meta">
                        <span class="meta-item">
                            <i class="fa-regular fa-clock"></i>
                            30 phút
                        </span>

                        <span class="meta-item">
                            <i class="fa-regular fa-rectangle-list"></i>
                            50 câu
                        </span>
                    </div>
                    <!-- Hiển thị test description -->
                    <p class="card-desc">
                        Bài kiểm tra ngữ pháp tập trung vào các điểm thường gặp trong đề thi TOEIC.
                    </p>
                    <div class="card-tags">
                        <span class="tag">Grammar</span>
                        <span class="tag">Mini Test</span>
                    </div>
                    <!-- Card footer -->
                    <div class="card-footer">
                        <div class="card-stats">
                            <span class="stat-item">
                                <i class="fa-regular fa-user"></i>
                                4,821 lượt
                            </span>
                            <!-- Đường kẻ chia ra giữa tổng số lượt làm bài và điểm trung bình -->
                            <span class="stat-divider"></span>

                            <span class="stat-item score">
                                <i class="fa-regular fa-star"></i>
                                TB 72đ
                            </span>
                        </div>
                        <button class="btn-start">Làm bài</button>
                    </div>
                </div>
            </div>
            <div class="section-label">Premium</div>
            <!-- Lưới hiển thị đề thi -->
            <div class="test-grid">
                <!-- Ô đề thi PREMIUM-->
                <div class="test-card premium">
                    <!-- Title đề thi -->
                    <div class="card-top">
                        <div class="card-title">TOEIC Full Test 2024 — Bộ 1</div>
                        <span class="badge badge-premium">✦ Premium</span>
                    </div>
                    <!-- Hiển thị thời gian và số câu -->
                    <div class="card-meta">
                        <span class="meta-item">
                            <i class="fa-regular fa-clock"></i>
                            120 phút
                        </span>

                        <span class="meta-item">
                            <i class="fa-regular fa-rectangle-list"></i>
                            200 câu
                        </span>
                    </div>
                    <!-- Hiển thị test description -->
                    <div class="card-desc">
                        Đề thi mô phỏng chuẩn TOEIC 2024 gồm cả Listening và Reading, kèm giải thích chi tiết.
                    </div>

                    <div class="card-tags">
                        <span class="tag">Full Test</span>
                        <span class="tag">2024</span>
                        <span class="tag">Giải thích</span>
                    </div>
                    <!-- Card footer -->
                    <div class="card-footer">
                        <div class="card-stats">
                            <div class="stat-item">
                                <i class="fa-regular fa-user"></i>
                                <span class="stat-count">6,540 lượt</span>
                            </div>
                            <!-- Đường kẻ chia ra giữa tổng số lượt làm bài và điểm trung bình -->
                            <div class="stat-divider"></div>

                            <div class="stat-item">
                                <i class="fa-regular fa-star"></i>
                                <span class="stat-score">TB 665đ</span>
                            </div>
                        </div>

                        <button class="btn-start gold">Làm bài ✦</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    </script>
</body>

</html>