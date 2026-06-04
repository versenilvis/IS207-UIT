<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once '../../server/db/config.php';
require_once '../../server/middleware/auth.php';
homeRedirect();

$userId = $_SESSION['user_id'];
$fullName = trim(($_SESSION['last_name'] ?? '') . ' ' . ($_SESSION['first_name'] ?? ''));
$email = $_SESSION['email'] ?? '';
$isPremium = $_SESSION['is_premium'] ?? false;
$planId = $_SESSION['premium_plan'] ?? null;
$planName = $_SESSION['premium_name'] ?? null;
$planPrice = $_SESSION['premium_price'] ?? null;
$planPeriod = $_SESSION['premium_period'] ?? null;
$premiumUntil = $_SESSION['premium_until'] ?? null;

// lấy lịch sử giao dịch trực tiếp từ database để tránh lệch session
$stmtAllTx = $conn->prepare("SELECT tx_id as id, plan_id, plan_name, price, period, status, created_at FROM transaction_history WHERE user_id = :user_id ORDER BY created_at DESC");
$stmtAllTx->execute(['user_id' => $userId]);
$history = $stmtAllTx->fetchAll(PDO::FETCH_ASSOC);

$lastPayment = !empty($history) ? $history[0] : null;

$allPlans = require '../../server/config/premiumPlan.php';
if ($planId && isset($allPlans[$planId])) {
	$planPrice = $allPlans[$planId]['price'];
}

// nâng cấp session cũ có is_premium nhưng chưa có thông tin gói
if ($isPremium && empty($planId)) {
	$planId = 'pro'; // mặc định gói pro cho các session cũ
	$plan = $allPlans[$planId];
	$planName = $plan['name'];
	$planPrice = $plan['price'];
	$planPeriod = $plan['period'];
	// lưu lại session để chỉ chạy một lần
	$_SESSION['premium_plan'] = $planId;
	$_SESSION['premium_name'] = $planName;
	$_SESSION['premium_price'] = $planPrice;
	$_SESSION['premium_period'] = $planPeriod;
}

// fallback an toàn hiển thị
$planName = $planName ?? 'Free';
$planId = $planId ?? 'free';
$planPrice = $planPrice ?? 0;
$planPeriod = $planPeriod ?? '';

// Hiện banner thành công nếu vừa thanh toán
$showSuccess = isset($_GET['upgrade']) && $_GET['upgrade'] === 'success' && $lastPayment;

// Tính số ngày còn lại
$daysLeft = 0;
if ($premiumUntil) {
	$daysLeft = max(0, (int) ceil((strtotime($premiumUntil) - time()) / 86400));
}

// tính số tiền hoàn lại dự kiến
$totalPaid = 0;
$hasCombo = false;
$deductCourse = false;
$refundWindow = 7 * 86400; // 7 ngày

$ultraPrice = $allPlans['ultra']['price'] ?? 289000;
$ultraYearPrice = $allPlans['ultra_year']['price'] ?? 749000;

foreach ($history as $tx) {
	if ($tx['status'] === 'success' && (time() - strtotime($tx['created_at']) <= $refundWindow)) {
		$totalPaid += $tx['price'];
		if (in_array($tx['plan_id'] ?? '', ['ultra', 'ultra_year'])) {
			$hasCombo = true;
			if ($tx['plan_id'] === 'ultra' && $tx['price'] >= $ultraPrice) {
				$deductCourse = true;
			} elseif ($tx['plan_id'] === 'ultra_year' && $tx['price'] >= $ultraYearPrice) {
				$deductCourse = true;
			}
		}
	}
}

$refundAmount = $totalPaid;
if ($deductCourse) {
	$refundAmount = max(0, $totalPaid - 249000);
}

// Kiểm tra xem giao dịch gần nhất có được hoàn tiền hay không
$isLastPaymentRefundable = false;
if ($lastPayment && $lastPayment['status'] === 'success') {
	$isLastPaymentRefundable = (time() - strtotime($lastPayment['created_at']) <= $refundWindow);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lịch sử hoá đơn - PREPHUB</title>
	<?php include './components/metadata.php'; ?>
	<link rel="stylesheet" href="../styles/payment.css">
	<link rel="stylesheet" href="../styles/billing.css">
</head>

<body>
	<?php $navbarMode = 'light';
	include './components/navbar.php'; ?>

	<div class="billing-page">

		<!-- modal đặt hàng thành công -->
		<?php if ($showSuccess && $lastPayment): ?>
			<div class="pmo-overlay pmo-active" id="successOverlay">
				<div class="pmo-shell">


					<div class="pmo-success" style="padding-bottom: 20px;">
						<div class="pmo-success-ring">
							<svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#1d9e75" stroke-width="1.8"
								stroke-linecap="round" stroke-linejoin="round">
								<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
								<polyline points="22 4 12 14.01 9 11.01" />
							</svg>
						</div>
						<h3 class="pmo-success-title">Đặt hàng thành công</h3>
						<p class="pmo-success-desc">Cảm ơn bạn đã nâng cấp tài khoản tại Prephub.</p>
					</div>

					<div class="pmo-rule"></div>

					<div class="pmo-body" style="grid-template-columns: 1fr; padding-top: 20px;">
						<div class="pmo-details-col">
							<p class="pmo-section-label">Thông tin đơn hàng</p>

							<div class="pmo-row">
								<div style="display: flex; justify-content: space-between;">
									<span class="pmo-row-label">Mã đơn hàng</span>
									<span class="pmo-row-val pmo-mono"><?= htmlspecialchars($lastPayment['id']) ?></span>
								</div>
							</div>

							<div class="pmo-row">
								<div style="display: flex; justify-content: space-between;">
									<span class="pmo-row-label">Ngày đặt hàng</span>
									<span
										class="pmo-row-val"><?= date('H:i d/m/Y', strtotime($lastPayment['created_at'])) ?></span>
								</div>
							</div>

							<div class="pmo-row">
								<div style="display: flex; justify-content: space-between;">
									<span class="pmo-row-label">Sản phẩm</span>
									<span class="pmo-row-val">Gói <?= htmlspecialchars($lastPayment['plan_name']) ?></span>
								</div>
							</div>

							<div class="pmo-row">
								<div style="display: flex; justify-content: space-between;">
									<span class="pmo-row-label">Số tiền</span>
									<span
										class="pmo-row-val pmo-accent pmo-mono"><?= number_format($lastPayment['price'], 0, ',', '.') ?>₫</span>
								</div>
							</div>
						</div>
					</div>

					<div class="pmo-foot">
						<button class="pmo-cta" onclick="closeBanner()">
							<span class="pmo-cta-inner">Hoàn tất</span>
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- thẻ thông tin gói dịch vụ hiện tại -->
		<div class="billing-section">
			<h1 class="billing-page-title">Gói dịch vụ</h1>


			<?php if ($isPremium): ?>
				<div class="plan-active-card">
					<div class="pac-left">
						<span class="pac-plan-name"><?= htmlspecialchars($planName) ?></span>
						<div class="pac-meta">
							<span class="pac-price"><?= number_format($planPrice, 0, ',', '.') ?>₫<span
									class="pac-price-period">/<?= $planPeriod ?></span></span>
							<?php if ($premiumUntil): ?>
								<span class="pac-expiry">Hết hạn <?= date('d/m/Y', strtotime($premiumUntil)) ?> - còn
									<strong><?= $daysLeft ?> ngày</strong></span>
							<?php endif; ?>
						</div>
					</div>
					<div class="pac-right">
						<div class="pac-progress-wrap">
							<?php
							$total = $allPlans[$planId]['days'] ?? 30;
							$pct = ($total > 0) ? min(100, round($daysLeft / $total * 100)) : 0;
							?>
							<div class="pac-progress-bar">
								<div class="pac-progress-fill" style="width:<?= $pct ?>%"></div>
							</div>
							<span class="pac-progress-label">Còn <?= $daysLeft ?> / <?= $total ?> ngày</span>
						</div>
						<?php if ($isLastPaymentRefundable): ?>
							<button class="btn-refund" onclick="openRefundModal()" id="refundBtn">Hủy &amp; Hoàn tiền</button>
						<?php endif; ?>
					</div>
				</div>
			<?php else: ?>
				<div class="plan-free-card">
					<div class="pfc-info">
						<p class="pfc-name">Tài khoản Miễn phí</p>
						<p class="pfc-hint">Nâng cấp để mở khóa toàn bộ đề thi và giải thích đáp án chi tiết</p>
					</div>
					<a href="pricing.php" class="btn-upgrade">Nâng cấp ngay</a>
				</div>
			<?php endif; ?>
		</div>

		<!-- bảng lịch sử thanh toán -->
		<div class="billing-section">
			<h2 class="billing-section-title">Lịch sử thanh toán</h2>
			<?php if (empty($history)): ?>
				<div class="billing-empty">
					<p>Chưa có giao dịch nào</p>
					<span>Khi bạn nâng cấp tài khoản, lịch sử sẽ hiển thị tại đây.</span>
				</div>
			<?php else: ?>
				<div class="billing-table-wrap">
					<table class="billing-table">
						<thead>
							<tr>
								<th>Mã GD</th>
								<th>Gói</th>
								<th>Chu kỳ</th>
								<th>Ngày</th>
								<th>Số tiền</th>
								<th>Trạng thái</th>
								<th>Hành động</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($history as $tx): ?>
								<tr>
									<td class="bt-mono"><?= htmlspecialchars($tx['id']) ?></td>
									<td class="bt-plan-name"><?= htmlspecialchars($tx['plan_name']) ?></td>
									<td><?= htmlspecialchars($tx['period']) ?></td>
									<td><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></td>
									<td class="bt-price"><?= number_format($tx['price'], 0, ',', '.') ?>₫</td>
									<td
										class="bt-status bt-status--<?= $tx['status'] === 'refunded' ? 'refunded' : ($tx['status'] === 'failed' ? 'failed' : 'success') ?>">
										<?= $tx['status'] === 'success' ? 'Thành công' : ($tx['status'] === 'refunded' ? 'Đã hoàn tiền' : 'Thất bại') ?>
									</td>
									<td>
										<?php if ($tx['status'] === 'success' && $isPremium && $lastPayment && $tx['id'] === $lastPayment['id'] && (time() - strtotime($tx['created_at']) <= $refundWindow)): ?>
											<button class="bt-refund-link"
												onclick="openRefundModal('<?= htmlspecialchars($tx['id']) ?>')">Hoàn tiền</button>
										<?php else: ?>
											<span class="bt-na">-</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>

	</div>

	<!-- modal xác nhận hủy và hoàn tiền -->
	<div class="refund-overlay" id="refundOverlay">
		<div class="refund-modal">
			<h3 class="rfm-title">Xác nhận hủy gói</h3>
			<p class="rfm-desc">Bạn có chắc muốn hủy gói <strong><?= htmlspecialchars($planName) ?></strong> và nhận hoàn tiền <strong><?= number_format($refundAmount, 0, ',', '.') ?>₫</strong>? Tài khoản sẽ trở về mức Miễn phí ngay lập tức.
				<?php if ($deductCourse): ?>
					<br><span style="font-size: 0.85em; color: #71717a; margin-top: 4px; display: inline-block;">(Số tiền hoàn lại đã khấu trừ giá trị Khóa Học 249.000₫ không được hoàn trả)</span>
				<?php endif; ?>
			</p>
			<!-- Chi tiết hoàn tiền từng giao dịch -->
			<div class="rfm-details" style="margin-bottom: 20px; font-size: 0.82rem; color: #4b5563; background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0;">
				<div style="font-weight: 700; color: #0f172a; margin-bottom: 8px;">Chi tiết hoàn trả:</div>
				<div style="display: flex; flex-direction: column; gap: 6px;">
					<?php foreach ($history as $tx): ?>
						<?php if ($tx['status'] === 'success' && (time() - strtotime($tx['created_at']) <= $refundWindow)): ?>
							<div style="display: flex; justify-content: space-between; align-items: center;">
								<span>Gói <?= htmlspecialchars($tx['plan_name']) ?> (<?= date('d/m/Y', strtotime($tx['created_at'])) ?>)</span>
								<span style="font-family: monospace; font-weight: 600; color: #0f172a;">
									+<?= number_format($tx['price'], 0, ',', '.') ?>₫
								</span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if ($deductCourse): ?>
						<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; padding-top: 6px; border-top: 1px dashed #cbd5e1; color: #ef4444; font-weight: 600;">
							<span>Khấu trừ Khóa học (giữ quyền sở hữu)</span>
							<span style="font-family: monospace;">-249.000₫</span>
						</div>
					<?php endif; ?>

					<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">
						<span>Tổng tiền nhận lại:</span>
						<span style="font-family: monospace;"><?= number_format($refundAmount, 0, ',', '.') ?>₫</span>
					</div>
				</div>
			</div>
			<div class="rfm-note" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; margin: 0 0 24px;">
				<span style="font-size: 0.78rem; color: #64748b; line-height: 1.6; font-weight: 500;">Hoàn tiền thường được xử lý trong 3-5 ngày làm việc qua phương thức thanh toán ban đầu.</span>
			</div>
			<div class="rfm-actions">
				<button class="rfm-cancel" onclick="closeRefundModal()">Giữ gói</button>
				<button class="rfm-confirm" id="refundConfirmBtn" onclick="submitRefund()">
					<span class="rfm-confirm-text">Hủy và hoàn tiền</span>
					<div class="rfm-loader"></div>
				</button>
			</div>
		</div>
	</div>

	<?php include './components/footer.php'; ?>

	<script>
		let currentTxId = '<?= htmlspecialchars($lastPayment['id'] ?? '') ?>';

		function openRefundModal(txId) {
			if (txId) currentTxId = txId;
			document.getElementById('refundOverlay').classList.add('rfm-active');
			document.body.style.overflow = 'hidden';
		}

		function closeRefundModal() {
			document.getElementById('refundOverlay').classList.remove('rfm-active');
			document.body.style.overflow = '';
		}

		document.getElementById('refundOverlay').addEventListener('click', function (e) {
			if (e.target === this) closeRefundModal();
		});

		function submitRefund() {
			const btn = document.getElementById('refundConfirmBtn');
			btn.classList.add('rfm-loading');

			const form = new FormData();
			form.append('tx_id', currentTxId);

			fetch('../../server/controllers/payment-refund.php', { method: 'POST', body: form })
				.then(r => r.json())
				.then(data => {
					if (data.success) {
						setTimeout(() => { window.location.href = 'billing.php'; }, 800);
					} else {
						alert(data.message || 'Có lỗi xảy ra');
						btn.classList.remove('rfm-loading');
					}
				})
				.catch(() => {
					alert('Có lỗi kết nối, vui lòng thử lại');
					btn.classList.remove('rfm-loading');
				});
		}

		function closeBanner() {
			const b = document.getElementById('successOverlay');
			if (b) b.classList.remove('pmo-active');
			document.body.style.overflow = '';
		}

		// xóa tham số thành công trên url để khi f5 không bị hiện lại banner
		if (location.search.includes('upgrade=success')) {
			history.replaceState(null, '', location.pathname);
		}
	</script>
</body>

</html>