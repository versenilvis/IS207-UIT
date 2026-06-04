<?php
/**
 * @var PDO $conn
 */
global $conn;
require_once __DIR__ . '/../../server/db/config.php';

$token = $_GET['token'] ?? '';
$token_hash = $token !== '' ? hash("sha256", $token) : '';
$errorMessage = '';
$isSuccess = false;

if ($token === '') {
    $errorMessage = "Liên kết xác thực tài khoản bị thiếu token.";
} else {
    $sql = "SELECT id FROM users
            WHERE account_activation_hash = :account_activation_hash";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'account_activation_hash' => $token_hash,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        $errorMessage = "Liên kết xác thực tài khoản không hợp lệ hoặc tài khoản đã được kích hoạt.";
    } else {
        $sql = "UPDATE users
                SET account_activation_hash = NULL
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'id' => $user['id'],
        ]);

        $isSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include './components/metadata.php'; ?>
    <title>PrepHub - Xác thực tài khoản</title>
    <link rel="stylesheet" href="../styles/reset.css">
</head>
<body class="reset-password-page">
    <main class="reset-page-shell">
        <section class="auth-modal-content reset-page-card" aria-labelledby="verify-title">
            <div class="auth-wrapper reset-page-wrapper">
                <div class="auth-side auth-form-side reset-page-form">
                    <div class="auth-container">
                        <a class="reset-brand" href="home.php" aria-label="Về trang chủ PrepHub">
                            <img src="../img/logo.svg" alt="">
                            <span>
                                PrepHub
                                <small>Nền tảng luyện thi TOEIC</small>
                            </span>
                        </a>

                        <?php if ($isSuccess): ?>
                            <div class="reset-success-container">
                                <div class="reset-success-icon" aria-hidden="true">
                                    <i class="fa-solid fa-check"></i>
                                </div>

                                <div class="auth-header reset-success-header">
                                    <h1 id="verify-title">Tài khoản đã được kích hoạt</h1>
                                    <p>Bạn có thể quay về trang chủ và đăng nhập để bắt đầu học trên PrepHub.</p>
                                </div>

                                <a class="btn-auth-submit reset-home-button" href="home.php">Quay về trang chủ</a>
                            </div>
                        <?php else: ?>
                            <div class="auth-header">
                                <h1 id="verify-title">Không thể kích hoạt tài khoản</h1>
                                <p>Liên kết xác thực không dùng được. Vui lòng kiểm tra lại email xác thực mới nhất của bạn.</p>
                            </div>

                            <p class="reset-alert" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="reset-page-actions">
                                <a class="btn-auth-submit" href="home.php">Quay về trang chủ</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="auth-side auth-visual-side reset-page-visual">
                    <div class="visual-overlay"></div>
                    <div class="visual-content">
                        <div class="testimonial-stack">
                            <div class="testimonial-card animate-1">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Tran+Hai+Yen" alt="">
                                <div class="testi-info">
                                    <p class="testi-name">Trần Hải Yến <i class='bx bxs-badge-check'></i></p>
                                    <p class="testi-handle">@haiyen_digital</p>
                                    <p class="testi-text">Một không gian gọn gàng, tập trung để bạn quay lại luyện TOEIC thật nhẹ nhàng.</p>
                                </div>
                            </div>
                            <div class="testimonial-card animate-2">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Nguyen+Hoang+Nam" alt="">
                                <div class="testi-info">
                                    <p class="testi-name">Nguyễn Hoàng Nam <i class='bx bxs-badge-check'></i></p>
                                    <p class="testi-handle">@nam_tech</p>
                                    <p class="testi-text">PrepHub giúp mỗi buổi học rõ ràng, có tổ chức và dễ tiếp tục.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
