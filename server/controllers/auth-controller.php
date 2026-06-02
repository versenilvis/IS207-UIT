<?php
// xử lí logic api cho user: login, logout, register, ...
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';

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
        authResponse(false, "Vui lòng nhập đầy đủ thông tin", "/client/pages/home.php", "register_error");
        return;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        authResponse(false, "Email không hợp lệ. Vui lòng nhập đúng định dạng email.", "/client/pages/home.php", "register_error");
        return;
    }

    if (strlen($data["password"]) < 8) {
        authResponse(false, "Mật khẩu phải có ít nhất 8 ký tự.", "/client/pages/home.php", "register_error");
        return;
    }

    if (!preg_match("/[a-z]/i", $data["password"])) {
        authResponse(false, "Mật khẩu phải có ít nhất 1 chữ cái.", "/client/pages/home.php", "register_error");
        return;
    }

    if (!preg_match("/[0-9]/", $data["password"])) {
        authResponse(false, "Mật khẩu phải có ít nhất 1 số.", "/client/pages/home.php", "register_error");
        return;
    }

    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    //Để email verify thì ng dùng mới được đky
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    try {
        // Xóa tài khoản chưa kích hoạt quá 15 phút của chính email đang đăng ký lại
        $deleteExpired = $conn->prepare(" DELETE FROM users
                                        WHERE email = :email
                                        AND account_activation_hash IS NOT NULL
                                        AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $deleteExpired->execute([
            'email' => $data['email']
        ]);

        // kiểm tra email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);

        if ($stmt->fetch()) {
            authResponse(false, "Email này đã được đăng ký!", "/client/pages/home.php", "register_error");
        } else {
            $insert = $conn->prepare("INSERT INTO users (uuid, first_name, last_name, email, password, account_activation_hash) 
                                        VALUES (:uuid, :first_name, :last_name, :email, :password, :account_activation_hash)");
            $inserted = $insert->execute([
                'uuid'       => $uuid,
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => $password_hash,
                'account_activation_hash' => $token_hash
            ]);

            if ($inserted){
                $mail = require_once __DIR__ . '/../services/mailer.php';
                $mail->addAddress($data['email']);
                $mail->Subject = "Xác thực tài khoản email Prephub";
                
                $verificationLink = 'http://localhost:3000/client/pages/verify.php?token=' . urlencode($token);
                $verificationLink = htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8');
                ob_start();
                //CSS html của email được gửi cho người dùng
                require __DIR__ . '/../../client/pages/components/verification-mail.php';
                $mail->Body = ob_get_clean();
                //Gửi mail. Chúng ta sẽ chặn người dùng không đăng nhập nếu token khác NULL.
                try{
                    $mail->send();
                }catch (Exception $exception){
                    error_log("Register verification email failed: {$mail->ErrorInfo}");
                    $cleanup = $conn->prepare("DELETE FROM users WHERE account_activation_hash = :account_activation_hash");
                    $cleanup->execute([
                        'account_activation_hash' => $token_hash
                    ]);
                    sendError("Không gửi được email xác thực. Vui lòng thử lại sau.", 500);
                }
            }
            $_SESSION['active_form'] = 'login';
            authResponse(true, "Đăng ký thành công!", "/client/pages/home.php");
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
        
        // Thêm phần kiểm tra xem account token có NULL hay không. Nếu null mới được đăng nhập.
        if ($user && password_verify($data['password'], $user['password']) && $user['account_activation_hash'] === null) {
            // kiểm tra tài khoản bị khóa
            if (isset($user['is_banned']) && (int)$user['is_banned'] === 1) {
                authResponse(false, "Tài khoản của bạn đã bị đình chỉ hoạt động do vi phạm điều khoản dịch vụ. Nếu bạn cho rằng đây là một sự cố hoặc sai sót, vui lòng báo cáo tới support@prephub.com để được hỗ trợ", "/client/pages/home.php", "login_error");
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['avatar'] = $user['avatar'] ?? null;

            
            $_SESSION['is_premium'] = !empty($user['is_premium']);
            $_SESSION['has_course'] = !empty($user['has_course']);
            if (!empty($user['premium_plan'])) {
                $_SESSION['premium_plan'] = $user['premium_plan'];
                $_SESSION['premium_until'] = $user['premium_until'];
                $plans = require __DIR__ . '/../config/premiumPlan.php';
                if (isset($plans[$user['premium_plan']])) {
                    $_SESSION['premium_name'] = $plans[$user['premium_plan']]['name'];
                    $_SESSION['premium_period'] = $plans[$user['premium_plan']]['period'];
                }
            }
            
            $_SESSION['payment_history'] = [];
            $txStmt = $conn->prepare("SELECT tx_id as id, plan_id, plan_name, price, period, status, created_at FROM transaction_history WHERE user_id = :user_id ORDER BY created_at ASC");
            $txStmt->execute(['user_id' => $user['id']]);
            $history = $txStmt->fetchAll();
            if ($history) {
                $_SESSION['payment_history'] = $history;
                $_SESSION['last_payment'] = end($_SESSION['payment_history']);
            }
            
            authResponse(true, "Đăng nhập thành công", "/client/pages/home.php");
        } else {
            authResponse(false, "Email hoặc mật khẩu không chính xác", "/client/pages/home.php", "login_error");
        }
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

//Xử lý reset password
//Note cho phần security: Luôn luôn hiện "Hãy kiểm tra email của bạn" sau khi gửi cho dù email có tồn tại trong db hay không.
//Tránh việc người dùng dò ra email nào hợp lệ trong db
function handleReset(){
    global $conn;
    $data = getAuthInput();
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    //token trong 15' thôi
    $expir = date("Y-m-d H:i:s", time()+ 60 * 15); 

    
    $resetLink = 'http://localhost:3000/client/pages/reset.php?token=' . urlencode($token);
    //Tránh việc điều chỉnh token thẳng trên URL
    $resetLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
    
    try{
        $sql = "UPDATE users
                SET reset_token_hash = :reset_token_hash,
                    reset_token_expires_at = :reset_token_expires_at
                WHERE email = :email";

        $stmt = $conn->prepare($sql);
        
        $stmt->execute([
            'reset_token_hash' => $token_hash,
            'reset_token_expires_at' => $expir,
            'email' => $data['email']
        ]);
        
        //Phần gửi mail. 
        if ($stmt->rowCount() > 0){
            $mail = require_once __DIR__ . '/../services/mailer.php';
            $mail->addAddress($data['email']);
            $mail->Subject = "Đặt lại mật khẩu Prephub";

            //Phần css/html của email gửi cho người dùng.
            ob_start();
            require __DIR__ . '/../../client/pages/components/reset-mail.php';
            $mail->Body = ob_get_clean();

            try{
                $mail->send();
            }catch (Exception $exception){
                error_log("Forgot password email failed: {$mail->ErrorInfo}");
                sendError("Không gửi được email đặt lại mật khẩu. Vui lòng thử lại sau.", 500);
            }
            
        }
        sendJson([
            "success" => true,
            "message" => "Nếu email tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi."
        ]);
    }catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

//Login w gg
function handleGoogleLogin(){
    $googleConfig = require __DIR__ . '/../config/google.php';

    $client = new Google\Client;
    $client->setClientId($googleConfig['client_id']);
    $client->setClientSecret($googleConfig['client_secret']);
    $client->setRedirectUri($googleConfig['redirect_uri']);

    $client->addScope("openid");
    $client->addScope("email");
    $client->addScope("profile");

    $url = $client->createAuthUrl();
    header("Location: " . $url);
    exit();
}

// Xử lý đăng xuất
// Hàm xử lý đăng xuất t dời r. StackOverflow bảo là làm nó thành file riêng để tránh việc thực thi nhầm (accidentally executed)
