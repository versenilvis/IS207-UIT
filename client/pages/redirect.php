<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/server/db/config.php';

Firebase\JWT\JWT::$leeway = 60;

$googleConfig = require dirname(__DIR__, 2) . '/server/config/google.php';

function redirectGoogleLoginFailed($message) {
    $_SESSION['login_error'] = $message;
    $_SESSION['active_form'] = 'login';
    header('Location: /client/pages/home.php');
    exit();
}

function createUuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

if (empty($_GET['code'])) {
    redirectGoogleLoginFailed('Google login failed.');
}

$client = new Google\Client();
$client->setClientId($googleConfig['client_id']);
$client->setClientSecret($googleConfig['client_secret']);
$client->setRedirectUri($googleConfig['redirect_uri']);

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    redirectGoogleLoginFailed($token['error_description'] ?? $token['error']);
}

if (empty($token['id_token'])) {
    redirectGoogleLoginFailed('Google did not return an ID token.');
}

$payload = $client->verifyIdToken($token['id_token']);

if (!$payload) {
    redirectGoogleLoginFailed('Cannot verify Google account.');
}

if (empty($payload['email']) || empty($payload['sub'])) {
    redirectGoogleLoginFailed('Google account is missing required profile data.');
}

if (isset($payload['email_verified']) && !$payload['email_verified']) {
    redirectGoogleLoginFailed('Google email is not verified.');
}

$googleId = $payload['sub'];
$email = $payload['email'];
$firstName = $payload['given_name'] ?? '';
$lastName = $payload['family_name'] ?? '';
$avatar = $payload['picture'] ?? null;

if ($firstName === '' && $lastName === '') {
    $firstName = $payload['name'] ?? 'Google';
    $lastName = 'User';
}

try {
    $conn->beginTransaction();

    // liên kết tài khoản google với tài khoản đang đăng nhập
    if (isset($_SESSION['user_id'])) {
        $currentUserId = $_SESSION['user_id'];
        
        $checkGoogle = $conn->prepare('SELECT user_id FROM oauth_accounts WHERE google_id = :google_id LIMIT 1');
        $checkGoogle->execute(['google_id' => $googleId]);
        $existingLink = $checkGoogle->fetch();
        
        if ($existingLink && $existingLink['user_id'] != $currentUserId) {
            $conn->rollBack();
            $_SESSION['changeNameResult'] = 'Tài khoản Google này đã liên kết với tài khoản khác';
            header('Location: /client/pages/profile.php');
            exit();
        }
        
        $linkOauth = $conn->prepare(
            'INSERT IGNORE INTO oauth_accounts (user_id, google_id)
             VALUES (:user_id, :google_id)'
        );
        $linkOauth->execute([
            'user_id' => $currentUserId,
            'google_id' => $googleId
        ]);
        
        $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $currentUserId]);
        $user = $stmt->fetch();
        
        if (empty($user['avatar']) && $avatar) {
            $updateAvatar = $conn->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
            $updateAvatar->execute([
                'avatar' => $avatar,
                'id' => $currentUserId
            ]);
            $user['avatar'] = $avatar;
        }
        
        $conn->commit();
        
        $_SESSION['avatar'] = $user['avatar'] ?? null;
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        
        $_SESSION['changeNameResult'] = 'Liên kết tài khoản Google thành công';
        header('Location: /client/pages/profile.php');
        exit();
    }

    $stmt = $conn->prepare(
        'SELECT u.*
         FROM users u
         INNER JOIN oauth_accounts oa ON oa.user_id = u.id
         WHERE oa.google_id = :google_id
         LIMIT 1'
    );
    $stmt->execute(['google_id' => $googleId]);
    $user = $stmt->fetch();

    if (!$user) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
    }

    if (!$user) {
        $insertUser = $conn->prepare(
            'INSERT INTO users (uuid, first_name, last_name, email, password, avatar, account_activation_hash)
             VALUES (:uuid, :first_name, :last_name, :email, NULL, :avatar, NULL)'
        );
        $insertUser->execute([
            'uuid' => createUuid(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'avatar' => $avatar
        ]);

        $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $conn->lastInsertId()]);
        $user = $stmt->fetch();
    } elseif (empty($user['avatar']) && $avatar) {
        $updateAvatar = $conn->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
        $updateAvatar->execute([
            'avatar' => $avatar,
            'id' => $user['id']
        ]);
        $user['avatar'] = $avatar;
    }

    $linkOauth = $conn->prepare(
        'INSERT IGNORE INTO oauth_accounts (user_id, google_id)
         VALUES (:user_id, :google_id)'
    );
    $linkOauth->execute([
        'user_id' => $user['id'],
        'google_id' => $googleId
    ]);

    $conn->commit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    redirectGoogleLoginFailed('Cannot login with Google right now.');
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name'] = $user['last_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['is_premium'] = !empty($user['is_premium']);
$_SESSION['has_course'] = !empty($user['has_course']);
$_SESSION['avatar'] = $user['avatar'] ?? null;

if (!empty($user['premium_plan'])) {
    $_SESSION['premium_plan'] = $user['premium_plan'];
    $_SESSION['premium_until'] = $user['premium_until'];
    $plans = require dirname(__DIR__, 2) . '/server/config/premiumPlan.php';
    if (isset($plans[$user['premium_plan']])) {
        $_SESSION['premium_name'] = $plans[$user['premium_plan']]['name'];
        $_SESSION['premium_period'] = $plans[$user['premium_plan']]['period'];
    }
}

$_SESSION['payment_history'] = [];
$txStmt = $conn->prepare(
    'SELECT tx_id as id, plan_id, plan_name, price, period, status, created_at
     FROM transaction_history
     WHERE user_id = :user_id
     ORDER BY created_at ASC'
);
$txStmt->execute(['user_id' => $user['id']]);
$history = $txStmt->fetchAll();
if ($history) {
    $_SESSION['payment_history'] = $history;
    $_SESSION['last_payment'] = end($_SESSION['payment_history']);
}

header('Location: /client/pages/home.php');
exit();
