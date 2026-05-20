<?php
// pull recent attempts from session or API - for now stub with empty state
$hasPremium = !empty($_SESSION['is_premium']);
$premiumPlanName = $_SESSION['premium_name'] ?? null;
$premiumUntil = $_SESSION['premium_until'] ?? null;
?>

<div class="dash-widgets">

	<!-- quick nav row -->
	<div class="dash-quicknav">
		<a href="tests.php" class="dash-qn-item">
			<span class="dash-qn-icon">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					stroke-linecap="round" stroke-linejoin="round">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
					<polyline points="14 2 14 8 20 8" />
					<line x1="16" y1="13" x2="8" y2="13" />
					<line x1="16" y1="17" x2="8" y2="17" />
					<polyline points="10 9 9 9 8 9" />
				</svg>
			</span>
			<span class="dash-qn-label">Kho đề thi</span>
		</a>
		<a href="attempts.php" class="dash-qn-item">
			<span class="dash-qn-icon">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="10" />
					<polyline points="12 6 12 12 16 14" />
				</svg>
			</span>
			<span class="dash-qn-label">Lịch sử</span>
		</a>
		<a href="user.php" class="dash-qn-item">
			<span class="dash-qn-icon">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					stroke-linecap="round" stroke-linejoin="round">
					<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
					<circle cx="12" cy="7" r="4" />
				</svg>
			</span>
			<span class="dash-qn-label">Hồ sơ</span>
		</a>
		<a href="profile.php" class="dash-qn-item">
			<span class="dash-qn-icon">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="3" />
					<path
						d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
				</svg>
			</span>
			<span class="dash-qn-label">Cài đặt</span>
		</a>
		<a href="pricing.php" class="dash-qn-item">
			<span class="dash-qn-icon">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
					stroke-linecap="round" stroke-linejoin="round">
					<polygon
						points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
				</svg>
			</span>
			<span class="dash-qn-label">Nâng cấp</span>
		</a>
	</div>


	<!-- premium status card (only shown if premium) -->
	<?php if ($hasPremium && $premiumPlanName): ?>
		<div class="dash-premium-strip">
			<div class="dash-ps-left">
				<span class="dash-ps-icon">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z" />
					</svg>
				</span>
				<div>
					<div class="dash-ps-title">Gói <?= htmlspecialchars($premiumPlanName) ?></div>
					<?php if ($premiumUntil): ?>
						<div class="dash-ps-sub">Hiệu lực đến <?= date('d/m/Y', strtotime($premiumUntil)) ?></div>
					<?php endif; ?>
				</div>
			</div>
			<a href="billing.php" class="dash-ps-btn">Quản lý</a>
		</div>
	<?php else: ?>
		<div class="dash-upsell-strip">
			<div class="dash-us-left">
				<span class="dash-us-icon">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
						stroke-linecap="round" stroke-linejoin="round">
						<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z" />
					</svg>
				</span>
				<div>
					<div class="dash-us-title">Mở khoá toàn bộ kho đề</div>
					<div class="dash-us-sub">1.500+ đề thi, giải thích chi tiết, phân tích điểm yếu</div>
				</div>
			</div>
			<a href="pricing.php" class="dash-us-btn">Nâng cấp</a>
		</div>
	<?php endif; ?>

	<!-- recent activity: populated via JS from dashboard.js -->
	<div class="dash-recent">
		<div class="dash-section-head">
			<span class="dash-section-title">Bài làm gần đây</span>
			<a href="attempts.php" class="dash-see-all">Xem tất cả <i class='bx bx-chevron-right'></i></a>
		</div>
		<div class="dash-recent-list" id="dash-recent-list">
			<!-- skeleton placeholders -->
			<div class="dash-recent-skeleton"></div>
			<div class="dash-recent-skeleton"></div>
			<div class="dash-recent-skeleton"></div>
		</div>
	</div>

</div>