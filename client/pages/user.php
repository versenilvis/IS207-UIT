<?php
require_once '../../server/middleware/auth.php';
require_once '../../server/controllers/profile-controller.php';
require_once '../../server/controllers/dashboard-controller.php';

homeRedirect();

$maxScore = getMaxScore();
$avgScore = getAvgScore();
$pastTests = getPastTests(5);
$totalTests = getNumTestDone();

$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$userName = trim($lastName . ' ' . $firstName) ?: 'Người dùng';
$heroName = function_exists('mb_strtolower') ? mb_strtolower($userName, 'UTF-8') : strtolower($userName);
$firstInitial = function (string $value): string {
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return function_exists('mb_substr') ? mb_substr($value, 0, 1, 'UTF-8') : substr($value, 0, 1);
};
$initials = strtoupper(($firstInitial($lastName) ?: 'H') . ($firstInitial($firstName) ?: 'N'));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <?php include './components/metadata.php'; ?>
    <title>Dashboard · Prephub</title>
    <link rel="stylesheet" href="../styles/user.css">
</head>

<body class="dashboard-page">
    <!-- INCLUDE NAVBAR FILE -->
    <?php include './components/navBar.php'; ?>
    <main class="page">
        <section class="dashboard-hero" aria-label="Lời chào luyện tập">
            <div class="hero-copy">
                <h1>Xin chào, <?= htmlspecialchars($heroName) ?>!</h1>
                <p class="hero-sub">Tiếp tục luyện thi để đạt mục tiêu<br>TOEIC của bạn. Bạn đang làm rất tốt!</p>

                <div class="hero-actions">
                    <a href="tests.php" class="hero-btn hero-primary">
                        <i class="fas fa-play"></i>
                        Làm bài ngay
                    </a>

                    <a href="profile.php" class="hero-btn hero-secondary">
                        <i class="fas fa-gear"></i>
                        Cài đặt
                    </a>
                </div>
            </div>

            <div class="hero-metrics" aria-label="Thống kê nhanh">
                <div class="hero-metric">
                    <strong><?= htmlspecialchars($totalTests ?: '0') ?></strong>
                    <span>Bài đã làm</span>
                </div>

                <div class="hero-metric">
                    <strong><?= htmlspecialchars($maxScore ?: '-') ?></strong>
                    <span>Điểm cao nhất</span>
                </div>

                <div class="hero-metric">
                    <strong><?= htmlspecialchars($avgScore ?: '-') ?></strong>
                    <span>Điểm trung bình</span>
                </div>
            </div>
        </section>
        <section class="stat-grid" aria-label="Tổng quan luyện tập">
            <article class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Điểm cao nhất</div>
                    <div class="stat-value" id="max-score"><?= htmlspecialchars($maxScore ?: '0') ?></div>
                    <div class="stat-sub">Tổng điểm tốt nhất</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-circle-check"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Số bài đã làm</div>
                    <div class="stat-value" id="total-tests"><?= htmlspecialchars($totalTests ?: '0') ?></div>
                    <div class="stat-sub">Tổng số đề hoàn thành</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-chart-line"></i>
                </div>

                <div class="stat-content">
                    <div class="stat-label">Điểm trung bình</div>
                    <div class="stat-value" id="avg-score"><?= htmlspecialchars($avgScore ?: '0') ?></div>
                    <div class="stat-sub">Trên tất cả các bài thi</div>
                </div>
            </article>
        </section>

        <section class="dashboard-layout">
            <article class="dashboard-card chart-card">
                <div class="card-head">
                    <div class="card-title-wrap">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>

                        <div>
                            <h2>Tiến độ điểm số</h2>
                            <p>Dữ liệu các lần thi gần đây</p>
                        </div>
                    </div>
                </div>

                <div class="chart-wrapper">
                    <canvas id="scoreChart"></canvas>
                </div>
            </article>

            <aside class="dashboard-card tip-card">
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
            </aside>
        </section>

        <section class="dashboard-card history-card">
            <div class="card-head history-head">
                <div class="card-title-wrap">
                    <div class="card-icon">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>

                    <div>
                        <h2>Lịch sử làm bài</h2>
                        <p>Kết quả các bài thi gần đây</p>
                    </div>
                </div>

                <a href="attempts.php" class="view-all">
                    Xem tất cả
                    <i class="fas fa-chevron-right"></i>
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
                            <th>Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody id="history-body">
                        <?php if (empty($pastTests)): ?>
                            <tr>
                                <td colspan="7" class="empty-row">Bạn chưa có lịch sử làm bài nào.</td>
                            </tr>
                        <?php else: ?>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <!-- INCLUDE FOOTER FILE -->
    <?php include './components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/user.js"></script>
</body>

</html>
