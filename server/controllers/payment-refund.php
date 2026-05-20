<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
    echo json_encode(['success' => false, 'message' => 'Không có gói nào để hoàn tiền']);
    exit;
}

require_once __DIR__ . '/../db/config.php';
$userId = $_SESSION['user_id'] ?? null;

// ghi hoàn tiền vào lịch sử
$txId = $_POST['tx_id'] ?? null;
if ($txId && isset($_SESSION['payment_history'])) {
    foreach ($_SESSION['payment_history'] as &$tx) {
        if ($tx['id'] === $txId) {
            $tx['status'] = 'refunded';
            break;
        }
    }
    unset($tx);
}

// hủy premium
$_SESSION['is_premium']     = false;
$_SESSION['premium_plan']   = null;
$_SESSION['premium_name']   = null;
$_SESSION['premium_price']  = null;
$_SESSION['premium_until']  = null;
$_SESSION['last_payment']   = null;

if ($userId && isset($conn)) {
    try {
        $stmt = $conn->prepare("UPDATE users SET is_premium = 0, has_course = 0, premium_plan = NULL, premium_until = NULL WHERE id = :id");
        $stmt->execute(['id' => $userId]);

        if ($txId) {
            $stmtTx = $conn->prepare("UPDATE transaction_history SET status = 'refunded' WHERE tx_id = :tx_id AND user_id = :user_id");
            $stmtTx->execute(['tx_id' => $txId, 'user_id' => $userId]);
        }
    } catch (PDOException $e) {
        error_log("Refund save error: " . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Hoàn tiền và hủy gói thành công',
]);
