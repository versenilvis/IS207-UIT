<?php

/**
 * @var PDO $conn
 */
global $conn;
require_once __DIR__ . '/../../server/db/config.php';
require_once __DIR__ . '/../../server/utils/response.php';

$token = $_POST['token'] ?? '';
$token_hash = $token !== '' ? hash("sha256", $token) : '';
$errorMessage = '';
$isTokenValid = false;

if ($token === '') {
    $errorMessage = "Liên kết đặt lại mật khẩu bị thiếu token.";
} else {
    $sql = "SELECT * FROM users
            WHERE reset_token_hash = :reset_token_hash";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        'reset_token_hash' => $token_hash,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        $errorMessage = "Liên kết đặt lại mật khẩu không hợp lệ.";
    } elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
        $errorMessage = "Liên kết đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu liên kết mới.";
    } else {
        $isTokenValid = true;
    }
}

if (strlen($_POST["password"]) < 8) {
    die("Mật khẩu phải dài hơn 8 ký tự.");
}

if ( ! preg_match("/[a-z]/i", $_POST["password"])) {
    die("Mật khẩu phải có ít nhất 1 chữ cái.");
}

if ( ! preg_match("/[0-9]/", $_POST["password"])) {
    die("Mật khẩu phải trùng nhau");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Pass");
}

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$sql = "UPDATE users
        SET password = :password_hash,
            reset_token_hash = NULL,
            reset_token_expires_at = NULL,
            account_activation_hash = NULL
        WHERE id = :id";

$stmt = $conn->prepare($sql);

$stmt->execute([
    'password_hash' => $password_hash,
    'id' => $user['id']
]);

header("Location: /client/pages/reset.php?success=1");
exit();
