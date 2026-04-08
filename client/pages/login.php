<?php
session_start();
// XỬ LÝ NHẬP SAI TÀI KHOẢN HOẶC MẬT KHẨU
$errors = [
	'login' => $_SESSION['login_error'] ?? '',
	'register' => $_SESSION['register_error'] ?? '',
	'success' => $_SESSION['register_success'] ?? '',
];
$activeForm = $_SESSION['active_form'] ?? 'login';
session_unset(); //Session vẫn còn hoạt động nhưng bỏ hết các biến
function showError($error)
{
	return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}
function showSuccess($msg)
{
	return !empty($msg) ? "<p class='success-message' style='color: green; font-size: 0.8rem; margin-bottom: 10px;'>$msg</p>" : '';
}
function isActiveForm($formName, $activeForm)
{
	return $formName === $activeForm ? 'active' : '';
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<?php include './components/metadata.php'; ?>
	<!-- cái này nhằm mục đích để hiện chính xác trang hiện tại là login hay register -->
	<title>PrepHub - <?= $activeForm === 'login' ? 'Đăng nhập' : 'Đăng ký' ?></title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
		rel="stylesheet">
	<link rel="stylesheet" href="../styles/loginPageStyle.css">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Poppins', sans-serif;
		}
	</style>
</head>
<!--http://localhost:81/Web/PrepHub/IS207-UIT/client/page/login.php-->

<body>
	<div class="container">
		<div class="form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
			<form action="../../server/controllers/auth-controller.php" method="post">
				<h2>Đăng nhập</h2>
				<?= showSuccess($errors['success']); ?>

								<?= showError($errors['login']); ?>
				<input type="email" name="email" placeholder="Email" required>
				<div class="password-row">
					<input type="password" name="password" placeholder="Mật khẩu" required>
					<img src="../img/eye_close.png" class="eye" onclick="togglePassword(this)">
				</div>

				<button type="submit" name="login">Đăng nhập</button>
				<p>Chưa có tài khoản? <a href="#" onclick="showForm('register-form')">Đăng ký ngay</a></p>
			</form>
		</div>
		<div class="form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">
			<form action="../../server/controllers/auth-controller.php" method="post">
				<h2>Đăng ký</h2>
				<?= showError($errors['register']); ?>
				<div class="name-row">
					<input type="name" name="first_name" placeholder="Tên" required>
					<input type="name" name="last_name" placeholder="Họ" required>
				</div>
				<input type="email" name="email" placeholder="Email" required>
				<div class="password-row">
					<input type="password" name="password" placeholder="Mật khẩu" id="password" required>
					<img src="../img/eye_close.png" class="eye" onclick="togglePassword(this)">
				</div>
				<div class="password-row">
					<input type="password" name="reenter_password" placeholder="Nhập lại mật khẩu" id="rePassword"
						required>
					<img src="../img/eye_close.png" class="eye" onclick="togglePassword(this)">
				</div>
				<p id="checkPasswordError"></p>
				<button type="submit" name="register">Đăng ký</button>
				<p>Đã có tài khoản? <a href="#" onclick="showForm('login-form')">Đăng nhập</a></p>
			</form>
		</div>
	</div>
	<script src="../js/auth.js"></script>
</body>

</html>