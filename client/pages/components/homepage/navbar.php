<!-- Navbar -->
<nav class="navbar navbar-expand-lg" id="mainNavbar">
	<div class="container-fluid position-relative">
		<!-- logo: locked left -->
		<a class="navbar-brand" href="home.php">
			<svg viewBox="0 0 2048 2048" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M 986.325 167.538 C 1008.83 166.293 1044.6 166.228 1067.01 167.658 C 1248.97 180.828 1418.33 265.449 1538.11 403.048 C 1633.56 509.36 1693.16 642.997 1708.48 785.049 C 1711.8 815.873 1713.82 855.392 1711.77 886.251 C 1692.28 1204.19 1457.23 1467.26 1143.47 1522.27 C 1066.75 1536.29 992.994 1531.15 915.685 1532.09 C 910.292 1532.15 895.396 1530.62 891.876 1533.72 C 887.797 1544.15 890.037 1682.94 890.025 1706.5 L 890.111 1781 C 890.13 1796.64 890.931 1812.7 888.529 1828.2 C 884.017 1857.32 858.076 1880.47 828.464 1881.07 C 802.066 1881.61 775.022 1881.21 748.404 1881.23 L 577.683 1881.02 L 462.033 1881.04 C 439.179 1881.05 416.002 1881.95 393.297 1879.54 C 363.945 1876.42 340.386 1848.82 339.748 1819.64 C 339.363 1802.06 339.636 1784.35 339.647 1766.72 L 339.685 1664.76 L 339.682 1351.33 L 339.721 1025.24 L 339.7 893.228 C 339.687 872.069 338.752 843.226 339.95 822.761 C 342.441 780.173 348.701 737.889 358.658 696.407 C 385.91 585.498 440.471 483.167 517.372 398.73 C 636.284 266.862 807.736 176.883 986.325 167.538 z" />
			</svg>
			<div class="logo-container">
				<span class="brand-text">Prephub</span>
				<span class="brand-subtext">Luyện thi TOEIC online</span>
			</div>
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
			aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<!-- links: centered -->
			<ul class="navbar-nav nav-center">
				<li class="nav-item"><a class="nav-link active" href="home.php">Trang chủ</a></li>
				<li class="nav-item"><a class="nav-link" href="#tests">Đề thi</a></li>
				<li class="nav-item"><a class="nav-link" href="#about">Giới thiệu</a></li>
				<li class="nav-item"><a class="nav-link" href="#pricing">Nâng cấp</a></li>
				<div class="nav-indicator" id="navIndicator"></div>
			</ul>
			<!-- login: locked right -->
			<ul class="navbar-nav nav-right">
				<li class="nav-item">
					<a href="javascript:void(0)" class="btn-login" id="loginBtn" data-bs-toggle="modal"
						data-bs-target="#loginModal">Đăng nhập</a>
					<img src="https://ui-avatars.com/api/?name=User&background=05102b&color=fff" alt="Avatar"
						class="user-avatar d-none" id="userAvatar">
				</li>
			</ul>
		</div>
	</div>
</nav>
