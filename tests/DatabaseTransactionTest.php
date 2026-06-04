<?php
// bộ kiểm thử transaction database
// kiểm tra tính năng rollback khi có ngoại lệ CSDL trong hàm submitAndGrade

require_once __DIR__ . '/bootstrap.php';

class DatabaseTransactionTest {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function run() {
        echo "Running Database Transaction Tests...\n";
        $this->testTransactionRollbackOnFailure();
    }

    private function testTransactionRollbackOnFailure() {
        echo "  - Testing transaction rollback on details insert failure...\n";
        
        // 1. Lấy uuid đề thi hợp lệ
        $stmt = $this->db->prepare("SELECT id, uuid FROM tests LIMIT 1");
        $stmt->execute();
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        // lấy một user id hợp lệ
        $stmtUser = $this->db->prepare("SELECT id FROM users LIMIT 1");
        $stmtUser->execute();
        $userId = $stmtUser->fetchColumn();

        if (!$test || !$userId) {
            echo "    [SKIP] No test or user found to run transaction test\n";
            return;
        }

        $testUuid = $test['uuid'];
        $testId = $test['id'];

        // lấy một question id hợp lệ thuộc đề thi này
        $stmtQ = $this->db->prepare("SELECT id FROM questions WHERE test_id = ? LIMIT 1");
        $stmtQ->execute([$testId]);
        $questionId = $stmtQ->fetchColumn();

        if (!$questionId) {
            echo "    [SKIP] No questions found in the test to run transaction test\n";
            return;
        }

        // đếm số lượng lượt làm bài trước khi chạy test
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM attempts");
        $stmtCount->execute();
        $countBefore = (int)$stmtCount->fetchColumn();

        // 2. Truyền giá trị đáp án quá dài cho cột selected_answer (char(1)) để gây lỗi vi phạm ràng buộc cột
        $badAnswers = [
            $questionId => 'TOO_LONG_ANSWER_VALUE'
        ];

        $thrown = false;
        try {
            submitAndGrade($this->db, $userId, $testUuid, $badAnswers, 60);
        } catch (PDOException $e) {
            $thrown = true;
            echo "    [PASS] Correctly caught exception: " . $e->getMessage() . "\n";
        }

        $this->assertEquals(true, $thrown, "submitAndGrade must throw PDOException on bad details data");

        // 3. Đếm số lượng lượt làm bài sau khi chạy test
        $stmtCount->execute();
        $countAfter = (int)$stmtCount->fetchColumn();

        $this->assertEquals($countBefore, $countAfter, "attempts count should remain unchanged due to transaction rollback");
    }

    private function assertEquals($expected, $actual, $message) {
        TestTracker::$assertions++;
        if ($expected !== $actual) {
            throw new Exception("FAIL: {$message} (Expected: {$expected}, Got: {$actual})");
        }
        echo "    [PASS] {$message}\n";
    }
}
