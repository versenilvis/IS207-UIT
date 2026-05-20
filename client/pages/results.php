<?php
session_start();
$navbarMode = 'light';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php include './components/metadata.php'; ?>
	<title>Kết quả bài thi | Prephub</title>
	<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../styles/results.css">
</head>

<body>
	<?php include './components/navbar.php'; ?>

	<div class="results-page">
		<div class="results-container">

			<!-- stat cards -->
			<div class="stat-row">
				<div class="stat-card total premium-gradient">
					<div class="stat-content">
						<span class="stat-label">Tổng điểm</span>
						<span class="stat-value" id="total-points">-</span>
					</div>
					<div class="stat-bg-icon"><i class="bx bxs-trophy"></i></div>
				</div>
				<div class="stat-card listening soft-green">
					<div class="stat-content">
						<span class="stat-label">Listening</span>
						<span class="stat-value" id="listening-points">-<span class="stat-sub">/495</span></span>
					</div>
					<div class="stat-bg-icon"><i class="bx bx-headphone"></i></div>
				</div>
				<div class="stat-card reading soft-blue">
					<div class="stat-content">
						<span class="stat-label">Reading</span>
						<span class="stat-value" id="reading-points">-<span class="stat-sub">/495</span></span>
					</div>
					<div class="stat-bg-icon"><i class="bx bxs-book-open"></i></div>
				</div>
				<div class="stat-card accuracy soft-orange">
					<div class="stat-content">
						<span class="stat-label">Độ chính xác</span>
						<span class="stat-value" id="accuracy-rate">-</span>
					</div>
					<div class="stat-bg-icon"><i class="bx bx-target-lock"></i></div>
				</div>
			</div>

			<!-- main split layout -->
			<div class="split-layout">

				<!-- left: scrollable review list -->
				<div class="split-left">
					<div class="content-card h-100-flex">
						<div class="content-card-header">
							<h6 class="content-card-title">Review chi tiết từng câu</h6>
							<div class="filter-toggle">
								<button class="ft-btn active" id="filter-all">Tất cả</button>
								<button class="ft-btn ft-wrong" id="filter-wrong">Câu sai</button>
							</div>
						</div>
						<div id="wrong-questions-list" class="review-scroll-area">
							<div class="p-5 text-center" style="color:#94a3b8;">
								<div class="spinner-border spinner-border-sm me-2" style="color:#05102b;"></div>
								Đang tải dữ liệu bài làm...
							</div>
						</div>
					</div>
				</div>

				<!-- right: fixed answer grid -->
				<div class="split-right">
					<div class="content-card h-100-flex">
						<div class="content-card-header d-flex flex-column align-items-stretch pb-2">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<h6 class="content-card-title mb-0">Bảng đáp án</h6>
								<span style="font-size:0.72rem;font-weight:600;color:#94a3b8;">1 - 200</span>
							</div>
							<div class="legend-row">
								<span class="legend-item"><span class="legend-dot correct"></span>Đúng</span>
								<span class="legend-item"><span class="legend-dot wrong"></span>Sai</span>
								<span class="legend-item"><span class="legend-dot unanswered"></span>Không chọn</span>
							</div>
						</div>
						<div class="sidebar-grid-area pt-2">
							<div id="answer-grid"></div>
						</div>
						<div class="sidebar-footer">
							<div class="row g-2">
								<div class="col-6">
									<a href="#" id="btn-retake" class="btn-retake">Làm lại</a>
								</div>
								<div class="col-6">
									<a href="user.php" class="btn-home">Về hồ sơ</a>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="../js/results.js"></script>
</body>

</html>