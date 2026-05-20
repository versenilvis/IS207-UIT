<?php
$STK = '1027384290'; // sô fake
$BANK_ID = '970436';
$ACCOUNT_NAME = 'PREPHUB TEAM';
?>

<!-- ── Payment Modal ─────────────────────── -->
<div class="pmo-overlay" id="paymentOverlay">
	<div class="pmo-shell" role="dialog" aria-modal="true" aria-label="Thanh toán">

		<!-- ── Payment view ── -->
		<div id="pmoPayView">

			<!-- header -->
			<div class="pmo-head">
				<span class="pmo-badge" id="pmPlanBadge">PREMIUM</span>
				<div class="pmo-price-row">
					<div class="pmo-price-wrap">
						<span class="pmo-price-old" id="pmPriceOld"></span>
						<span class="pmo-price" id="pmPrice">63.200</span>
						<span class="pmo-cur">₫</span>
					</div>
					<span class="pmo-period" id="pmPeriod">/tháng</span>
				</div>
				<span class="pmo-discount-note" id="pmDiscountNote" style="display:none">Đã trừ gói Premium đã
					mua</span>
			</div>

			<div class="pmo-rule"></div>

			<!-- body: QR + details -->
			<div class="pmo-body">

				<!-- QR -->
				<div class="pmo-qr-col">
					<p class="pmo-section-label">Quét QR thanh toán</p>
					<div class="pmo-qr-frame" id="pmQrContainer">
						<div class="pmo-spinner"></div>
					</div>
					<p class="pmo-qr-hint">VietQR · Vietcombank</p>
				</div>

				<!-- Details -->
				<div class="pmo-details-col">
					<p class="pmo-section-label">Chi tiết chuyển khoản</p>

					<div class="pmo-row">
						<span class="pmo-row-label">Ngân hàng</span>
						<span class="pmo-row-val">Vietcombank</span>
					</div>

					<div class="pmo-row">
						<span class="pmo-row-label">Chủ tài khoản</span>
						<span class="pmo-row-val"><?= htmlspecialchars($ACCOUNT_NAME) ?></span>
					</div>

					<div class="pmo-row pmo-row--copy">
						<span class="pmo-row-label">Số tài khoản</span>
						<div class="pmo-row-inline">
							<span class="pmo-row-val pmo-mono"><?= $STK ?></span>
							<button class="pmo-copy-btn" onclick="pmCopy('<?= $STK ?>', this)">
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
									stroke-width="2.3" stroke-linecap="round">
									<rect x="9" y="9" width="13" height="13" rx="2" />
									<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
								</svg>
								Sao chép
							</button>
						</div>
					</div>

					<div class="pmo-row pmo-row--copy">
						<span class="pmo-row-label">Số tiền</span>
						<div class="pmo-row-inline">
							<span class="pmo-row-val pmo-mono pmo-accent" id="pmAmount">63.200₫</span>
							<button class="pmo-copy-btn"
								onclick="pmCopy(document.getElementById('pmAmount').textContent, this)">
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
									stroke-width="2.3" stroke-linecap="round">
									<rect x="9" y="9" width="13" height="13" rx="2" />
									<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
								</svg>
								Sao chép
							</button>
						</div>
					</div>

					<div class="pmo-row pmo-row--copy pmo-row--content">
						<span class="pmo-row-label">Nội dung chuyển khoản</span>
						<div class="pmo-row-inline">
							<span class="pmo-row-val pmo-mono" id="pmContent"></span>
							<button class="pmo-copy-btn"
								onclick="pmCopy(document.getElementById('pmContent').textContent, this)">
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
									stroke-width="2.3" stroke-linecap="round">
									<rect x="9" y="9" width="13" height="13" rx="2" />
									<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
								</svg>
								Sao chép
							</button>
						</div>
					</div>

					<div class="pmo-note">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
							stroke-width="2" stroke-linecap="round">
							<circle cx="12" cy="12" r="10" />
							<line x1="12" y1="16" x2="12" y2="12" />
							<line x1="12" y1="8" x2="12.01" y2="8" />
						</svg>
						<span>Nội dung chuyển khoản phải chính xác để hệ thống tự kích hoạt.</span>
					</div>
				</div>
			</div>

			<!-- CTA -->
			<div class="pmo-foot">
				<button class="pmo-cta" id="pmCtaBtn" onclick="pmSimulate()">
					<span class="pmo-cta-inner"><i class="bx bx-qr"></i> Giả lập thanh toán</span>
					<span class="pmo-cta-loader"></span>
				</button>
				<p class="pmo-support">Gặp khó khăn? Hotline <strong>1900 8888</strong></p>
			</div>
		</div>

		<!-- ── Success view ── -->
		<div id="pmoSuccessView" class="pmo-success" style="display:none;">
			<div class="pmo-success-ring">
				<svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#1d9e75" stroke-width="1.8"
					stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
					<polyline points="22 4 12 14.01 9 11.01" />
				</svg>
			</div>
			<h3 class="pmo-success-title">Thanh toán thành công</h3>
			<p class="pmo-success-desc">Tài khoản đã được nâng cấp lên <strong id="pmSuccessPlan">Premium</strong>. Đang
				chuyển hướng…</p>
		</div>

	</div>
</div>

<script>
	const pmBankId = '<?= $BANK_ID ?>';
	const pmStk = '<?= $STK ?>';
	const pmAccName = '<?= $ACCOUNT_NAME ?>';
	var currentPlanId = 'pro';
	var currentPlanPrice = '0';
	<?php if (isset($isPremium)): ?>
		var isPremiumUser = <?= $isPremium ? 'true' : 'false' ?>;
	<?php else: ?>
		var isPremiumUser = false;
	<?php endif; ?>

	function openPaymentModal(planId, planName, btn) {
		currentPlanId = planId;

		var card = btn.closest('.pricing-card');
		var priceEl = card.querySelector('.price-amount');
		var periodEl = card.querySelector('.price-period');
		var planPrice = priceEl.getAttribute('data-total') || priceEl.textContent.trim();
		currentPlanPrice = planPrice;
		var planPeriod = periodEl.textContent.trim();
		if (currentPlanId === 'pro_year' || currentPlanId === 'ultra_year') {
			planPeriod = '/năm';
		} else if (currentPlanId === 'pro' || currentPlanId === 'ultra') {
			planPeriod = '/tháng';
		} else {
			planPeriod = '/khoá';
		}

		// capture original price for strikethrough in modal
		var originalPrice = null;
		var oldEl = document.getElementById('pmPriceOld');
		var noteEl = document.getElementById('pmDiscountNote');

		if (planId === 'ultra' && isPremiumUser) {
			var priceWrap = btn.closest('.pricing-card').querySelector('.price-old');
			if (priceWrap) {
				originalPrice = priceWrap.textContent.trim();
			}
		}

		document.getElementById('pmoPayView').style.display = '';
		document.getElementById('pmoSuccessView').style.display = 'none';
		document.querySelector('.pmo-cta').classList.remove('pmo-loading');
		document.querySelector('.pmo-cta').disabled = false;
		document.querySelector('.pmo-cta-inner').innerHTML = '<i class="bx bx-qr"></i> Giả lập thanh toán';

		document.getElementById('pmPlanBadge').textContent = planName;
		document.getElementById('pmPrice').textContent = planPrice.replace(/[₫đ]/gi, '');
		document.getElementById('pmPeriod').textContent = planPeriod;
		document.getElementById('pmAmount').textContent = planPrice;
		document.getElementById('pmSuccessPlan').textContent = planName;

		// old price strikethrough + discount note
		if (originalPrice) {
			oldEl.textContent = originalPrice;
			oldEl.style.display = 'inline';
			noteEl.style.display = 'inline-block';
		} else {
			oldEl.textContent = '';
			oldEl.style.display = 'none';
			noteEl.style.display = 'none';
		}

		var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		var code = Array.from({ length: 6 }, function () { return chars[Math.floor(Math.random() * chars.length)]; }).join('');
		var content = 'PREPHUB ' + planName.toUpperCase() + ' ' + code;
		document.getElementById('pmContent').textContent = content;

		var qrUrl = 'https://img.vietqr.io/image/' + pmBankId + '-' + pmStk + '-compact.png'
			+ '?amount=' + planPrice.replace(/[^0-9]/g, '')
			+ '&addInfo=' + encodeURIComponent(content)
			+ '&accountName=' + encodeURIComponent(pmAccName);
		var container = document.getElementById('pmQrContainer');
		container.innerHTML = '<div class="pmo-spinner"></div>';
		var img = new Image();
		img.alt = 'VietQR';
		img.onload = function () { container.innerHTML = ''; container.appendChild(img); };
		img.src = qrUrl;

		document.getElementById('paymentOverlay').classList.add('pmo-active');
		document.body.style.overflow = 'hidden';
	}

	function closePaymentModal() {
		document.getElementById('paymentOverlay').classList.remove('pmo-active');
		document.body.style.overflow = '';
	}

	document.getElementById('paymentOverlay').addEventListener('click', function (e) {
		if (e.target === this) closePaymentModal();
	});

	function pmCopy(text, btn) {
		navigator.clipboard.writeText(text.trim()).then(function () {
			btn.classList.add('pmo-copied');
			var orig = btn.innerHTML;
			btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg> Đã sao chép';
			setTimeout(function () {
				btn.innerHTML = orig;
				btn.classList.remove('pmo-copied');
			}, 1600);
		});
	}

	function pmSimulate() {
		var btn = document.getElementById('pmCtaBtn');
		var inner = btn.querySelector('.pmo-cta-inner');
		btn.classList.add('pmo-loading');
		btn.disabled = true;

		var form = new FormData();
		form.append('plan_id', currentPlanId);
		form.append('amount', currentPlanPrice.replace(/[^0-9]/g, ''));

		fetch('../../server/controllers/payment-success.php', { method: 'POST', body: form })
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (data.success) {
					setTimeout(function () {
						btn.classList.remove('pmo-loading');
						inner.innerHTML = '<i class="bx bx-check-circle" style="font-size:1.4rem"></i>';
						btn.style.background = '#059669';
						setTimeout(function () {
							closePaymentModal();
							btn.style.background = '';
							inner.innerHTML = '<i class="bx bx-qr"></i> Giả lập thanh toán';
							btn.disabled = false;
							window.location.href = 'billing.php?upgrade=success';
						}, 1200);
					}, 1800);
				} else {
					alert(data.message || 'Giao dịch bị từ chối!');
					btn.classList.remove('pmo-loading');
					btn.disabled = false;
				}
			})
			.catch(function (err) {
				console.error(err);
				alert('Có lỗi xảy ra, vui lòng thử lại!');
				btn.classList.remove('pmo-loading');
				btn.disabled = false;
			});
	}
</script>