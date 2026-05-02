<?php
// xử lí logic api cho user: login, logout, register, ...
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// cái này để auto complete cho code editor thôi
// tức là cho editor biết $conn có kiểu PDO để gợi ý method nhanh hơn
/**
 * @var PDO $conn
 */

// Helper để lấy dữ liệu đầu vào (hỗ trợ cả JSON và $_POST)
function getAuthInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    return [
        'first_name' => trim($_POST['first_name'] ?? $input['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name']  ?? $input['last_name']  ?? ''),
        'email'      => trim($_POST['email']      ?? $input['email']      ?? ''),
        'password'   => $_POST['password']        ?? $input['password']   ?? ''
    ];
}

// Helper để trả về kết quả (JSON cho API, Redirect cho Form)
function authResponse($success, $message, $redirectPath, $errorSessionKey = null) {
    // Kiểm tra xem có phải yêu cầu từ API/AJAX không
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

    if ($isAjax || $isJson) {
        if ($success) {
            sendJson(["success" => true, "message" => $message]);
        } else {
            sendError($message, 401);
        }
    } else {
        // Nếu là Form truyền thống thì dùng Redirect
        if (!$success && $errorSessionKey) {
            $_SESSION[$errorSessionKey] = $message;
        }
        header("Location: " . $redirectPath);
        exit();
    }
}

// Xử lý register
function handleRegister() {
    global $conn;
    $data = getAuthInput();
    
    if (empty($data['email']) || empty($data['password'])) {
        authResponse(false, "Vui lòng nhập đầy đủ thông tin", "/client/pages/login.php", "register_error");
    }

    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
		// Kiểm tra xem email có tồn tại hay chưa
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

        if ($stmt->fetch()) {
            authResponse(false, "Email này đã được đăng ký!", "/client/pages/login.php", "register_error");
        } else {
            $insert = $conn->prepare("INSERT INTO users (uuid, first_name, last_name, email, password) VALUES (:uuid, :first_name, :last_name, :email, :password)");
            $insert->execute([
                'uuid' => $uuid,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $password_hash
            ]);
            $_SESSION['active_form'] = 'login';
            authResponse(true, "Đăng ký thành công!", "/client/pages/login.php");
        }
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// Xử lý login
function handleLogin() {
    global $conn;
    $data = getAuthInput();

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($data['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            authResponse(true, "Đăng nhập thành công", "/client/pages/user.php");
        } else {
            authResponse(false, "Email hoặc mật khẩu không chính xác", "/client/pages/login.php", "login_error");
        }
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// Xử lý đăng xuất
function handleLogout() {
    session_unset();
    session_destroy();
    authResponse(true, "Đã đăng xuất", "/client/pages/home.php");
}