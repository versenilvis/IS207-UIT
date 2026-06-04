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

// ghi hoàn tiền vào lịch sử và cập nhật database
$refundAmount = 0;
$refundWindow = 7 * 86400; // 7 ngày

if ($userId && isset($conn)) {
	try {
		$plans = require __DIR__ . '/../config/premiumPlan.php';
		$ultraPrice = $plans['ultra']['price'] ?? 289000;
		$ultraYearPrice = $plans['ultra_year']['price'] ?? 749000;

		// 1. Lấy tất cả các giao dịch 'success' của user
		$stmtTxList = $conn->prepare("SELECT * FROM transaction_history WHERE user_id = :user_id AND status = 'success'");
		$stmtTxList->execute(['user_id' => $userId]);
		$activeTxList = $stmtTxList->fetchAll(PDO::FETCH_ASSOC);

		// 2. Tính tổng số tiền hoàn và cập nhật trạng thái các giao dịch đủ điều kiện
		$totalPaid = 0;
		$deductCourse = false;
		foreach ($activeTxList as $tx) {
			$txAge = time() - strtotime($tx['created_at']);
			if ($txAge <= $refundWindow) {
				$totalPaid += $tx['price'];
				if (in_array($tx['plan_id'] ?? '', ['ultra', 'ultra_year'])) {
					if ($tx['plan_id'] === 'ultra' && $tx['price'] >= $ultraPrice) {
						$deductCourse = true;
					} elseif ($tx['plan_id'] === 'ultra_year' && $tx['price'] >= $ultraYearPrice) {
						$deductCourse = true;
					}
				}

				// Cập nhật giao dịch thành 'refunded'
				$stmtTxUpdate = $conn->prepare("UPDATE transaction_history SET status = 'refunded' WHERE id = :id");
				$stmtTxUpdate->execute(['id' => $tx['id']]);
			}
		}

		$refundAmount = $totalPaid;
		if ($deductCourse) {
			$refundAmount = max(0, $totalPaid - 249000);
		}

		// 3. Đọc dữ liệu users để giữ nguyên has_course
		$stmtCheck = $conn->prepare("SELECT has_course FROM users WHERE id = :id");
		$stmtCheck->execute(['id' => $userId]);
		$hasCourse = $stmtCheck->fetchColumn() ? 1 : 0;

		// 4. Lấy các giao dịch 'success' còn lại sau hoàn tiền để tính lại gói dịch vụ
		$stmtTxLeft = $conn->prepare("SELECT * FROM transaction_history WHERE user_id = :user_id AND status = 'success' ORDER BY created_at ASC");
		$stmtTxLeft->execute(['user_id' => $userId]);
		$txsLeft = $stmtTxLeft->fetchAll(PDO::FETCH_ASSOC);

		$isPremium = 0;
		$premiumPlan = null;
		$premiumUntil = null;

		foreach ($txsLeft as $tx) {
			$planId = $tx['plan_id'];
			$plan = $plans[$planId] ?? null;
			if (!$plan) continue;

			if ($plan['is_premium'] ?? true) {
				$isPremium = 1;
			}
			if ($plan['has_course'] ?? false) {
				$hasCourse = 1;
			}

			$newTier = $plan['tier'] ?? 0;
			$newDays = $plan['days'] ?? 0;
			
			$currentPlan = $premiumPlan ?? 'free';
			$currentDays = $plans[$currentPlan]['days'] ?? 0;
			$userTier = $plans[$currentPlan]['tier'] ?? 0;

			if ($newTier >= $userTier || $newDays > $currentDays) {
				$premiumPlan = $planId;
				$premiumUntil = date('Y-m-d H:i:s', strtotime($tx['created_at'] . " +{$newDays} days"));
			}
		}

		// Cập nhật lại thông tin user trong Database
		$stmtUser = $conn->prepare("UPDATE users SET is_premium = :is_premium, has_course = :has_course, premium_plan = :premium_plan, premium_until = :premium_until WHERE id = :id");
		$stmtUser->execute([
			'is_premium'    => $isPremium,
			'has_course'    => $hasCourse,
			'premium_plan'  => $premiumPlan,
			'premium_until' => $premiumUntil,
			'id'            => $userId
		]);

		// Cập nhật lại Session của user theo trạng thái mới
		$_SESSION['is_premium']   = (bool)$isPremium;
		$_SESSION['has_course']   = (bool)$hasCourse;
		$_SESSION['premium_plan'] = $premiumPlan;
		if ($premiumPlan && isset($plans[$premiumPlan])) {
			$_SESSION['premium_name']   = $plans[$premiumPlan]['name'];
			$_SESSION['premium_price']  = $plans[$premiumPlan]['price'];
			$_SESSION['premium_period'] = $plans[$premiumPlan]['period'];
		} else {
			$_SESSION['premium_name']   = null;
			$_SESSION['premium_price']  = null;
			$_SESSION['premium_period'] = null;
		}
		$_SESSION['premium_until'] = $premiumUntil;

		// Cập nhật lại lịch sử thanh toán trong Session
		$stmtAllTx = $conn->prepare("SELECT tx_id as id, plan_id, plan_name, price, period, status, created_at FROM transaction_history WHERE user_id = :user_id ORDER BY created_at DESC");
		$stmtAllTx->execute(['user_id' => $userId]);
		$dbHistory = $stmtAllTx->fetchAll(PDO::FETCH_ASSOC);

		$_SESSION['payment_history'] = array_reverse($dbHistory);
		if (!empty($dbHistory)) {
			$_SESSION['last_payment'] = $dbHistory[0];
		} else {
			$_SESSION['last_payment'] = null;
		}

	} catch (PDOException $e) {
		error_log("Refund save error: " . $e->getMessage());
	}
}

echo json_encode([
	'success' => true,
	'message' => 'Hoàn tiền ' . number_format($refundAmount, 0, ',', '.') . '₫ và hủy gói thành công',
]);
