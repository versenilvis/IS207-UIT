<?php
/**
 * @var PDO $conn
 */
global $conn;
require_once __DIR__ . '/../../server/db/config.php';
require_once __DIR__ . '/../../server/utils/response.php';

$token = $_GET['token'] ?? '';
$token_hash = $token !== '' ? hash("sha256", $token) : '';
$errorMessage = '';
$isTokenValid = false;
$isSuccess = ($_GET['success'] ?? '') === '1';

if ($isSuccess) {
    $isTokenValid = false;
} elseif ($token === '') {
    $errorMessage = "Liên kết đặt lại mật khẩu bị thiếu token.";
} else {
    $sql = "SELECT * FROM users
            WHERE reset_token_hash = :reset_token_hash";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        'reset_token_hash' => $token_hash,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        $errorMessage = "Liên kết đặt lại mật khẩu không hợp lệ.";
    } elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
        $errorMessage = "Liên kết đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu liên kết mới.";
    } else {
        $isTokenValid = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include './components/metadata.php'; ?>
    <title>PrepHub - Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="../styles/reset.css">
</head>
<body class="reset-password-page">
    <main class="reset-page-shell">
        <section class="auth-modal-content reset-page-card" aria-labelledby="reset-title">
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
                                    <h1 id="reset-title">Mật khẩu cập nhật thành công!</h1>
                                    <p>Bạn có thể đăng nhập lại và tiếp tục học trên PrepHub.</p>
                                </div>

                                <a class="btn-auth-submit reset-home-button" href="home.php">Quay về trang chủ</a>
                            </div>
                        <?php else: ?>
                            <div class="auth-header">
                                <h1 id="reset-title">Đặt lại mật khẩu</h1>
                                <p>Tạo mật khẩu mới cho tài khoản của bạn để tiếp tục học trên PrepHub.</p>
                            </div>

                            <?php if (!$isTokenValid): ?>
                                <p class="reset-alert" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="reset-page-actions">
                                    <a class="btn-auth-submit" href="home.php">Quay lại đăng nhập</a>
                                </div>
                            <?php else: ?>
                                <form id="resetPasswordForm" class="auth-form" method="post" action="../../server/controllers/reset-controller.php">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                                    <div class="input-group-glass">
                                        <label for="password">Mật khẩu mới</label>
                                        <div class="input-wrapper relative">
                                            <input type="password" id="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="8" required>
                                            <button type="button" class="eye-toggle" aria-label="Hiện mật khẩu">
                                                <img src="../img/eye_close.png" alt="" class="eye-icon">
                                            </button>
                                        </div>
                                    </div>

                                    <div class="input-group-glass">
                                        <label for="password_confirmation">Nhập lại mật khẩu</label>
                                        <div class="input-wrapper relative">
                                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="8" required>
                                            <button type="button" class="eye-toggle" aria-label="Hiện mật khẩu nhập lại">
                                                <img src="../img/eye_close.png" alt="" class="eye-icon">
                                            </button>
                                        </div>
                                    </div>

                                    <p class="password-hint">Mật khẩu nên có ít nhất 8 ký tự.</p>
                                    <p class="error-message reset-match-error" id="resetMatchError"></p>

                                    <button type="submit" class="btn-auth-submit">Cập nhật mật khẩu</button>
                                </form>

                                <p class="auth-switch-text">
                                    Nhớ mật khẩu rồi? <a href="home.php">Đăng nhập</a>
                                </p>
                            <?php endif; ?>
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

    <script>
        document.querySelectorAll('.eye-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.parentElement.querySelector('input');
                const icon = button.querySelector('img');
                if (!input || !icon) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.src = isPassword ? '../img/eye_open.png' : '../img/eye_close.png';
                button.setAttribute('aria-label', isPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            });
        });

        const resetForm = document.getElementById('resetPasswordForm');
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        const matchError = document.getElementById('resetMatchError');

        if (resetForm && password && confirmation && matchError) {
            resetForm.addEventListener('submit', (event) => {
                if (password.value !== confirmation.value) {
                    event.preventDefault();
                    matchError.textContent = 'Mật khẩu nhập lại không khớp.';
                    matchError.style.display = 'block';
                    confirmation.focus();
                    return;
                }

                matchError.textContent = '';
                matchError.style.display = 'none';
            });
        }
    </script>
</body>
</html>
