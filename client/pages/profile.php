<?php
// tránh việc người dùng gõ địa chỉ vào url nhưng chưa đăng nhập
require_once '../../server/middleware/auth.php';
require_once '../../server/controllers/profile-controller.php';
homeRedirect();

// hiển thị tên và email
$firstName = $_SESSION['first_name'] ?? 'Người';
$lastName = $_SESSION['last_name'] ?? 'dùng';
$email = $_SESSION['email'] ?? 'user@email.com';

$fullName = trim($lastName . ' ' . $firstName);
$getInitial = function ($value) {
    return preg_match('/./u', trim($value), $match) ? $match[0] : '';
};
$initials = strtoupper($getInitial($firstName) . $getInitial($lastName));

// kiểm tra trạng thái thành viên premium
$isPremium = $_SESSION['is_premium'] ?? false;
$premiumName = $_SESSION['premium_name'] ?? 'Premium';

// thông báo đổi tên thành công
$changeNameResult = $_SESSION['changeNameResult'] ?? null;
unset($_SESSION['changeNameResult']);

// thông báo đổi mật khẩu thành công
$changePassResult = $_SESSION['changePassResult'] ?? null;
$changePassType = $_SESSION['changePassType'] ?? 'success';
$isChangePassError = $changePassType === 'error' || $changePassResult === "Hãy kiểm tra xem bạn đã nhập đúng mật khẩu hay chưa.";
unset($_SESSION['changePassResult']);
unset($_SESSION['changePassType']);

// thông báo kiểm tra mật khẩu để xóa tài khoản
$deletePasswordResult = $_SESSION['password_confirmation_result'] ?? null;
unset($_SESSION['password_confirmation_result']);

// lấy điểm cao nhất, điểm trung bình và số đề đã làm
$maxScore = getMaxScore();
$avgScore = getAvgScore();
$total_number_of_tests = getNumTestDone();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Prephub</title>
    <?php include './components/metadata.php'; ?>
    <link rel="stylesheet" href="../styles/profile.css">
</head>

<body>
	<?php $navbarMode = 'light'; include './components/navbar.php'; ?>

    <div class="page">
        <!-- thông báo flash từ hệ thống -->
        <?php if ($changeNameResult): ?>
            <div class="success-message">
                <?= htmlspecialchars($changeNameResult) ?>
            </div>
        <?php endif; ?>
        <?php if ($changePassResult): ?>
            <div class="success-message <?= $isChangePassError ? 'error-message' : '' ?>">
                <?= htmlspecialchars($changePassResult) ?>
            </div>
        <?php endif; ?>
        <?php if ($deletePasswordResult): ?>
            <div class="success-message error-message">
                <?= htmlspecialchars($deletePasswordResult) ?>
            </div>
        <?php endif; ?>

        <!-- phần tiêu đề hero trang hồ sơ -->
        <div class="profile-hero">
            <div class="hero-left">
                <div class="hero-eyebrow">Tài khoản cá nhân</div>
                <h1>Hồ sơ của tôi</h1>
                <p>Quản lý thông tin tài khoản cá nhân, cài đặt bảo mật và cập nhật trạng thái thành viên của bạn</p>

                <div class="hero-actions">
                    <a href="tests.php" class="hero-btn primary-btn">
                        <i class="fas fa-play"></i>
                        Làm bài ngay
                    </a>
                    <?php if ($isPremium): ?>
                        <a href="billing.php" class="hero-btn secondary-btn">
                            <i class="fas fa-receipt"></i>
                            Quản lý hóa đơn
                        </a>
                    <?php else: ?>
                        <a href="pricing.php" class="hero-btn secondary-btn">
                            <i class="fas fa-crown"></i>
                            Nâng cấp Premium
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hero-right">
                <div class="hero-stat">
                    <div class="hero-stat-val"><?= $total_number_of_tests ?></div>
                    <div class="hero-stat-label">Bài đã làm</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-val"><?= $maxScore ?></div>
                    <div class="hero-stat-label">Điểm cao nhất</div>
                </div>
            </div>
        </div>

        <!-- bố cục hai cột hồ sơ -->
        <div class="profile-layout">
            <!-- cột trái hiển thị thông tin tóm tắt -->
            <aside class="left-col">
                <div class="profile-card user-card">
                    <div class="avatar-wrap">
                        <div class="avatar-circle">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                        <button class="avatar-edit" type="button" aria-label="Chỉnh sửa ảnh đại diện">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>

                    <div class="user-name"><?= htmlspecialchars($fullName) ?></div>
                    <div class="user-email"><?= htmlspecialchars($email) ?></div>

                    <?php if ($isPremium): ?>
                        <div class="plan-pill" style="background:#e1f5ee; border-color:#d3f9d8; color:#1d9e75;">
                            <i class="fas fa-crown"></i>
                            <?= htmlspecialchars($premiumName) ?>
                        </div>
                    <?php else: ?>
                        <div class="plan-pill">
                            <i class="fas fa-bolt"></i>
                            Tài khoản miễn phí
                        </div>
                    <?php endif; ?>

                    <div class="user-divider"></div>

                    <div class="quick-stats">
                        <div class="quick-stat">
                            <div class="quick-icon blue">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <div class="quick-val"><?= $total_number_of_tests ?></div>
                                <div class="quick-label">Bài đã làm</div>
                            </div>
                        </div>

                        <div class="quick-stat">
                            <div class="quick-icon green">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <div class="quick-val"><?= $avgScore ?></div>
                                <div class="quick-label">Điểm trung bình</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- cột phải hiển thị các form cài đặt chính -->
            <section class="right-col">
                <!-- form chỉnh sửa thông tin cá nhân -->
                <form class="profile-card section-card" method="POST" action="../../server/controllers/profile-controller.php">
                    <div class="section-head">
                        <div class="section-title-wrap">
                            <div class="section-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h2>Thông tin cá nhân</h2>
                                <p>Cập nhật thông tin cơ bản được hiển thị trên tài khoản của bạn</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="i-lname">Họ</label>
                            <input id="i-lname" name="last_name" type="text" value="<?= htmlspecialchars($lastName) ?>">
                        </div>

                        <div class="form-group">
                            <label for="i-fname">Tên</label>
                            <input id="i-fname" name="first_name" type="text" value="<?= htmlspecialchars($firstName) ?>">
                        </div>

                        <div class="form-group full">
                            <label for="i-email">Email đăng ký</label>
                            <input id="i-email" type="email" value="<?= htmlspecialchars($email) ?>" readonly>
                        </div>
                    </div>
                    <input type="hidden" name="changeName" value="changeUsername"> 
                    <div class="card-actions">
                        <button class="save-btn" type="submit">
                            <i class="fas fa-floppy-disk"></i>
                            Cập nhật thông tin
                        </button>
                    </div>
                </form>

                <!-- form đổi mật khẩu bảo mật -->
                <form class="profile-card section-card" method="POST" action="../../server/controllers/profile-controller.php">
                    <div class="section-head">
                        <div class="section-title-wrap">
                            <div class="section-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div>
                                <h2>Cài đặt bảo mật</h2>
                                <p>Đổi mật khẩu định kỳ để nâng cao tính an toàn cho tài khoản</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="current-password">Mật khẩu hiện tại</label>
                            <div class="password-box">
                                <input id="current-password" name="current_password" type="password" placeholder="Nhập mật khẩu hiện tại">
                                <button type="button" class="eye-toggle" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                                    <img src="../img/eye_close.png" alt="" class="eye-icon">
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new-password">Mật khẩu mới</label>
                            <div class="password-box">
                                <input id="new-password" name="new_password" type="password" placeholder="Nhập mật khẩu mới">
                                <button type="button" class="eye-toggle" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                                    <img src="../img/eye_close.png" alt="" class="eye-icon">
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Xác nhận mật khẩu</label>
                            <div class="password-box">
                                <input id="confirm-password" name="confirm_password" type="password" placeholder="Xác nhận lại mật khẩu">
                                <button type="button" class="eye-toggle" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                                    <img src="../img/eye_close.png" alt="" class="eye-icon">
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="changePassword" value="changePassword">
                    <div class="card-actions">
                        <button class="save-btn" type="submit">
                            <i class="fas fa-shield-halved"></i>
                            Cập nhật mật khẩu
                        </button>
                    </div>
                </form>

                <!-- khối tài khoản liên kết mạng xã hội -->
                <div class="profile-card connected-card">
                    <div class="connected-left">
                        <div class="connected-icon google-icon">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="connected-text">
                            <h2>Tài khoản liên kết</h2>
                            <p>Đăng nhập nhanh chóng và bảo mật thông qua tài khoản Google</p>
                        </div>
                    </div>
                    <a href="#" class="connect-google-btn">
                        Liên kết ngay
                    </a>
                </div>

                <!-- khối khu vực nguy hiểm quản lý tài khoản -->
                <div class="profile-card danger-card">
                    <div class="danger-left">
                        <div class="danger-icon">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h2>Xóa tài khoản vĩnh viễn</h2>
                            <p>Hành động này sẽ xóa toàn bộ dữ liệu luyện thi và kết quả làm bài của bạn</p>
                        </div>
                    </div>
                    <button class="danger-btn" id="open-delete-popup" type="button">
                        <i class="fas fa-trash-can"></i>
                        Xóa tài khoản
                    </button>
                </div>
            </section>
        </div>
    </div>

    <!-- modal popup xác nhận xóa tài khoản -->
    <div class="delete-popup-overlay" id="delete-popup">
        <div class="delete-popup" role="dialog" aria-modal="true" aria-labelledby="delete-popup-title">
            <button class="delete-popup-close" id="close-delete-popup" type="button" aria-label="Đóng">
                <i class="fas fa-xmark"></i>
            </button>

            <div class="delete-popup-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h2 id="delete-popup-title">Bạn chắc chắn muốn xóa?</h2>
            <p>Hành động này không thể hoàn tác. Toàn bộ lịch sử làm bài và điểm số sẽ biến mất vĩnh viễn</p>

            <form method="POST" action="../../server/controllers/profile-controller.php">
                <div class="delete-password-field">
                    <label for="delete-account-password">Nhập mật khẩu của bạn để xác nhận</label>
                    <div class="password-box">
                        <input id="delete-account-password" name="password_confirmation_delete" type="password" placeholder="Mật khẩu tài khoản">
                        <button type="button" class="eye-toggle" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                            <img src="../img/eye_close.png" alt="" class="eye-icon">
                        </button>
                    </div>
                </div>

                <div class="delete-popup-actions">
                    <button class="delete-cancel-btn" id="cancel-delete-popup" type="button">Hủy bỏ</button>
                    <input type="hidden" name="deleteAccount" value="deleteAccount">
                    <button class="delete-confirm-btn" type="submit">Xác nhận xóa</button>
                </div>
            </form>
        </div>
    </div>

    <?php include './components/footer.php'; ?>

    <script src="../js/profile.js"></script>
</body>

</html>
