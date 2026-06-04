<?php
$isPremium = $_SESSION['is_premium'] ?? false;
$premiumName = $_SESSION['premium_name'] ?? 'Premium';

if ($isPremium) {
  return;
}
?>
<div class="pro-card" data-is-premium="<?= $isPremium ? 'true' : 'false' ?>">
  <button type="button" class="pro-card__close" aria-label="Đóng" onclick="dismissProCard(event)">
    <i class="fas fa-xmark"></i>
  </button>
  <div class="pro-card__banner">
    <div class="iridescence-container"></div>
    <img src="../img/logo.svg" alt="Prephub" class="pro-card__banner-logo">
    <span class="pro-card__banner-text">Prephub</span>
  </div>

  <div class="pro-card__body">
    <?php if ($isPremium): ?>
      <h2 class="pro-card__title">Tài khoản Premium!</h2>
      <p class="pro-card__subtitle">
        Chúc mừng bạn đã sở hữu gói <strong><?= htmlspecialchars($premiumName) ?></strong>. Mọi đặc quyền học tập đã được mở khóa!
      </p>

      <div class="pro-card__discount" style="background:rgba(29, 158, 117, 0.04); border-color:rgba(29, 158, 117, 0.1);">
        <div class="pro-card__avatar" style="box-shadow: 0 4px 12px rgba(29, 158, 117, 0.2); width:48px; height:48px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative;">
          <div class="pro-card__avatar-img" style="width:100%; height:100%; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center;">
            <span class="pro-card__emoji" style="width:100%; height:100%;">
              <img src="https://i.pinimg.com/1200x/8a/d5/69/8ad569c206c4981e5c6a4da116b98227.jpg" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            </span>
          </div>
          <span class="pro-card__badge" style="position:absolute; top:-2px; left:-6px; background:#1d9e75; color:#fff; font-size:7px; font-weight:900; padding:3px 6px; border-radius:6px; line-height:1; text-transform:uppercase;">PRO</span>
        </div>
        <div class="pro-card__discount-text">
          <strong style="color:#1d9e75; display:block; font-size:14px; font-weight:700; margin-bottom:2px;">Đã kích hoạt Premium</strong>
          <span style="font-size:12px; color:#64748b; font-weight:500;">Cảm ơn bạn đã tin dùng Prephub</span>
        </div>
      </div>

      <a href="billing.php" class="pro-card__btn" style="background:#1d9e75; color:#fff;">
        <i class="fas fa-receipt"></i>
        <span>Lịch sử hóa đơn</span>
      </a>
    <?php else: ?>
      <h2 class="pro-card__title">Nâng cấp ngay với gói PRO!</h2>
      <p class="pro-card__subtitle">
        Mở khoá toàn bộ đề thi, giải thích đáp án chi tiết, lịch sử và thống kê chi tiết
      </p>

      <div class="pro-card__discount">
        <div class="pro-card__avatar" style="width:48px; height:48px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; box-shadow: 0 4px 12px rgba(255, 182, 193, 0.3);">
          <div class="pro-card__avatar-img" style="width:100%; height:100%; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center;">
            <span class="pro-card__emoji" style="width:100%; height:100%;">
              <img src="https://i.pinimg.com/1200x/8a/d5/69/8ad569c206c4981e5c6a4da116b98227.jpg" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            </span>
          </div>
          <span class="pro-card__badge" style="position:absolute; top:-2px; left:-6px; background:linear-gradient(to right, #90D5EC, #FDC365, #F484A0, #C1A0D8); color:#000; font-size:7px; font-weight:900; padding:3px 6px; border-radius:6px; line-height:1; text-transform:uppercase;">PRO</span>
        </div>
        <div class="pro-card__discount-text">
          <strong style="display:block; font-size:14px; font-weight:700; color:#111; margin-bottom:2px;">Giảm 50% cho tháng đầu tiên</strong>
          <span style="font-size:12px; color:#64748b; font-weight:500;">Ưu đãi dành riêng cho bạn</span>
        </div>
      </div>

      <a href="pricing.php" class="pro-card__btn">
        <i class="fas fa-crown"></i>
        <span>Nâng cấp tài khoản</span>
        <div class="shine-overlay"></div>
      </a>
    <?php endif; ?>
  </div>
</div>
