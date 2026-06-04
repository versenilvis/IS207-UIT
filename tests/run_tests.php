<?php
// trình chạy test tập trung
// thực thi tất cả các suite test và in ra báo cáo tổng hợp

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/ScoreCalculationTest.php';
require_once __DIR__ . '/AuthMiddlewareTest.php';
require_once __DIR__ . '/DatabaseTransactionTest.php';
require_once __DIR__ . '/SecurityTest.php';
require_once __DIR__ . '/AdminSecurityTest.php';

global $conn;

$suites = [
    'Score Calculation' => new ScoreCalculationTest($conn),
    'Auth Middleware' => new AuthMiddlewareTest($conn),
    'Database Transactions' => new DatabaseTransactionTest($conn),
    'Security' => new SecurityTest($conn),
    'Admin Security' => new AdminSecurityTest()
];

$passedCount = 0;
$failedCount = 0;
$failures = [];

echo "=========================================\n";
echo "       PREPHUB TEST SUITE RUNNER         \n";
echo "=========================================\n\n";

foreach ($suites as $name => $suite) {
    try {
        $suite->run();
        $passedCount++;
        echo "[SUCCESS] Suite '{$name}' completed successfully\n\n";
    } catch (Exception $e) {
        $failedCount++;
        $failures[$name] = $e->getMessage();
        echo "[FAILURE] Suite '{$name}' failed: " . $e->getMessage() . "\n\n";
    }
}

echo "=========================================\n";
echo "             TEST RESULTS                \n";
echo "=========================================\n";
echo "Total Suites Run: " . count($suites) . "\n";
echo "Passed Suites:    {$passedCount}\n";
echo "Failed Suites:    {$failedCount}\n";
echo "Total Assertions: " . TestTracker::$assertions . " passed\n";

if ($failedCount > 0) {
    echo "\nFailed Details:\n";
    foreach ($failures as $name => $error) {
        echo "  - {$name}: {$error}\n";
    }
    exit(1);
} else {
    echo "\n[ALL PASSED] All test suites completed successfully!\n";
    exit(0);
}
