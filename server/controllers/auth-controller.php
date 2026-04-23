<?php
// xử lí logic api cho user: login, logout, register, ...
session_start();

// cái này để auto complete cho code editor thôi
// tức là cho editor biết $conn có kiểu PDO để gợi ý method nhanh hơn
/**
 * @var PDO $conn
 */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Xử lý register
function handleRegister()
{
	global $conn;

	$first_name = $_POST['first_name'] ?? '';
	$last_name = $_POST['last_name'] ?? '';
	$email = $_POST['email'] ?? '';
	$password_plain = $_POST['password'] ?? '';

	$uuid = uniqid();
	$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

	try {
		// Kiểm tra xem email có tồn tại hay chưa
		// NOTE: đọc post dưới đây để hiểu thêm về prepare statements
		// https://thuedoan.vn/su-dung-prepared-statements-trong-pdo-de-chong-sql-injection.html
		$stmt = $conn->prepare("SELECT email FROM users WHERE email = :email");
		$stmt->execute(['email' => $email]);

		if ($stmt->fetch()) {
			$_SESSION['register_error'] = 'Email này đã được đăng ký!';
			$_SESSION['active_form'] = 'register';
		} else {
			$insert = $conn->prepare("INSERT INTO users (uuid, first_name, last_name, email, password) VALUES (:uuid, :first_name, :last_name, :email, :password)");
			$insert->execute([
				'uuid' => $uuid,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'email' => $email,
				'password' => $password_hash
			]);
			$_SESSION['register_success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
			$_SESSION['active_form'] = 'login';
		}
	} catch (PDOException $e) {
		die("Error: " . $e->getMessage());
	}

	header("Location: /client/pages/login.php");
	exit();
}

// Xử lý login
function handleLogin()
{
	global $conn;

	$email = $_POST['email'] ?? '';
	$password_plain = $_POST['password'] ?? '';

	try {
		$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->execute(['email' => $email]);
		$user = $stmt->fetch();

		// Nếu nhập đúng mật khẩu thì chuyển tới trang user.php
		if ($user && password_verify($password_plain, $user['password'])) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['role'] = $user['role'];
			$_SESSION['first_name'] = $user['first_name'];
			$_SESSION['last_name'] = $user['last_name'];
			$_SESSION['email'] = $user['email'];
			header("Location: /client/pages/user.php");
			exit();
		}
	} catch (PDOException $e) {
		die("Error: " . $e->getMessage());
	}

	$_SESSION['login_error'] = 'Email hoặc mật khẩu không chính xác';
	$_SESSION['active_form'] = 'login';
	header("Location: /client/pages/login.php");
	exit();
}

// Xử lý đăng xuất
function handleLogout()
{
	session_unset();
	session_destroy();
	header("Location: /client/pages/home.php");
	exit();
}