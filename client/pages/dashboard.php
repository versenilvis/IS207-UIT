<?php
require_once '../../server/middleware/auth.php';
require_once '../../server/controllers/profile-controller.php';
require_once '../../server/controllers/dashboard-controller.php';
//Chặn gõ thẳng lên URL
homeRedirect();

//Điểm cao nhất, điểm tb, tổng số bài đã làm và 5 đề thi gần đây nhất
$maxScore = getMaxScore();
$avgScore = getAvgScore();
$pastTests = getPastTests(5); //array được bỏ vào history body. Lấy 5 test thôi
$total_number_of_tests = getNumTestDone();
$avgTime = getAvgTime();

// Lấy tên người dùng để hiển thị lời chào
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$userName = trim($lastName . ' ' . $firstName) ?: 'bạn';

// Xác định lời chào theo giờ
$hour = (int)date('G');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'CHÀO BUỔI SÁNG ☀️';
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = 'CHÀO BUỔI CHIỀU 🌤️';
} else {
    $greeting = 'CHÀO BUỔI TỐI 🌙';
}
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
                <div class="hero-eyebrow"><?= htmlspecialchars($greeting) ?></div>
                <h1>Xin chào, <?= htmlspecialchars($userName) ?>!</h1>
                <p>Tiếp tục luyện thi để đạt mục tiêu TOEIC của bạn. Bạn đang làm rất tốt!</p>

                <div class="hero-actions">
                    <a href="tests.php" class="hero-btn primary-btn">
                        <i class="fas fa-play"></i>
                        Làm bài ngay
                    </a>

                    <a href="profile.php" class="hero-btn secondary-btn">
                        <i class="fas fa-gear"></i>
                        Cài đặt
                    </a>
                </div>
            </div>

            <!-- Hero stats (top-right) -->
            <div class="hero-right">
                <div class="hero-stat">
                    <div class="hero-stat-val"><?= $total_number_of_tests ?: '0' ?></div>
                    <div class="hero-stat-label">Bài đã làm</div>
                </div>

                <div class="hero-stat">
                    <div class="hero-stat-val"><?= $maxScore ?: '-' ?></div>
                    <div class="hero-stat-label">Điểm cao nhất</div>
                </div>

                <div class="hero-stat">
                    <div class="hero-stat-val"><?= $avgScore ?: '-' ?></div>
                    <div class="hero-stat-label">Điểm trung bình</div>
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
                    <div class="stat-value" id="max-score"><?= $maxScore ?: '0' ?></div>
                    <div class="stat-sub">Tổng điểm tốt nhất</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-circle-check"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Số bài đã làm</div>
                    <div class="stat-value" id="total-tests"><?= $total_number_of_tests ?></div>
                    <div class="stat-sub">Tổng số đề đã hoàn thành</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-chart-line"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Điểm trung bình</div>
                    <div class="stat-value" id="avg-score"><?= $avgScore ?: '0' ?></div>
                    <div class="stat-sub">Trên tất cả các bài thi</div>
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
                            <h2>Tiến độ điểm số</h2>
                            <p>Dữ liệu các lần thi gần đây.</p>
                        </div>
                    </div>
                </div>

                <!-- tab hiển thị thông tin ng dùng -->
                <div class="chart-legend-row">
                    <span class="legend-dot tong"></span><span class="legend-label">Tổng điểm</span>
                    <span class="legend-dot listening"></span><span class="legend-label">Listening</span>
                    <span class="legend-dot reading"></span><span class="legend-label">Reading</span>
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
                        <p>Làm full test mỗi 2 tuần để theo dõi tiến độ tổng thể.</p>
                    </div>
                </div>
            </aside>

        </section>

        <!-- Lịch sử -->
        <section class="dashboard-card history-card">
            <div class="card-head">
                <div class="card-title-wrap">
                    <div class="card-icon">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>

                    <div>
                        <h2>Lịch sử làm bài</h2>
                        <p>Kết quả các bài thi gần đây.</p>
                    </div>
                </div>

                <a href="attempts.php" class="view-all">
                    Xem tất cả
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <!--Ô hiển thị danh sách đề thi đã làm. Hiển thị 5 đề thi gần nhất  -->
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
                            <th>Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody id="history-body">
                        <?php foreach ($pastTests as $test): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($test['created_at']))) ?></td>
                                <td><?= htmlspecialchars($test['title']) ?></td>
                                <td class="score-listening"><?= htmlspecialchars($test['listening_score']) ?></td>
                                <td class="score-reading"><?= htmlspecialchars($test['reading_score']) ?></td>
                                <td class="score-total"><?= htmlspecialchars($test['total_score']) ?></td>
                                <td><?= htmlspecialchars($test['time_spent']) ?> phút</td>
                                <td>
                                    <a href="results.php?attempt_id=<?= urlencode($test['uuid']) ?>" class="btn-view">
                                        Xem <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
