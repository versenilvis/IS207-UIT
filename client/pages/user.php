<?php
session_start();
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
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <!-- INCLUDE NAVBAR FILE -->
    <?php include './components/navBar.php'; ?>
    <main class="container mb-5">
        <!--Hiển thị thông tin người dùng-->
        <div class="greeting-box">
            <div class="avatar"></div>
            <div class="greeting-content">
                <h2>Xin chao, <?=  htmlspecialchars($fullName) ?>!</h2> <!--Chống XSS-->
                <p>Tiếp tục luyện thi để đạt mục tiêu <br>TOEIC của bạn</p>
            </div>
            <div class="stats">
                <div class="stat-item">
                    <h3>10</h3>
                    <p>BÀI ĐÃ <br>LÀM</p>
                </div>
                <div class="divider"></div>
                <div class="stat-item">
                    <h3>150</h3>
                    <p>ĐIỂM CAO <br>NHẤT</p>
                </div>
            </div>
        </div>
        <!--Hiển thị Danh sách đề thi-->
        <section id="book-list-section">
            <h2 class="fw-bold mb-4">Danh sách đề thi</h2>
            <div class="test-grid">
                <!--Hiển thị danh sách đề thi lấy từ database
                Hàm load test ở main.js-->
            </div>
        </section>

    </main>
    <!-- INCLUDE FOOTER FILE -->
    <?php include './components/footer.php'; ?>

    <script src="../js/main.js"></script>
</body>

</html>