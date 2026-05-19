<?php
session_start();
//Kiểm tra xem user có đăng nhập hay chưa
//Tránh việc lên URL gõ user.php là ra trang này
require_once '../../server/middleware/auth.php';
homeRedirect();


// Lấy username để hiển thị ở greeting box
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($lastName . ' ' . $firstName);

?>
<!doctype html>
<html lang="vi">

<head>
    <?php include './components/metadata.php'; ?>
    <title>PREHUB - Luyện Thi TOEIC</title>
    <link rel="stylesheet" href="../styles/user.css">
</head>

<body>
    <!-- INCLUDE NAVBAR FILE -->
    <?php include './components/navBar.php'; ?>
    <div class="page">

        <!-- GREETING HERO -->
        <div class="greeting-hero">
            <div class="greet-left">
                <div class="greet-time">Chào buổi sáng ☀️</div>
                <div class="greet-name">Xin chào, <?= htmlspecialchars($fullName) ?>!</div>
                <div class="greet-sub">Tiếp tục luyện thi để đạt mục tiêu<br>TOEIC của bạn. Bạn đang làm rất tốt!</div>
                <div class="greet-cta">
                    <a href="tests.php" class="cta-btn cta-primary" style="text-decoration: none;">
                        <i class="fas fa-play" style="font-size:11px;margin-right:6px"></i>Làm bài ngay
                    </a>
                </div>
            </div>
            <div class="greet-right">
                <div class="greet-stats">
                    <div class="gstat">
                        <div class="gstat-val">24</div>
                        <div class="gstat-label">Bài đã làm</div>
                    </div>
                    <div class="gstat">
                        <div class="gstat-val">580</div>
                        <div class="gstat-label">Điểm cao nhất</div>
                    </div>
                    <div class="gstat">
                        <div class="gstat-val">73%</div>
                        <div class="gstat-label">Độ chính xác</div>
                    </div>
                </div>
            </div>
        </div>

       

        <!-- Danh sách đề thi -->
        <div>
            <div class="section-head" style="margin-bottom:12px">
                <div class="section-title">Danh sách đề thi</div>
                <a class="see-all" href="tests.php">Xem tất cả →</a>
            </div>
            <!-- Ô hiển thị 3 đề thi cho người dùng thử -->
            <!-- Sau khi có logic nộp bài, trang user.php chỉ hiện thị đề thi chưa làm -->
            <div class="test-grid">

            </div>
        </div>

        <!-- Lịch sử làm bài -->
        <div class="bottom-row">
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title">Bài làm gần đây</span>
                    <a class="see-all" href="#">Xem tất cả →</a>
                </div>
                <div class="recent-list">
                    <div class="recent-item">
                        <div class="recent-icon"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <div class="recent-name">TOEIC Mock Test Jan 2024</div>
                            <div class="recent-date">23/04/2024 · 33:58</div>
                        </div>
                        <span class="score-pill s-hi">650</span>
                    </div>
                    <div class="recent-item">
                        <div class="recent-icon"><i class="fas fa-pen"></i></div>
                        <div>
                            <div class="recent-name">Grammar Sprint #8</div>
                            <div class="recent-date">29/03/2024 · 20:10</div>
                        </div>
                        <span class="score-pill s-mid">61%</span>
                    </div>
                    <div class="recent-item">
                        <div class="recent-icon"><i class="fas fa-book-open"></i></div>
                        <div>
                            <div class="recent-name">Reading Mock #3</div>
                            <div class="recent-date">28/03/2024 · 36:17</div>
                        </div>
                        <span class="score-pill s-lo">44%</span>
                    </div>
                    <div class="recent-item">
                        <div class="recent-icon"><i class="fas fa-headphones"></i></div>
                        <div>
                            <div class="recent-name">Listening Sprint #5</div>
                            <div class="recent-date">26/03/2024 · 49:24</div>
                        </div>
                        <span class="score-pill s-hi">82%</span>
                    </div>
                </div>
            </div>

            <!-- Phần mẹo luyện thi để trang trí -->
            <div class="panel">
                <div class="panel-head">
                    <span class="panel-title">Mẹo luyện thi</span>
                </div>
                <div class="tips-list">
                    <div class="tip-item">
                        <div class="tip-num">1</div>
                        <div>
                            <div class="tip-text">Luyện nghe mỗi ngày 15 phút</div>
                            <div class="tip-sub">Cải thiện Part 3 & 4 hiệu quả nhất</div>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">2</div>
                        <div>
                            <div class="tip-text">Đọc báo tiếng Anh hàng ngày</div>
                            <div class="tip-sub">Tăng tốc độ đọc cho Part 6 & 7</div>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">3</div>
                        <div>
                            <div class="tip-text">Ôn tập ngữ pháp Part 5 theo chủ đề</div>
                            <div class="tip-sub">Từ vựng + ngữ pháp = điểm cao hơn</div>
                        </div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-num">4</div>
                        <div>
                            <div class="tip-text">Làm full test mỗi 2 tuần một lần</div>
                            <div class="tip-sub">Track tiến độ và làm quen áp lực thời gian</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- INCLUDE FOOTER FILE -->
    <?php include './components/footer.php'; ?>

    <script src="../js/user.js"></script>
</body>

</html>

