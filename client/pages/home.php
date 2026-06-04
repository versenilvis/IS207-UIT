<?php
session_start();

// chỉ lấy flash errors, KHÔNG session_unset() vì sẽ xóa mất login state
$errors = [
	'login'    => $_SESSION['login_error'] ?? '',
	'register' => $_SESSION['register_error'] ?? '',
	'success'  => $_SESSION['register_success'] ?? '',
];
$activeAuthForm = $_SESSION['active_form'] ?? 'login';
$resetToken = $_GET['token'] ?? $_GET['reset_token'] ?? '';

// xóa chỉ các flash keys, giữ nguyên user session
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['active_form']);

?>
<!doctype html>
<html lang="vi">

<head>
	<?php include './components/metadata.php'; ?>
	<title>Prephub - Luyện thi TOEIC online</title>
	<link rel="stylesheet" href="../styles/pricing.css">
	<link rel="stylesheet" href="../styles/homepage/feedback.css?v=2">
</head>

<body>

	<?php $navbarMode = 'dark';
	include './components/navbar.php'; ?>

	<?php include './components/homepage/hero.php'; ?>

	<?php include './components/homepage/about.php'; ?>

	<?php include './components/homepage/tests.php'; ?>

	<?php include './components/homepage/pricing.php'; ?>

	<?php include './components/homepage/feedback.php'; ?>

	<?php include './components/homepage/banner.php'; ?>

	<?php include './components/footer.php'; ?>

	<script>
		document.addEventListener('DOMContentLoaded', () => {
			const toggleContainer = document.getElementById('priceToggle');
			const monthlyBtn = document.getElementById('monthlyBtn');
			const yearlyBtn = document.getElementById('yearlyBtn');

			if (toggleContainer && monthlyBtn && yearlyBtn) {
				const priceAmounts = document.querySelectorAll('#pricing .price-amount');
				const pricePeriods = document.querySelectorAll('#pricing .price-period');
				const priceOlds = document.querySelectorAll('#pricing .price-old');
				const priceSubtexts = document.querySelectorAll('#pricing .price-subtext');
				const monthlyActions = document.querySelectorAll('#pricing .monthly-action');
				const yearlyActions = document.querySelectorAll('#pricing .yearly-action');

				let isYearly = false;

				function updatePrices() {
					priceAmounts.forEach((el) => el.style.opacity = '0');
					pricePeriods.forEach((el) => el.style.opacity = '0');
					priceOlds.forEach((el) => el.style.opacity = '0');
					priceSubtexts.forEach((el) => el.style.opacity = '0');

					setTimeout(() => {
						priceAmounts.forEach((el) => {
							el.textContent = isYearly ? el.getAttribute('data-yearly') : el.getAttribute('data-monthly');
							el.style.opacity = '1';
						});
						pricePeriods.forEach((el) => {
							el.textContent = isYearly ? el.getAttribute('data-yearly-suffix') : el.getAttribute('data-monthly-suffix');
							el.style.opacity = '1';
						});
						priceOlds.forEach((el) => {
							if (el.hasAttribute('data-yearly')) {
								el.textContent = isYearly ? el.getAttribute('data-yearly') : el.getAttribute('data-monthly');
								el.style.opacity = '1';
							}
						});
						priceSubtexts.forEach((el) => {
							el.textContent = isYearly ? el.getAttribute('data-yearly') : el.getAttribute('data-monthly');
							el.style.opacity = '1';
						});

						if (isYearly) {
							monthlyActions.forEach(el => el.style.display = 'none');
							yearlyActions.forEach(el => el.style.display = 'block');
						} else {
							monthlyActions.forEach(el => el.style.display = 'block');
							yearlyActions.forEach(el => el.style.display = 'none');
						}
					}, 150);
				}

				toggleContainer.addEventListener('click', () => {
					isYearly = !isYearly;
					if (isYearly) {
						toggleContainer.classList.add('yearly');
						yearlyBtn.classList.add('active');
						monthlyBtn.classList.remove('active');
					} else {
						toggleContainer.classList.remove('yearly');
						monthlyBtn.classList.add('active');
						yearlyBtn.classList.remove('active');
					}
					updatePrices();
				});

				monthlyBtn.addEventListener('click', () => {
					if (isYearly) {
						isYearly = false;
						toggleContainer.classList.remove('yearly');
						monthlyBtn.classList.add('active');
						yearlyBtn.classList.remove('active');
						updatePrices();
					}
				});

				yearlyBtn.addEventListener('click', () => {
					if (!isYearly) {
						isYearly = true;
						toggleContainer.classList.add('yearly');
						yearlyBtn.classList.add('active');
						monthlyBtn.classList.remove('active');
						updatePrices();
					}
				});
			}
		});
	</script>
	<script>
		window.isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
	</script>
</body>

</html>
