<?php
require_once '../../server/middleware/auth.php';
require_once '../../server/controllers/profile-controller.php';
require_once '../../server/controllers/dashboard-controller.php';
//Chặn gõ thẳng lên URL
homeRedirect();

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <?php include './components/metadata.php'; ?>
    <title>TOEIC Dashboard</title>

    <link rel="stylesheet" href="../styles/dashboard.css">
</head>

<body>
    <!-- GIỮ NAVBAR -->
    <?php include './components/navBar.php'; ?>

    <div class="page">

        <!-- DASHBOARD HERO -->
        <section class="dashboard-hero">
            <div class="hero-left">
                <div class="hero-eyebrow">Dashboard cá nhân</div>
                <h1>Kết quả luyện tập TOEIC</h1>
                <p>Theo dõi điểm số, số bài đã làm và tiến độ luyện tập của bạn theo từng lần thi.</p>

                <div class="hero-actions">
                    <a href="tests.php" class="hero-btn primary-btn">
                        <i class="fas fa-play"></i>
                        Làm bài mới
                    </a>

                    <a href="attempts.php" class="hero-btn secondary-btn">
                        <i class="fas fa-clock-rotate-left"></i>
                        Xem lịch sử
                    </a>
                </div>
            </div>

            
        </section>

        <!-- STAT CARDS -->
        <section class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Điểm cao nhất</div>
                    <div class="stat-value" id="max-score">0</div>
                    <div class="stat-sub">Tổng điểm tốt nhất bạn đạt được</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-circle-check"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Số bài đã làm</div>
                    <div class="stat-value" id="total-tests">0</div>
                    <div class="stat-sub">Tổng số đề đã hoàn thành</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-stopwatch"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Thời gian trung bình</div>
                    <div class="stat-value" id="avg-time">0m</div>
                    <div class="stat-sub">Thời gian làm bài trung bình</div>
                </div>
            </div>
        </section>

        <!-- MAIN DASHBOARD -->
        <section class="dashboard-layout">

            <!-- CHART -->
            <div class="dashboard-card chart-card">
                <div class="card-head">
                    <div class="card-title-wrap">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>

                        <div>
                            <h2>Biểu đồ tiến độ điểm số</h2>
                            <p>Dữ liệu được tính theo từng lần thi gần đây.</p>
                        </div>
                    </div>

                    <span class="soft-badge">
                        <i class="fas fa-arrow-trend-up"></i>
                        Score progress
                    </span>
                </div>

                <div class="chart-wrapper">
                    <canvas id="scoreChart"></canvas>
                </div>
            </div>

            <!-- SIDE PANEL -->
            <aside class="dashboard-side">

                <div class="dashboard-card tip-card">
                    <div class="tip-head">
                        <i class="fas fa-lightbulb"></i>
                        Gợi ý luyện tập
                    </div>

                    <div class="tip-item">
                        <span>01</span>
                        <p>Làm lại các đề có điểm Reading thấp để cải thiện tốc độ đọc.</p>
                    </div>

                    <div class="tip-item">
                        <span>02</span>
                        <p>Ôn lại Part 3 và Part 4 nếu điểm Listening chưa ổn định.</p>
                    </div>

                    <div class="tip-item">
                        <span>03</span>
                        <p>Đặt mục tiêu tăng 20–30 điểm sau mỗi 3 bài luyện tập.</p>
                    </div>
                </div>
            </aside>

        </section>

        <!-- HISTORY TABLE -->
        <section class="dashboard-card history-card">
            <div class="card-head">
                <div class="card-title-wrap">
                    <div class="card-icon">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>

                    <div>
                        <h2>Lịch sử làm bài gần đây</h2>
                        <p>Xem lại kết quả các bài thi bạn đã hoàn thành.</p>
                    </div>
                </div>

                <a href="attempts.php" class="view-all">
                    Xem tất cả
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Ngày thi</th>
                            <th>Đề thi</th>
                            <th>Listening</th>
                            <th>Reading</th>
                            <th>Tổng điểm</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>

                    <tbody id="history-body">
                    </tbody>
                </table>
            </div>
        </section>

    </div>
    <!-- INCLUDE FOOTER FILE -->
    <?php include './components/footer.php'; ?>
    <script src="../js/dashboard.js"></script>
</body>

</html>