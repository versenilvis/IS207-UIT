<?php
// Khai báo tất cả biến ở trên đây cho dễ sửa 
$plans = require_once '../../server/config/premiumPlan.php'; //Hiển thị các plan options
//Plan miễn phí
$freePlan = $plans['free']['name'];
$freePrice = $plans['free']['price'];
$freePeriod = $plans['free']['period'];

//Plan Pro
$proPlan = $plans['pro']['name'];
$proPrice = $plans['pro']['price'];
$proPeriod = $plans['pro']['period'];

//Plan Ultra
$ultraPlan = $plans['ultra']['name'];
$ultraPrice = $plans['ultra']['price'];
$ultraPeriod = $plans['ultra']['period'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Plans</title>
    <?php include './components/metadata.php';?> 
    <link rel="stylesheet" href="../styles/premium.css">
</head>

<body>
    <a href="user.php" class="back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 5l-7 7 7 7" />
        </svg>
    </a>
    <div class="pricing-hero">
        <h1>Nâng cấp gói đăng ký</h1>
        <p>Mở khóa toàn bộ đề thi và tính năng nâng cao</p>
    </div>
    <section class="pricing-section">
        <!-- Gói miễn phi -->
        <div class="pricing-container">
            <div class="pricing-card">
                <div class="card-header-row">
                    <h2 class="plan-name"><?= $freePlan ?></h2>
                    <span class="popular-tag" style="visibility: hidden;">Placeholder</span>
                </div>
                <div class="plan-price">
                    <span class="price-amount"><?= $freePrice?>₫</span>
                    <span class="price-period">/ <?= $freePeriod ?></span>
                </div>
                <hr class="divider">
                <ul class="feature-list">
                    <li class="feature-item yes">5 đề thi cơ bản</li>
                    <li class="feature-item yes">Kết quả sau khi làm bài</li>
                    <li class="feature-item yes">Lịch sử làm bài</li>
                    <li class="feature-item no">Đề thi nâng cao</li>
                    <li class="feature-item no">Phân tích điểm chi tiết</li>
                    <li class="feature-item no">Lộ trình học cá nhân</li>
                    <li class="feature-item no">Hỗ trợ ưu tiên 24/7</li>
                    <li class="feature-item no">Tiết kiệm 16.7%</li>
                </ul>
                <a class="plan-btn btn-free">Gói hiện tại</a>
            </div>
            <!-- Gói pro -->
            <div class="pricing-card card-pro">
                <div class="card-header-row">
                    <h2 class="plan-name"><?= $proPlan ?></h2>
                    <span class="popular-tag">Phổ biến nhất</span>
                </div>
                <div class="plan-price">
                    <span class="price-amount"><?= $proPrice ?>₫</span>
                    <span class="price-period">/ <?= $proPeriod ?></span>
                </div>
                <hr class="divider">
                <ul class="feature-list">
                    <li class="feature-item yes">Tất cả đề thi</li>
                    <li class="feature-item yes">Kết quả sau khi làm bài</li>
                    <li class="feature-item yes">Lịch sử làm bài</li>
                    <li class="feature-item yes">Đề thi nâng cao</li>
                    <li class="feature-item yes">Phân tích điểm chi tiết</li>
                    <li class="feature-item yes">Lộ trình học cá nhân</li>
                    <li class="feature-item no">Hỗ trợ ưu tiên 24/7</li>
                    <li class="feature-item no">Tiết kiệm 16.7%</li>
                </ul>
                <a class="plan-btn btn-pro" href="payment.php?plan=pro">Nâng cấp Pro</a>
            </div>
            <!-- Gói ultra -->
            <div class="pricing-card card-ultra">
                <div class="card-header-row">
                    <h2 class="plan-name"><?= $ultraPlan ?></h2>
                    <span class="popular-tag" style="visibility: hidden;">Placeholder</span>
                </div>
                <div class="plan-price">
                    <span class="price-amount"><?= $ultraPrice ?>₫</span>
                    <span class="price-period">/ <?= $ultraPeriod ?></span>
                </div>

                <hr class="divider">
                <ul class="feature-list">
                    <li class="feature-item yes">Tất cả đề thi</li>
                    <li class="feature-item yes">Kết quả sau khi làm bài</li>
                    <li class="feature-item yes">Lịch sử làm bài</li>
                    <li class="feature-item yes">Đề thi nâng cao</li>
                    <li class="feature-item yes">Phân tích điểm chi tiết</li>
                    <li class="feature-item yes">Lộ trình học cá nhân</li>
                    <li class="feature-item yes">Hỗ trợ ưu tiên 24/7</li>
                    <li class="feature-item yes">Tiết kiệm 16.7%</li>
                </ul>
                <a class="plan-btn btn-pro" href="payment.php?plan=ultra">Nâng cấp Ultra</a>
            </div>
        </div>
    </section>
</body>

</html>