<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../styles/navBar.css">
</head>

<body>
	<nav class="ph-nav">
		<a class="ph-brand" href="#">
			<i class="fas fa-graduation-cap"></i>
			<!--LOGO-->
			PREPHUB
		</a>
		<ul class="ph-nav-links">
			<!--Nav link-->
			<li><a href="user.php">Trang chủ</a></li>
			<li><a href="tests.php">Danh sách đề thi</a></li>
			<li><a href="dashboard.php">Dashboard</a></li>
			<div class="ph-divider"></div>
			<li><a href="premium.php" class="ph-premium"><span class="ph-premium-dot"></span>Premium</a></li>
		</ul>
		<div class="ph-avatar-wrap" id="avatarWrap">
			<div class="ph-avatar" id="avatarBtn">TU</div>
			<div class="ph-dropdown" id="dropdown">
				<div class="ph-dropdown-header">
					<p>Test User</p>
					<span>user@email.com</span>
				</div>
				<!--Drop down menu-->
				<a href="#"><i class="fas fa-user"></i> Hồ sơ</a>
				<a href="#"><i class="fas fa-bell"></i> Thông báo</a>
				<a href="#"><i class="fas fa-keyboard"></i> Phím tắt</a>
				<a href="#"><i class="fas fa-gift"></i> Có gì mới</a>
				<a href="#"><i class="fas fa-circle-question"></i> Hỗ trợ</a>
				<a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
			</div>
		</div>
	</nav>
    <script src="../js/navBar.js"></script>
</body>

</html>