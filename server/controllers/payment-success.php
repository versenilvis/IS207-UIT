<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once __DIR__ . '/../db/config.php';
$plans = require __DIR__ . '/../config/premiumPlan.php';

$planId = $_POST['plan_id'] ?? 'pro';
$plan   = $plans[$planId] ?? $plans['pro'];
$paidAmount = isset($_POST['amount']) ? (int)$_POST['amount'] : $plan['price'];

$newTier    = $plan['tier'];
$newDays    = $plan['days'];
$repurchase = $plan['repurchasable'] ?? false;

$userTier    = 0;
$currentPlan = 'free';
if (!empty($_SESSION['is_premium'])) {
    $currentPlan = $_SESSION['premium_plan'] ?? 'free';
    $userTier    = $plans[$currentPlan]['tier'] ?? 0;
}

// allow: higher tier, same-tier yearly upgrade, or repurchasable plan
$isYearlyUpgrade = ($newTier === $userTier)
    && isset($plans[$currentPlan])
    && ($plans[$currentPlan]['days'] ?? 0) < $newDays;

if (!$repurchase && $planId === $currentPlan) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã sở hữu gói này']);
    exit;
}

if (!isset($_SESSION['is_premium']) || ($plan['is_premium'] ?? true)) {
    $_SESSION['is_premium'] = $plan['is_premium'] ?? true;
}
if (!isset($_SESSION['has_course']) || ($plan['has_course'] ?? false)) {
    $_SESSION['has_course'] = $plan['has_course'] ?? false;
}

// Allow updating plan if higher tier OR longer duration (e.g. ultra to pro_year)
$currentDays = $plans[$currentPlan]['days'] ?? 0;
if ($newTier >= $userTier || $newDays > $currentDays) {
    $_SESSION['premium_plan']   = $planId;
    $_SESSION['premium_name']   = $plan['name'];
    $_SESSION['premium_price']  = $paidAmount;
    $_SESSION['premium_period'] = $plan['period'];
    $_SESSION['premium_until']  = date('Y-m-d H:i:s', strtotime("+{$newDays} days"));
}

// ── payment history ──
if (!isset($_SESSION['payment_history'])) {
    $_SESSION['payment_history'] = [];
}

$txId = 'PH' . strtoupper(substr(md5(uniqid()), 0, 8));
$_SESSION['payment_history'][] = [
    'id'        => $txId,
    'plan_id'   => $planId,
    'plan_name' => $plan['name'],
    'price'     => $paidAmount,
    'period'    => $plan['period'],
    'status'    => 'success',
    'created_at'=> date('Y-m-d H:i:s'),
];

$_SESSION['last_payment'] = end($_SESSION['payment_history']);

$userId = $_SESSION['user_id'] ?? null;
if ($userId && isset($conn)) {
    try {
        $stmt = $conn->prepare("UPDATE users SET is_premium = :is_premium, has_course = :has_course, premium_plan = :premium_plan, premium_until = :premium_until WHERE id = :id");
        $stmt->execute([
            'is_premium'    => !empty($_SESSION['is_premium']) ? 1 : 0,
            'has_course'    => !empty($_SESSION['has_course']) ? 1 : 0,
            'premium_plan'  => $_SESSION['premium_plan'] ?? null,
            'premium_until' => $_SESSION['premium_until'] ?? null,
            'id'            => $userId
        ]);

        $stmtTx = $conn->prepare("INSERT INTO transaction_history (tx_id, user_id, plan_id, plan_name, price, period, status) VALUES (:tx_id, :user_id, :plan_id, :plan_name, :price, :period, 'success')");
        $stmtTx->execute([
            'tx_id'     => $txId,
            'user_id'   => $userId,
            'plan_id'   => $planId,
            'plan_name' => $plan['name'],
            'price'     => $paidAmount,
            'period'    => $plan['period']
        ]);
    } catch (PDOException $e) {
        error_log("Payment save error: " . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Nâng cấp tài khoản thành công',
    'tx_id'   => $txId,
]);
