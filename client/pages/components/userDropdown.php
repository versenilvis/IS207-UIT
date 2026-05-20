<?php
$userDropdownData = [
    'name' => trim(($_SESSION['first_name'] ?? 'User') . ' ' . ($_SESSION['last_name'] ?? '')),
    'email' => $_SESSION['email'] ?? 'user@example.com',
    'avatarUrl' => 'https://ui-avatars.com/api/?name=' . urlencode(trim(($_SESSION['first_name'] ?? 'User') . ' ' . ($_SESSION['last_name'] ?? ''))) . '&background=05102b&color=fff',
];

$menuItems = [
    ['label' => 'Hồ sơ',    'icon' => 'bx-user-circle', 'href' => 'user.php'],
    ['label' => 'Hoá đơn',   'icon' => 'bx-receipt',     'href' => 'billing.php'],
    ['label' => 'Thông báo', 'icon' => 'bx-bell',        'href' => '#'],
    ['label' => 'Đề thi',    'icon' => 'bx-edit',        'href' => 'tests.php'],
    ['label' => 'Nâng cấp',  'icon' => 'bxf bx-star',   'href' => 'pricing.php', 'badge' => '-20%', 'highlight' => true],
];

$bottomItems = [
    ['label' => 'Cài đặt',  'icon' => 'bx-cog',         'href' => 'profile.php'],
    ['label' => 'Phím tắt', 'icon' => 'bx-command',     'href' => '?panel=shortcuts'],
    ['label' => 'Có gì mới', 'icon' => 'bx-mail-open',  'href' => '#', 'external' => true],
    ['label' => 'Hỗ trợ',    'icon' => 'bx-help-circle', 'href' => 'tos.php?tab=lien-he', 'external' => true],
];

$shortcuts = [
    ['keys' => ['Ctrl', 'K'], 'desc' => 'Tìm kiếm nhanh'],
    ['keys' => ['G', 'D'], 'desc' => 'Bảng điều khiển'],
    ['keys' => ['Ctrl', '/'], 'desc' => 'Xem phím tắt'],
    ['keys' => ['Esc'], 'desc' => 'Đóng panel'],
];
?>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="dropdown">
    <a href="#" class="d-flex align-items-center text-decoration-none" id="userDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="color: var(--primary); font-weight: 600;">
        <div class="avatar-wrapper-custom">
            <img src="<?= htmlspecialchars($userDropdownData['avatarUrl']) ?>" alt="Avatar" class="avatar-img-custom">
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg mt-2" aria-labelledby="userDropdown" style="border-radius: 16px;">
        <div class="dropdown-panel-wrap">
            <!-- Main Panel -->
            <div id="mainPanelCustom">
                <!-- header: user info -->
                <div class="panel-header d-flex align-items-center gap-3">
                    <div class="avatar-wrapper-custom">
                        <img src="<?= htmlspecialchars($userDropdownData['avatarUrl']) ?>" alt="avatar" class="avatar-img-custom">
                    </div>
                    <div class="d-flex flex-column">
                        <span class="user-name-text"><?= htmlspecialchars($userDropdownData['name']) ?></span>
                        <span class="user-email-text"><?= htmlspecialchars($userDropdownData['email']) ?></span>
                    </div>
                </div>

                <!-- upgrade banner -->
                <div class="px-3 py-3">
                    <?php if (isset($_SESSION['is_premium']) && $_SESSION['is_premium']): ?>
                    <?php $courseOnly = in_array($_SESSION['premium_plan'] ?? '', ['course']); ?>
                    <a href="<?= $courseOnly ? 'courses.php' : 'billing.php' ?>"
                       class="upgrade-banner-custom d-flex align-items-center justify-content-center gap-2 text-decoration-none"
                       style="background: linear-gradient(135deg,#e1f5ee,#d1fae5);">
                        <i class='bxf bx-crown text-dark fs-6'></i>
                        <span class="fw-bold text-dark" style="font-size:.8rem; white-space:nowrap;">
                            <?= $courseOnly ? 'Khoá học đã mua' : 'Quản lý gói' ?>
                        </span>
                        <div class="shine-overlay-custom"></div>
                    </a>
                    <?php else: ?>
                    <a href="pricing.php" class="upgrade-banner-custom d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                        <i class='bxf bx-crown text-dark fs-6'></i>
                        <span class="fw-bold text-dark small" style="white-space:nowrap;">Nâng cấp tài khoản</span>
                        <div class="shine-overlay-custom"></div>
                    </a>
                    <?php endif; ?>
                </div>


                <!-- main menu -->
                <div class="px-2 py-1">
                    <?php foreach ($menuItems as $item): ?>
                        <a href="<?= htmlspecialchars($item['href']) ?>" class="menu-item-custom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="menu-icon-custom <?= !empty($item['highlight']) ? 'menu-icon-star-custom' : '' ?>">
                                    <i class='<?= $item['icon'] ?> fs-5'></i>
                                </div>
                                <span class="menu-label-custom"><?= htmlspecialchars($item['label']) ?></span>
                            </div>
                            <?php if (!empty($item['badge'])): ?>
                                <span class="pro-badge-custom"><?= htmlspecialchars($item['badge']) ?></span>
                            <?php endif; ?>
                        </a>
                        <?php if (!empty($item['highlight'])): ?>
                            <div class="divider-h"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- bottom menu -->
                <div class="px-2 py-2">
                    <?php foreach ($bottomItems as $item): ?>
                        <?php if ($item['label'] === 'Phím tắt'): ?>
                            <a href="javascript:void(0)" onclick="togglePanelCustom('shortcutsPanelCustom')" class="menu-item-custom d-flex align-items-center justify-content-between">
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($item['href']) ?>" class="menu-item-custom d-flex align-items-center justify-content-between">
                        <?php endif; ?>
                            <div class="d-flex align-items-center gap-3">
                                <div class="menu-icon-custom">
                                    <i class='bx <?= $item['icon'] ?> fs-5'></i>
                                </div>
                                <span class="menu-label-custom"><?= htmlspecialchars($item['label']) ?></span>
                            </div>
                            <?php if (!empty($item['external'])): ?>
                                <div class="menu-icon-custom opacity-50">
                                    <i class='bx bx-arrow-out-up-right-square fs-6'></i>
                                </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>

                    <!-- Sign out button -->
                    <a href="../../server/controllers/log-out.php" class="signout-btn-custom d-flex align-items-center gap-3 w-100 mt-1">
                        <div class="menu-icon-custom signout-icon-custom">
                            <i class='bx bx-arrow-out-right-circle-half fs-5'></i>
                        </div>
                        <span class="signout-label-custom">Đăng xuất</span>
                    </a>
                </div>
            </div>

            <!-- Shortcuts Panel -->
            <div id="shortcutsPanelCustom" class="d-none">
                <!-- header: back + title -->
                <div class="panel-header d-flex align-items-center gap-1">
                    <a href="javascript:void(0)" onclick="togglePanelCustom('mainPanelCustom')" class="back-btn-custom">
                        <i class='bx bx-arrow-left-stroke fs-5'></i>
                    </a>
                    <span class="user-name-text">Phím tắt</span>
                </div>

                <div class="shortcuts-body-custom px-4 py-3">
                    <?php foreach ($shortcuts as $sc): ?>
                        <div class="shortcut-row-custom d-flex align-items-center justify-content-between">
                            <span class="shortcut-desc-custom"><?= htmlspecialchars($sc['desc']) ?></span>
                            <div class="d-flex align-items-center gap-1">
                                <?php foreach ($sc['keys'] as $key): ?>
                                    <kbd class="shortcut-key-custom"><?= htmlspecialchars($key) ?></kbd>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
            function togglePanelCustom(panelId) {
                document.getElementById('mainPanelCustom').classList.toggle('d-none', panelId === 'shortcutsPanelCustom');
                document.getElementById('shortcutsPanelCustom').classList.toggle('d-none', panelId === 'mainPanelCustom');
            }
        </script>
    </div>
</div>
<?php else: ?>
<a href="javascript:void(0)" class="btn-login" id="loginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập</a>
<?php endif; ?>
