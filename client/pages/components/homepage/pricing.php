<?php
// lấy session vars - giống pricing.php
if (!isset($isPremium)) {
	$isPremium = $_SESSION['is_premium'] ?? false;
	$isLoggedIn = isset($_SESSION['user_id']);
	$currentPlan = $_SESSION['premium_plan'] ?? 'free';
	$hasCourse = $_SESSION['has_course'] ?? in_array($currentPlan, ['course', 'ultra', 'ultra_year']);
	$planTier = ['free' => 0, 'pro' => 1, 'pro_year' => 1, 'course' => 1, 'ultra' => 2, 'ultra_year' => 2];
	$userTier = $planTier[$currentPlan] ?? 0;
}

$svgCheck = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><polyline points="216 72.005 104 184 48 128.005" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"/></svg>';
$svgCross = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><line x1="200" y1="56" x2="56" y2="200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"/><line x1="200" y1="200" x2="56" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"/></svg>';
$svgTick = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
?>

<style>
	/* chỉ scope trong khối pricing homepage */
	.hp-pricing {
		background: #05102b;
		padding: 80px 0 60px;
	}

	.hp-pricing .pricing-switch {
		justify-content: center;
		align-items: center;
		gap: 16px;
	}

	.hp-pricing h2 {
		font-size: clamp(1.8rem, 3vw, 2.6rem);
		font-weight: 700;
		color: #fff;
		text-align: center;
		margin: 0 0 28px;
		letter-spacing: -0.02em;
	}

	/* wrapper căn giữa, không dùng .pricing-container để tránh nest grid */
	.hp-pricing .hp-inner {
		max-width: 1300px;
		margin: 0 auto;
		padding: 0 24px;
	}

	/* switch tháng/năm cần màu sáng hơn vì nền tối */
	.hp-pricing .switch-btn {
		color: rgba(255, 255, 255, 0.5);
	}

	.hp-pricing .switch-btn.active {
		color: #ffffff;
		font-weight: 600;
	}

	.hp-pricing .toggle-container {
		background-color: var(--accent) !important;
	}

	.hp-pricing .discount-badge {
		background: rgba(16, 185, 129, 0.2);
		color: #10b981;
	}
</style>

<section class="hp-pricing" id="pricing">
	<div class="hp-inner">
		<h2>Đầu tư cho hành trình TOEIC của bạn</h2>

		<div class="pricing-switch" style="margin-bottom:40px;">
			<span class="switch-btn active" id="monthlyBtn">Theo tháng</span>
			<div class="toggle-container" id="priceToggle">
				<div class="toggle-circle"></div>
			</div>
			<span class="switch-btn" id="yearlyBtn">Theo năm</span>
			<span class="discount-badge">Tiết kiệm 20%</span>
		</div>

		<!-- dùng đúng .pricing-container như pricing.php để CSS apply nhất quán -->
		<div class="pricing-container">

			<!-- FREE -->
			<div class="pricing-card">
				<div class="plan-name">FREE</div>
				<div class="price-old-wrapper" style="min-height:1.2rem; margin-bottom:2px;"></div>
				<div class="plan-price-wrapper">
					<span class="price-amount" data-monthly="0đ" data-yearly="0đ">0đ</span>
					<span class="price-period" data-monthly-suffix="/tháng" data-yearly-suffix="/tháng">/tháng</span>
				</div>
				<div class="price-subtext" style="min-height:1.1rem; margin-top:4px;"></div>
				<div class="plan-desc">Làm quen với nền tảng, thử sức với đề mẫu</div>
				<ul class="features-list">
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> 5 đề thi mẫu
						/ tháng</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Xem đáp án
						sau khi nộp</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Thống kê kết
						quả cơ bản</li>
					<li class="feature-item disabled"><span class="feature-icon icon-cross"><?= $svgCross ?></span>
						Không có đề mới nhất</li>
					<li class="feature-item disabled"><span class="feature-icon icon-cross"><?= $svgCross ?></span>
						Không có giải thích chi tiết</li>
					<li class="feature-item disabled"><span class="feature-icon icon-cross"><?= $svgCross ?></span>
						Không có lộ trình học</li>
				</ul>
				<?php if (!$isLoggedIn): ?>
					<a href="javascript:void(0)" class="plan-btn btn-outline" data-bs-toggle="modal"
						data-bs-target="#loginModal">Bắt đầu miễn phí</a>
				<?php else: ?>
					<a href="home.php" class="plan-btn btn-outline">Bắt đầu miễn phí</a>
				<?php endif; ?>
			</div>

			<!-- PREMIUM -->
			<div class="pricing-card card-featured">
				<span class="featured-tag">Lựa chọn tốt nhất</span>
				<div class="plan-name">PREMIUM</div>
				<div class="price-old-wrapper" style="min-height:1.2rem; margin-bottom:2px;">
					<span class="price-old" style="text-decoration:line-through; color:#a1a1aa;" data-monthly="99.000đ"
						data-yearly="828.000đ">99.000đ</span>
				</div>
				<div class="plan-price-wrapper">
					<?php if ($isPremium): ?>
						<span class="price-amount" data-monthly="69.000đ" data-yearly="43.000đ">69.000đ</span>
					<?php else: ?>
						<span class="price-amount" data-monthly="69.000đ" data-yearly="49.000đ">69.000đ</span>
					<?php endif; ?>
					<span class="price-period" data-monthly-suffix="/tháng" data-yearly-suffix="/tháng">/tháng</span>
				</div>
				<div class="price-subtext" style="min-height:1.1rem; margin-top:4px;" data-monthly="" data-yearly="">
				</div>
				<div class="plan-desc">Unlock toàn bộ kho đề thi, luôn cập nhật mới nhất.</div>
				<ul class="features-list">
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Toàn bộ kho
						đề (1.500+ đề)</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Cập nhật đề
						mới nhất 2026</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Giải thích
						đáp án chi tiết</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Phân tích
						điểm yếu theo Part</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Lịch sử &amp;
						thống kê chi tiết</li>
					<li class="feature-item disabled"><span class="feature-icon icon-cross"><?= $svgCross ?></span>
						Không có khoá học video</li>
				</ul>
				<?php if (!$isLoggedIn): ?>
					<a href="javascript:void(0)" class="plan-btn btn-white" data-bs-toggle="modal"
						data-bs-target="#loginModal">Đăng ký ngay</a>
				<?php else: ?>
					<div class="monthly-action">
						<?php if (in_array($currentPlan, ['pro', 'pro_year', 'ultra', 'ultra_year'])): ?>
							<a href="billing.php" class="plan-btn btn-current"><?= $svgTick ?> Gói hiện tại</a>
						<?php else: ?>
							<a href="pricing.php" class="plan-btn btn-white">Đăng ký ngay</a>
						<?php endif; ?>
					</div>
					<div class="yearly-action" style="display:none;">
						<?php if (in_array($currentPlan, ['pro_year', 'ultra_year'])): ?>
							<a href="billing.php" class="plan-btn btn-current"><?= $svgTick ?> Gói hiện tại</a>
						<?php else: ?>
							<a href="pricing.php"
								class="plan-btn btn-white"><?= $isPremium ? 'Nâng cấp lên Năm' : 'Đăng ký ngay' ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- KHOÁ HỌC -->
			<div class="pricing-card">
				<div class="plan-name">KHOÁ HỌC</div>
				<div class="price-old-wrapper" style="min-height:1.2rem; margin-bottom:2px;">
					<span class="price-old" style="text-decoration:line-through; color:#a1a1aa;" data-monthly="350.000đ"
						data-yearly="350.000đ">350.000đ</span>
				</div>
				<div class="plan-price-wrapper">
					<span class="price-amount" data-monthly="249.000đ" data-yearly="249.000đ">249.000đ</span>
					<span class="price-period" data-monthly-suffix="/vĩnh viễn" data-yearly-suffix="/vĩnh viễn">/vĩnh
						viễn</span>
				</div>
				<div class="price-subtext" style="min-height:1.1rem; margin-top:4px;"></div>
				<div class="plan-desc">Mua lẻ khoá học theo mục tiêu, sở hữu vĩnh viễn.</div>
				<ul class="features-list">
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Video bài
						giảng theo lộ trình</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Bài tập kèm
						theo mỗi bài học</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Sở hữu trọn
						đời</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Cập nhật nội
						dung miễn phí</li>
					<li class="feature-item disabled"><span class="feature-icon icon-cross"><?= $svgCross ?></span>
						Không có kho đề Premium</li>
					<li class="feature-item disabled"><span class="feature-icon icon-cross"><?= $svgCross ?></span> Phân
						tích điểm yếu nâng cao</li>
				</ul>
				<?php if (!$isLoggedIn): ?>
					<a href="javascript:void(0)" class="plan-btn btn-outline" data-bs-toggle="modal"
						data-bs-target="#loginModal">Đăng ký ngay</a>
				<?php elseif ($hasCourse): ?>
					<a href="pricing.php" class="plan-btn btn-outline">Xem các khoá học</a>
				<?php else: ?>
					<a href="pricing.php" class="plan-btn btn-outline">Mua khoá học</a>
				<?php endif; ?>
			</div>

			<!-- TRỌN BỘ -->
			<div class="pricing-card">
				<div class="plan-name">TRỌN BỘ</div>
				<div class="price-old-wrapper" style="min-height:1.2rem; margin-bottom:2px;">
					<span class="price-old" style="text-decoration:line-through; color:#a1a1aa;" data-monthly="419.000đ"
						data-yearly="938.000đ">419.000đ</span>
				</div>
				<div class="plan-price-wrapper">
					<?php if ($currentPlan === 'ultra'): ?>
						<span class="price-amount" data-monthly="289.000đ" data-yearly="38.000đ">289.000đ</span>
					<?php elseif ($isPremium): ?>
						<span class="price-amount" data-monthly="220.000đ" data-yearly="56.000đ">220.000đ</span>
					<?php else: ?>
						<span class="price-amount" data-monthly="289.000đ" data-yearly="62.000đ">289.000đ</span>
					<?php endif; ?>
					<span class="price-period" data-monthly-suffix="/khoá" data-yearly-suffix="/tháng">/khoá</span>
				</div>
				<div class="price-subtext" style="font-size:0.75rem; color:#666; margin-top:4px; min-height:1.1rem;"
					data-monthly="(Gồm Khoá học + Premium 1 tháng)" data-yearly="(Gồm Khoá học + Premium 1 năm)">(Gồm
					Khoá học + Premium 1 tháng)</div>
				<div class="plan-desc">Tiết kiệm nhất cho người nghiêm túc.</div>
				<ul class="features-list">
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Gói Premium 1
						tháng</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Toàn bộ khoá
						học hiện có</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Khoá học mới
						phát hành</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Lộ trình học
						cá nhân hoá</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Ưu tiên hỗ
						trợ</li>
					<li class="feature-item"><span class="feature-icon icon-check"><?= $svgCheck ?></span> Chứng chỉ
						hoàn thành khoá</li>
				</ul>
				<?php if (!$isLoggedIn): ?>
					<a href="javascript:void(0)" class="plan-btn btn-outline" data-bs-toggle="modal"
						data-bs-target="#loginModal">Đăng ký ngay</a>
				<?php else: ?>
					<div class="monthly-action">
						<?php if (in_array($currentPlan, ['ultra', 'ultra_year']) || ($isPremium && $hasCourse)): ?>
							<a href="billing.php" class="plan-btn btn-current"><?= $svgTick ?> Gói hiện tại</a>
						<?php else: ?>
							<a href="pricing.php"
								class="plan-btn btn-outline"><?= ($isPremium && $userTier < 2) ? 'Nâng cấp lên Trọn Bộ' : 'Đăng ký ngay' ?></a>
						<?php endif; ?>
					</div>
					<div class="yearly-action" style="display:none;">
						<?php if ($currentPlan === 'ultra_year' || ($currentPlan === 'pro_year' && $hasCourse)): ?>
							<a href="billing.php" class="plan-btn btn-current"><?= $svgTick ?> Gói hiện tại</a>
						<?php elseif ($currentPlan === 'ultra'): ?>
							<a href="pricing.php" class="plan-btn btn-outline">Nâng cấp lên Năm</a>
						<?php else: ?>
							<a href="pricing.php"
								class="plan-btn btn-outline"><?= ($isPremium && $userTier < 2) ? 'Nâng cấp lên Trọn Bộ Năm' : 'Đăng ký ngay' ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

		</div><!-- /.pricing-container -->
	</div><!-- /.hp-inner -->
</section>