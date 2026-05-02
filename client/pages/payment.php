<?php
$plans = require_once '../../server/config/premiumPlan.php'; //Hiển thị các plan options
$selected_plan = $_GET['plan'] ?? 'pro'; //Nếu plan bỏ trống thì mặc định chọn pro 

//Kiểm tra xem selected plan có trong plan config không. Tránh trường hợp người dùng điền bừa trên URL.
if (!array_key_exists($selected_plan, $plans)) {
    header('Location: premium.php');
    exit;
}

$selected_plan = $plans[$selected_plan];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - PREPHUB</title>
    <?php include './components/metadata.php'; ?>
    <link rel="stylesheet" href="../styles/payment.css">
</head>

<body>
    <?php include './components/navBar.php'; ?>

    <main class="checkout-wrapper">

        <!-- LEFT: Payment Form -->
        <div class="checkout-left">
            <a href="premium.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Quay lại
            </a>
            <h1 class="checkout-title">Thanh toán</h1>

            <div class="form-section">
                <h2 class="form-label">Phương thức thanh toán</h2>

                <!-- Card number with real logos -->
                <div class="input-group">
                    <input type="text" placeholder="1234 1234 1234 1234" class="input-field" maxlength="19" id="card-number">
                    <div class="card-icons">
                        <img src="https://cdn.jsdelivr.net/gh/aaronfagan/svg-credit-card-payment-icons@main/flat/visa.svg" alt="Visa">
                        <img src="https://cdn.jsdelivr.net/gh/aaronfagan/svg-credit-card-payment-icons@main/flat/mastercard.svg" alt="Mastercard">
                    </div>
                </div>

                <div class="input-row">
                    <input type="text" placeholder="Ngày hết hạn (MM/YY)" class="input-field" maxlength="5">
                    <input type="text" placeholder="Mã bảo mật (CVV)" class="input-field" maxlength="4">
                </div>

                <div class="input-group">
                    <input type="text" placeholder="Họ tên đầy đủ" class="input-field">
                </div>
            </div>

            <div class="form-section">
                <h2 class="form-label">Thông tin thanh toán</h2>
                <div class="input-group">
                    <input type="email" placeholder="Email" class="input-field">
                </div>
            </div>
        </div>

        <!-- RIGHT: Order Summary -->
        <div class="checkout-right">
            <div class="summary-card">
                <h2 class="summary-plan-name">Gói <?=$selected_plan['name']?></h2>
                <p class="summary-subtitle">Tính năng nổi bật</p>

                <ul class="summary-features">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Toàn bộ đề thi
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Phân tích điểm chi tiết
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Lộ trình học cá nhân
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Lịch sử làm bài đầy đủ
                    </li>
                </ul>

                <div class="summary-divider"></div>

                <div class="summary-row">
                    <span>Đăng ký theo <?=$selected_plan['period']?></span>
                    <span><?=number_format($selected_plan['price'], 0, ',', '.')?>₫</span>
                </div>
                <div class="summary-row">
                    <span>Thuế VAT</span>
                    <span><?=number_format($selected_plan['price']*0.1, 0, ',', '.')?>₫</span>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-row summary-total">
                    <span>Tổng thanh toán</span>
                    <span><?=number_format($selected_plan['price']*1.1, 0, ',', '.')?>₫</span>
                </div>

                <button class="pay-btn">Đăng ký ngay</button>

                <p class="summary-note">
                    Tự động gia hạn hàng tháng. Bạn có thể hủy bất kỳ lúc nào trong Cài đặt.
                </p>
            </div>
        </div>

    </main>

    <script>
        // Format lại thẻ ngân hàng theo format 1111 2222 3333 4444
        document.getElementById('card-number').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 16);
            e.target.value = value.replace(/(.{4})/g, '$1 ').trim();
        });
    </script>
</body>

</html>