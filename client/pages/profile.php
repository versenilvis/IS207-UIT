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

// ảnh đại diện đồng bộ
$avatarUrl = (!empty($_SESSION['avatar'])) ? $_SESSION['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=05102b&color=fff';

// kiểm tra trạng thái thành viên premium
$isPremium = $_SESSION['is_premium'] ?? false;
$premiumName = $_SESSION['premium_name'] ?? 'Premium';

// thông báo đổi tên thành công
$changeNameResult = $_SESSION['changeNameResult'] ?? null;
unset($_SESSION['changeNameResult']);

// thông báo đổi ảnh đại diện thành công
$changeAvatarResult = $_SESSION['changeAvatarResult'] ?? null;
unset($_SESSION['changeAvatarResult']);

// thông báo đổi mật khẩu thành công
$changePassResult = $_SESSION['changePassResult'] ?? null;
$changePassType = $_SESSION['changePassType'] ?? 'success';
$isChangePassError = $changePassType === 'error' || $changePassResult === "Hãy kiểm tra xem bạn đã nhập đúng mật khẩu hay chưa.";
unset($_SESSION['changePassResult']);
unset($_SESSION['changePassType']);

// thông báo kiểm tra mật khẩu để xóa tài khoản
$deletePasswordResult = $_SESSION['password_confirmation_result'] ?? null;
unset($_SESSION['password_confirmation_result']);

// kiểm tra liên kết tài khoản google
$userId = $_SESSION['user_id'] ?? null;
$isGoogleLinked = false;
if ($userId) {
    try {
        $stmt = $conn->prepare('SELECT 1 FROM oauth_accounts WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $isGoogleLinked = (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Prephub</title>
    <?php include './components/metadata.php'; ?>
    <link rel="stylesheet" href="../styles/profile.css?v=9">
</head>

<body class="profile-page-body">
	<?php $navbarMode = 'light'; include './components/navbar.php'; ?>

    <main class="profile-layout-container">

        <!-- tiêu đề trang tối giản -->
        <header class="profile-page-header">
            <h1 class="profile-title">Thiết lập tài khoản</h1>
            <p class="profile-subtitle">Cập nhật thông tin cá nhân và quản lý các thiết lập bảo mật</p>
        </header>

        <!-- bảng điều khiển hồ sơ -->
        <div class="profile-card-panel">
            <!-- cột trái: tóm tắt thông tin -->
            <aside class="profile-sidebar">
                <div class="profile-overview-card">
                    <div class="profile-avatar-container" title="Nhấp để thay đổi ảnh đại diện">
                        <img class="profile-avatar" src="<?= htmlspecialchars($avatarUrl) ?>" alt="avatar">
                        <div class="avatar-hover-overlay">
                            <i class="fas fa-camera"></i>
                            <span>Thay đổi</span>
                        </div>
                    </div>

                    <!-- form tải ảnh đại diện -->
                    <form id="avatar-upload-form" method="POST" action="../../server/controllers/profile-controller.php" enctype="multipart/form-data" style="display: none;">
                        <input type="file" id="avatar-file-input" name="avatar_file" accept="image/*">
                        <input type="hidden" name="changeAvatar" value="1">
                    </form>

                    <h2 class="user-fullname"><?= htmlspecialchars($fullName) ?></h2>
                    <p class="user-email-text"><?= htmlspecialchars($email) ?></p>

                    <?php if ($changeAvatarResult): ?>
                        <?php if (strpos($changeAvatarResult, 'thành công') !== false): ?>
                            <div class="form-success-inline" style="margin-top: 0; margin-bottom: 12px; font-size: 0.72rem; padding: 6px 10px; text-align: center;"><?= htmlspecialchars($changeAvatarResult) ?></div>
                        <?php else: ?>
                            <div class="form-error-inline" style="margin-top: 0; margin-bottom: 12px; font-size: 0.72rem; padding: 6px 10px; text-align: center;"><?= htmlspecialchars($changeAvatarResult) ?></div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="user-badge-wrap">
                        <?php if ($isPremium): ?>
                            <span class="premium-badge-pill">
                                <i class="fas fa-crown"></i>
                                <?= htmlspecialchars($premiumName) ?>
                            </span>
                        <?php else: ?>
                            <span class="free-badge-pill">
                                <i class="fas fa-bolt"></i>
                                Tài khoản miễn phí
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>

            <!-- cột phải: biểu mẫu thiết lập và phân khu xóa tài khoản -->
            <section class="profile-main-content">
                <!-- lưới chứa hai biểu mẫu chính side-by-side -->
                <div class="settings-forms-grid">
                    <!-- phần: thông tin cá nhân -->
                    <div class="settings-group">
                        <h3 class="settings-group-title">Thông tin cá nhân</h3>
                        <p class="settings-group-desc">Cập nhật họ tên hiển thị của bạn</p>
                        <?php if ($changeNameResult): ?>
                            <?php if (strpos($changeNameResult, 'thành công') !== false): ?>
                                <div class="form-success-inline"><?= htmlspecialchars($changeNameResult) ?></div>
                            <?php else: ?>
                                <div class="form-error-inline"><?= htmlspecialchars($changeNameResult) ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <form class="settings-form" method="POST" action="../../server/controllers/profile-controller.php">
                            <div class="settings-form-row">
                                <div class="settings-form-group">
                                    <label for="i-lname">Họ</label>
                                    <input id="i-lname" name="last_name" type="text" value="<?= htmlspecialchars($lastName) ?>" required>
                                </div>

                                <div class="settings-form-group">
                                    <label for="i-fname">Tên</label>
                                    <input id="i-fname" name="first_name" type="text" value="<?= htmlspecialchars($firstName) ?>" required>
                                </div>
                            </div>

                            <div class="settings-form-group full-width">
                                <label for="i-email">Địa chỉ email</label>
                                <input id="i-email" type="email" value="<?= htmlspecialchars($email) ?>" readonly>
                                <span class="input-helper-text">Địa chỉ email dùng để đăng nhập</span>
                            </div>

                            <input type="hidden" name="changeName" value="changeUsername">
                            <div class="settings-form-actions">
                                <button class="minimal-btn-primary" type="submit">
                                    Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- phần: cài đặt mật khẩu -->
                    <div class="settings-group">
                        <h3 class="settings-group-title">Cài đặt mật khẩu</h3>
                        <p class="settings-group-desc">Đổi mật khẩu định kỳ để bảo vệ tài khoản</p>
                        <?php if ($changePassResult): ?>
                            <?php if ($isChangePassError): ?>
                                <div class="form-error-inline"><?= htmlspecialchars($changePassResult) ?></div>
                            <?php else: ?>
                                <div class="form-success-inline"><?= htmlspecialchars($changePassResult) ?></div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form id="passwordForm" class="settings-form" method="POST" action="../../server/controllers/profile-controller.php" novalidate>
                            <div class="settings-form-group full-width">
                                <label for="current-password">Mật khẩu hiện tại</label>
                                <div class="password-input-wrapper">
                                    <input id="current-password" name="current_password" type="password" placeholder="Nhập mật khẩu hiện tại" required>
                                    <button type="button" class="password-toggle-trigger" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                                        <img src="../img/eye_close.png" alt="" class="eye-icon-img">
                                    </button>
                                </div>
                            </div>

                            <div class="settings-form-group full-width">
                                <label for="new-password">Mật khẩu mới</label>
                                <div class="password-input-wrapper">
                                    <input id="new-password" name="new_password" type="password" placeholder="Mật khẩu mới" minlength="8" required>
                                    <button type="button" class="password-toggle-trigger" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                                        <img src="../img/eye_close.png" alt="" class="eye-icon-img">
                                    </button>
                                </div>
                            </div>

                            <div class="settings-form-group full-width">
                                <label for="confirm-password">Xác nhận mật khẩu</label>
                                <div class="password-input-wrapper">
                                    <input id="confirm-password" name="confirm_password" type="password" placeholder="Xác nhận mật khẩu" minlength="8" required>
                                    <button type="button" class="password-toggle-trigger" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                                        <img src="../img/eye_close.png" alt="" class="eye-icon-img">
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="changePassword" value="changePassword">
                            <div class="settings-form-actions">
                                <button class="minimal-btn-primary" type="submit">
                                    Cập nhật mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="settings-divider">

                <!-- liên kết tài khoản google -->
                <div class="settings-group google-connect-group">
                    <div class="google-connect-header">
                        <div class="google-connect-info">
                            <h3 class="google-connect-title">Liên kết tài khoản Google</h3>
                            <p class="google-connect-desc">Liên kết tài khoản Google giúp bạn đăng nhập nhanh chóng chỉ bằng một cú nhấp chuột</p>
                        </div>
                        <?php if ($isGoogleLinked): ?>
                            <div class="google-connected-status">
                                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
                                <span>Đã liên kết</span>
                            </div>
                        <?php else: ?>
                            <a class="minimal-btn-google" href="/api/auth/google">
                                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
                                <span>Kết nối Google</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="settings-divider">

                <!-- phân khu nguy hiểm: xóa tài khoản -->
                <div class="settings-group danger-zone-group">
                    <div class="danger-zone-header">
                        <div class="danger-zone-info">
                            <h3 class="danger-zone-title">Xóa tài khoản</h3>
                            <p class="danger-zone-desc">Xóa vĩnh viễn tài khoản của bạn và toàn bộ kết quả luyện thi liên quan, hành động này không thể khôi phục</p>
                        </div>
                        <button class="minimal-btn-danger" id="open-delete-popup" type="button">
                            Xóa tài khoản
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- modal xác nhận xóa tài khoản -->
    <div class="delete-popup-overlay" id="delete-popup">
        <div class="delete-popup-card" role="dialog" aria-modal="true" aria-labelledby="delete-popup-title">
            <button class="delete-popup-close-btn" id="close-delete-popup" type="button" aria-label="Đóng">
                <i class="fas fa-xmark"></i>
            </button>

            <h2 id="delete-popup-title" class="delete-title">Xác nhận xóa tài khoản</h2>
            <p class="delete-desc">Hành động này sẽ xóa vĩnh viễn toàn bộ lịch sử làm bài, kết quả chấm thi và quyền Premium của bạn trên hệ thống</p>
            <?php if ($deletePasswordResult): ?>
                <div class="form-error-inline" style="text-align: center;"><?= htmlspecialchars($deletePasswordResult) ?></div>
            <?php endif; ?>

            <form method="POST" action="../../server/controllers/profile-controller.php">
                <div class="delete-form-group">
                    <label for="delete-account-password">Nhập mật khẩu xác nhận</label>
                    <div class="password-input-wrapper">
                        <input id="delete-account-password" name="password_confirmation_delete" type="password" placeholder="Nhập mật khẩu của bạn" required>
                        <button type="button" class="password-toggle-trigger" aria-label="Hiển thị mật khẩu" onclick="togglePassword(this)">
                            <img src="../img/eye_close.png" alt="" class="eye-icon-img">
                        </button>
                    </div>
                </div>

                <div class="delete-popup-button-group">
                    <button class="minimal-btn-secondary" id="cancel-delete-popup" type="button">Hủy bỏ</button>
                    <input type="hidden" name="deleteAccount" value="deleteAccount">
                    <button class="delete-popup-confirm-btn" type="submit">Xác nhận xóa</button>
                </div>
            </form>
        </div>
    </div>

    <?php include './components/footer.php'; ?>

    <script src="../js/profile.js?v=6"></script>
</body>

</html>
