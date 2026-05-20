<?php
$greetHour = (int) date('H');
if ($greetHour < 12)
    $greeting = 'Chào buổi sáng';
elseif ($greetHour < 18)
    $greeting = 'Chào buổi chiều';
else
    $greeting = 'Chào buổi tối';
?>
<section class="auth-hero">
    <div class="auth-hero-inner">

        <div class="auth-hero-left">
            <div class="auth-greet-time"><?= $greeting ?></div>
            <h1 class="auth-greet-name">
                <?= htmlspecialchars($fullName) ?>
                <?php if (!empty($_SESSION['is_premium'])): ?>
                    <span class="auth-premium-badge">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z" />
                        </svg>
                        Premium
                    </span>
                <?php endif; ?>
            </h1>
            <p class="auth-greet-sub">Tiếp tục hành trình TOEIC của bạn. Mỗi ngày luyện tập là một bước tiến.</p>
            <div class="auth-hero-actions">
                <a href="tests.php" class="auth-cta-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 3 19 12 5 21 5 3" />
                    </svg>
                    Làm bài ngay
                </a>
                <a href="billing.php" class="auth-cta-secondary">Quản lý gói</a>
            </div>
        </div>

        <div class="auth-hero-right">
            <div class="auth-stat-chips">
                <div class="auth-stat-chip">
                    <div class="auth-stat-val" id="hero-total-tests">-</div>
                    <div class="auth-stat-label">Bài đã làm</div>
                </div>
                <div class="auth-stat-chip">
                    <div class="auth-stat-val" id="hero-max-score">-</div>
                    <div class="auth-stat-label">Điểm cao nhất</div>
                </div>
                <div class="auth-stat-chip">
                    <div class="auth-stat-val" id="hero-accuracy">-</div>
                    <div class="auth-stat-label">Độ chính xác</div>
                </div>
            </div>
        </div>

    </div>

    <!-- subtle animated bg blobs -->
    <div class="auth-hero-blob blob-1"></div>
    <div class="auth-hero-blob blob-2"></div>
</section>