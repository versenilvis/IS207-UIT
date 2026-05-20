<?php
session_start();
require_once '../../server/middleware/auth.php';
require_once '../../server/controllers/profile-controller.php';
homeRedirect();

$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($lastName . ' ' . $firstName) ?: 'Người dùng';
$isPremium = $_SESSION['is_premium'] ?? false;
$premiumName = $_SESSION['premium_name'] ?? null;
$userId = (int) $_SESSION['user_id'];

$maxScore = getMaxScore();
$avgScore = getAvgScore();
$totalTests = getNumTestDone();

date_default_timezone_set('Asia/Ho_Chi_Minh');
$hour = (int) date('H');
$greet = $hour < 12 ? 'Chào buổi sáng ☀️' : ($hour < 18 ? 'Chào buổi chiều 🌤️' : 'Chào buổi tối 🌙');
?>
<!doctype html>
<html lang="vi">

<head>
	<?php include './components/metadata.php'; ?>
	<title>Hồ sơ · Prephub</title>
	<link rel="stylesheet" href="../styles/user.css">
</head>

<body>
	<?php $navbarMode = 'dark';
	include './components/navbar.php'; ?>

	<!-- greeting hero -->
	<div class="greeting-hero">
		<div class="greeting-hero-inner">
			<div class="greet-left">
				<div class="greet-time"><?= $greet ?></div>
				<div class="greet-name">
					Xin chào, <?= htmlspecialchars($fullName) ?>!
					<?php if ($isPremium): ?>
						<span class="premium-badge"><i class="fas fa-crown"></i>
							<?= htmlspecialchars($premiumName ?? 'Premium') ?></span>
					<?php endif; ?>
				</div>
				<div class="greet-sub">Tiếp tục luyện thi để đạt mục tiêu<br>TOEIC của bạn. Bạn đang làm rất tốt!</div>
				<div class="greet-cta">
					<a href="tests.php" class="cta-btn cta-primary" style="text-decoration:none;">
						<i class="fas fa-play" style="font-size:11px;margin-right:6px;"></i>Làm bài ngay
					</a>
					<a href="profile.php" class="cta-btn"
						style="text-decoration:none; background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.2);">
						<i class="fas fa-cog" style="font-size:11px;margin-right:6px;"></i>Cài đặt
					</a>
				</div>
			</div>
			<div class="greet-right">
				<div class="greet-stats">
					<div class="gstat">
						<div class="gstat-val"><?= $totalTests ?></div>
						<div class="gstat-label">Bài đã làm</div>
					</div>
					<div class="gstat">
						<div class="gstat-val"><?= $maxScore ?: '-' ?></div>
						<div class="gstat-label">Điểm cao nhất</div>
					</div>
					<div class="gstat">
						<div class="gstat-val"><?= $avgScore ?: '-' ?></div>
						<div class="gstat-label">Điểm trung bình</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="page">

		<!-- stat cards -->
		<section class="stat-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px;">
			<div class="stat-card"
				style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; align-items:center; gap:20px;">
				<div
					style="width:46px; height:46px; border-radius:50%; border:1.5px solid rgba(2, 132, 199, 0.16); color:#0284c7; font-size:18px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
					<i class="fas fa-trophy"></i>
				</div>
				<div>
					<div style="font-size:13px; color:#64748b; font-weight:600; text-transform:uppercase;">Điểm cao nhất
					</div>
					<div id="max-score" style="font-size:28px; font-weight:700; color:#05102b;"><?= $maxScore ?: '0' ?>
					</div>
					<div style="font-size:12px; color:#94a3b8;">Tổng điểm tốt nhất</div>
				</div>
			</div>
			<div class="stat-card"
				style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; align-items:center; gap:20px;">
				<div
					style="width:46px; height:46px; border-radius:50%; border:1.5px solid rgba(29, 158, 117, 0.16); color:#1d9e75; font-size:18px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
					<i class="fas fa-file-circle-check"></i>
				</div>
				<div>
					<div style="font-size:13px; color:#64748b; font-weight:600; text-transform:uppercase;">Số bài đã làm
					</div>
					<div id="total-tests" style="font-size:28px; font-weight:700; color:#05102b;">
						<?= $totalTests ?: '0' ?></div>
					<div style="font-size:12px; color:#94a3b8;">Tổng số đề hoàn thành</div>
				</div>
			</div>
			<div class="stat-card"
				style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; align-items:center; gap:20px;">
				<div
					style="width:46px; height:46px; border-radius:50%; border:1.5px solid rgba(245, 158, 11, 0.2); color:#f59e0b; font-size:18px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
					<i class="fas fa-chart-line"></i>
				</div>
				<div>
					<div style="font-size:13px; color:#64748b; font-weight:600; text-transform:uppercase;">Điểm trung
						bình</div>
					<div id="avg-score" style="font-size:28px; font-weight:700; color:#05102b;"><?= $avgScore ?: '0' ?>
					</div>
					<div style="font-size:12px; color:#94a3b8;">Trên tất cả các bài thi</div>
				</div>
			</div>
		</section>

		<!-- chart + tips -->
		<section style="display:grid; grid-template-columns:1.5fr 1fr; gap:24px;">
			<div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
				<div style="margin-bottom:24px;">
					<h2 style="font-size:16px; font-weight:700; color:#05102b; margin:0 0 4px; display:flex; align-items:center; gap:8px;">
						<i class="fas fa-chart-line" style="color:#0284c7; font-size:15px;"></i>
						Tiến độ điểm số
					</h2>
					<p style="font-size:12px; color:#64748b; margin:0; padding-left:23px;">Dữ liệu các lần thi gần đây</p>
				</div>
				<div style="height:280px;"><canvas id="scoreChart"></canvas></div>
			</div>
			<div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
				<div
					style="font-size:15px; font-weight:700; color:#05102b; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
					<i class="fas fa-lightbulb" style="color:#f39c12;"></i> Gợi ý luyện tập
				</div>
				<div style="display:flex; flex-direction:column; gap:16px;">
					<div style="display:flex; gap:12px;">
						<span
							style="font-size:12px; font-weight:700; color:#1d9e75; background:#e1f5ee; padding:4px 8px; border-radius:6px; height:fit-content;">01</span>
						<p style="font-size:13px; color:#64748b; margin:0; line-height:1.5;">Làm lại các đề có điểm
							Reading thấp để cải thiện tốc độ đọc.</p>
					</div>
					<div style="display:flex; gap:12px;">
						<span
							style="font-size:12px; font-weight:700; color:#1d9e75; background:#e1f5ee; padding:4px 8px; border-radius:6px; height:fit-content;">02</span>
						<p style="font-size:13px; color:#64748b; margin:0; line-height:1.5;">Ôn lại Part 3 và Part 4 nếu
							điểm Listening chưa ổn định.</p>
					</div>
					<div style="display:flex; gap:12px;">
						<span
							style="font-size:12px; font-weight:700; color:#1d9e75; background:#e1f5ee; padding:4px 8px; border-radius:6px; height:fit-content;">03</span>
						<p style="font-size:13px; color:#64748b; margin:0; line-height:1.5;">Làm full test mỗi 2 tuần để
							theo dõi tiến độ tổng thể.</p>
					</div>
				</div>
			</div>
		</section>

		<!-- history table -->
		<section style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
			<div
				style="padding:24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
				<div>
					<h2 style="font-size:16px; font-weight:700; color:#05102b; margin:0 0 4px; display:flex; align-items:center; gap:8px;">
						<i class="fas fa-history" style="color:#0284c7; font-size:15px;"></i>
						Lịch sử làm bài
					</h2>
					<p style="font-size:12px; color:#64748b; margin:0; padding-left:23px;">Kết quả các bài thi gần đây</p>
				</div>
				<a href="attempts.php"
					style="font-size:13px; font-weight:600; color:#1d9e75; text-decoration:none; display:inline-flex; align-items:center; gap:2px;">Xem
					tất cả <i class='bx bx-chevron-right' style='font-size:16px;'></i></a>
			</div>
			<div style="overflow-x:auto;">
				<table style="width:100%; border-collapse:collapse; text-align:left;">
					<thead>
						<tr style="background:#fff; border-bottom:2px solid #f1f5f9;">
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Ngày thi</th>
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Đề thi</th>
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Listening</th>
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Reading</th>
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Tổng điểm</th>
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Thời gian</th>
							<th
								style="padding:16px 24px; font-size:12px; font-weight:600; color:#64748b; text-transform:uppercase;">
								Chi tiết</th>
						</tr>
					</thead>
					<tbody id="history-body">
						<tr>
							<td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Đang tải dữ liệu...
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

	</div>

	<?php include './components/footer.php'; ?>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>
		const USER_ID = <?= $userId ?>;
		const API_URL = `/api/dashboard/stats?user_id=${USER_ID}`;

		$(document).ready(async function () {
			try {
				const res = await fetch(API_URL);
				const json = await res.json();

				if (json.status === 'success' && json.data) {
					const ov = json.data.overview;
					if (ov) {
						if (ov.maxScore) $('#max-score').text(ov.maxScore);
						if (ov.totalTests) $('#total-tests').text(ov.totalTests);
						if (ov.avgScore) $('#avg-score').text(ov.avgScore);
					}

					if (json.data.chartData && json.data.chartData.length > 0) {
						const ctx = document.getElementById('scoreChart').getContext('2d');
						new Chart(ctx, {
							type: 'line',
							data: {
								labels: json.data.chartData.map(d => d.date),
								datasets: [
									{
										label: 'Tổng điểm',
										data: json.data.chartData.map(d => d.total_score),
										borderColor: '#1d9e75',
										backgroundColor: 'rgba(29,158,117,.04)',
										borderWidth: 2.5,
										tension: 0.35,
										fill: true,
										pointBackgroundColor: '#1d9e75',
										pointRadius: 4,
									},
									{
										label: 'Listening',
										data: json.data.chartData.map(d => d.listening_score || 0),
										borderColor: '#0284c7',
										backgroundColor: 'rgba(2,132,199,.04)',
										borderWidth: 2,
										tension: 0.35,
										fill: true,
										pointBackgroundColor: '#0284c7',
										pointRadius: 3,
									},
									{
										label: 'Reading',
										data: json.data.chartData.map(d => d.reading_score || 0),
										borderColor: '#f59e0b',
										backgroundColor: 'rgba(245,158,11,.04)',
										borderWidth: 2,
										tension: 0.35,
										fill: true,
										pointBackgroundColor: '#f59e0b',
										pointRadius: 3,
									}
								]
							},
							options: {
								responsive: true,
								maintainAspectRatio: false,
								scales: { y: { beginAtZero: true, max: 990 } },
								plugins: {
									tooltip: { mode: 'index', intersect: false },
									legend: {
										position: 'top',
										labels: {
											boxWidth: 5,
											boxHeight: 5,
											usePointStyle: true,
											pointStyle: 'circle',
											padding: 20,
											font: {
												size: 12,
												weight: '600'
											}
										}
									}
								}
							}
						});
					}

					const history = json.data.history || [];
					if (history.length === 0) {
						$('#history-body').html('<tr><td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Bạn chưa có lịch sử làm bài nào.</td></tr>');
					} else {
						let html = '';
						history.forEach(item => {
							const date = new Date(item.created_at).toLocaleDateString('vi-VN');
							const total = (item.listening_score || 0) + (item.reading_score || 0);
							html += `<tr style="border-bottom:1px solid #f1f5f9;">
								<td style="padding:16px 24px; font-size:14px;">${date}</td>
								<td style="padding:16px 24px; font-size:14px; font-weight:600;">${item.test_name || 'Đề ngẫu nhiên'}</td>
								<td style="padding:16px 24px; font-size:14px; color:#1d9e75;">${item.listening_score || 0}</td>
								<td style="padding:16px 24px; font-size:14px; color:#0284c7;">${item.reading_score || 0}</td>
								<td style="padding:16px 24px; font-size:14px; font-weight:700;">${total}</td>
								<td style="padding:16px 24px; font-size:14px;">${item.time_taken || 0} phút</td>
								<td style="padding:16px 24px;">
									<a href="results.php?attempt_id=${item.attempt_id}" style="font-size:13px; font-weight:600; color:#1d9e75; text-decoration:none; display:inline-flex; align-items:center; gap:2px;">Xem <i class='bx bx-chevron-right' style='font-size:16px;'></i></a>
								</td>
							</tr>`;
						});
						$('#history-body').html(html);
					}
				}
			} catch (e) {
				$('#history-body').html('<tr><td colspan="7" style="text-align:center; padding:40px; color:#e74c3c;">Không thể tải dữ liệu.</td></tr>');
			}
		});
	</script>
	
</body>
</html>