<?php
// bộ kiểm thử tính điểm toeic
// kiểm tra model chấm điểm toeic và giải thuật nộp/chấm bài

require_once __DIR__ . '/bootstrap.php';

class ScoreCalculationTest {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function run() {
        echo "Running Score Calculation Tests...\n";
        $this->testCalculateToeicScore();
        $this->testSubmitAndGradeFlow();
    }

    private function testCalculateToeicScore() {
        echo "  - Testing calculateToeicScore scales...\n";
        
        // kiểm tra điểm tối thiểu
        $this->assertEquals(5, calculateToeicScore(0), "min score should be 5");
        
        // kiểm tra quy đổi điểm tuyến tính
        $this->assertEquals(450, calculateToeicScore(90), "90 correct should be 450");
        
        // kiểm tra khoảng giảm điểm 91-95
        $this->assertEquals(490, calculateToeicScore(95), "95 correct should be 490");
        $this->assertEquals(485, calculateToeicScore(94), "94 correct should be 485");
        
        // kiểm tra khoảng điểm tối đa 96-100
        $this->assertEquals(495, calculateToeicScore(96), "96 correct should be 495");
        $this->assertEquals(495, calculateToeicScore(100), "100 correct should be 495");
    }

    private function testSubmitAndGradeFlow() {
        echo "  - Testing submitAndGrade logic in DB...\n";

        // lấy một user id hợp lệ từ database
        $stmtUser = $this->db->prepare("SELECT id FROM users LIMIT 1");
        $stmtUser->execute();
        $userId = $stmtUser->fetchColumn();

        if (!$userId) {
            echo "    [SKIP] No user found in DB to run submitAndGrade test\n";
            return;
        }

        $testId = null;
        $attemptId = null;
        $attemptId2 = null;
        $attemptId3 = null;
        $attemptId4 = null;

        try {
            $testUuid = 'test-temp-' . bin2hex(random_bytes(4));
            
            // tạo một đề thi giả lập
            $stmt = $this->db->prepare("INSERT INTO tests (uuid, title, total_questions, duration, is_active) VALUES (?, 'Mock Test', 4, 3600, 1)");
            $stmt->execute([$testUuid]);
            $testId = $this->db->lastInsertId();

            // thêm 4 câu hỏi: 2 nghe (part 1, 2) và 2 đọc (part 5, 6)
            $stmtQ = $this->db->prepare("INSERT INTO questions (test_id, part, question_number, correct_answer) VALUES (?, ?, ?, ?)");
            
            // câu hỏi phần nghe
            $stmtQ->execute([$testId, 1, 1, 'A']);
            $q1Id = $this->db->lastInsertId();
            $stmtQ->execute([$testId, 2, 2, 'B']);
            $q2Id = $this->db->lastInsertId();

            // câu hỏi phần đọc
            $stmtQ->execute([$testId, 5, 3, 'C']);
            $q3Id = $this->db->lastInsertId();
            $stmtQ->execute([$testId, 6, 4, 'D']);
            $q4Id = $this->db->lastInsertId();

            // trường hợp 1: làm đúng hoàn toàn
            $userAnswers = [
                $q1Id => 'A',
                $q2Id => 'B',
                $q3Id => 'C',
                $q4Id => 'D'
            ];
            
            // xác nhận logic đúng 100%
            $attemptId = submitAndGrade($this->db, $userId, $testUuid, $userAnswers, 120);
            $this->assertNotEmpty($attemptId, "submitAndGrade should return attempt id");

            $stmtAttempt = $this->db->prepare("SELECT * FROM attempts WHERE id = ?");
            $stmtAttempt->execute([$attemptId]);
            $attempt = $stmtAttempt->fetch(PDO::FETCH_ASSOC);

            // đúng 100% -> 495 nghe và 495 đọc -> 990 tổng
            $this->assertEquals(495, $attempt['listening_score'], "100% listening correct should yield 495");
            $this->assertEquals(495, $attempt['reading_score'], "100% reading correct should yield 495");
            $this->assertEquals(990, $attempt['total_score'], "100% total correct should yield 990");

            // trường hợp 2: làm sai hoàn toàn
            $wrongAnswers = [
                $q1Id => 'B',
                $q2Id => 'A',
                $q3Id => 'D',
                $q4Id => 'C'
            ];
            $attemptId2 = submitAndGrade($this->db, $userId, $testUuid, $wrongAnswers, 150);
            $stmtAttempt->execute([$attemptId2]);
            $attempt2 = $stmtAttempt->fetch(PDO::FETCH_ASSOC);

            // sai 100% -> 5 nghe và 5 đọc -> 10 tổng
            $this->assertEquals(5, $attempt2['listening_score'], "0% listening correct should yield 5");
            $this->assertEquals(5, $attempt2['reading_score'], "0% reading correct should yield 5");
            $this->assertEquals(10, $attempt2['total_score'], "0% total correct should yield 10");

            // trường hợp 3: bỏ trống một số câu
            $partialAnswers = [
                $q1Id => 'A', // correct listening
                $q2Id => null, // missing listening
                $q3Id => null, // missing reading
                $q4Id => 'D' // correct reading
            ];
            $attemptId3 = submitAndGrade($this->db, $userId, $testUuid, $partialAnswers, 180);
            $stmtAttempt->execute([$attemptId3]);
            $attempt3 = $stmtAttempt->fetch(PDO::FETCH_ASSOC);

            // nghe: đúng 1 trên 2 câu -> 50% -> quy đổi 50 -> 250 điểm
            // đọc: đúng 1 trên 2 câu -> 50% -> quy đổi 50 -> 250 điểm
            // tổng điểm: 500
            $this->assertEquals(250, $attempt3['listening_score'], "50% listening correct should yield 250");
            $this->assertEquals(250, $attempt3['reading_score'], "50% reading correct should yield 250");
            $this->assertEquals(500, $attempt3['total_score'], "50% total correct should yield 500");

            // trường hợp 4: khớp đáp án theo question_id thay vì thứ tự mảng
            $scrambledAnswers = [];
            $scrambledAnswers[$q3Id] = 'C';
            $scrambledAnswers[$q1Id] = 'A';
            $scrambledAnswers[$q4Id] = 'D';
            $scrambledAnswers[$q2Id] = 'B';

            $attemptId4 = submitAndGrade($this->db, $userId, $testUuid, $scrambledAnswers, 200);
            $stmtAttempt->execute([$attemptId4]);
            $attempt4 = $stmtAttempt->fetch(PDO::FETCH_ASSOC);
            $this->assertEquals(990, $attempt4['total_score'], "scrambled key answers should match question ids correctly");

        } finally {
            // dọn dẹp dữ liệu giả lập thủ công
            if ($attemptId) {
                $this->db->prepare("DELETE FROM attempt_answers WHERE attempt_id = ?")->execute([$attemptId]);
                $this->db->prepare("DELETE FROM attempts WHERE id = ?")->execute([$attemptId]);
            }
            if ($attemptId2) {
                $this->db->prepare("DELETE FROM attempt_answers WHERE attempt_id = ?")->execute([$attemptId2]);
                $this->db->prepare("DELETE FROM attempts WHERE id = ?")->execute([$attemptId2]);
            }
            if ($attemptId3) {
                $this->db->prepare("DELETE FROM attempt_answers WHERE attempt_id = ?")->execute([$attemptId3]);
                $this->db->prepare("DELETE FROM attempts WHERE id = ?")->execute([$attemptId3]);
            }
            if ($attemptId4) {
                $this->db->prepare("DELETE FROM attempt_answers WHERE attempt_id = ?")->execute([$attemptId4]);
                $this->db->prepare("DELETE FROM attempts WHERE id = ?")->execute([$attemptId4]);
            }
            if ($testId) {
                $this->db->prepare("DELETE FROM questions WHERE test_id = ?")->execute([$testId]);
                $this->db->prepare("DELETE FROM tests WHERE id = ?")->execute([$testId]);
            }
            echo "  - Temporary test and attempt records cleaned from DB\n";
        }
    }

    private function assertEquals($expected, $actual, $message) {
        TestTracker::$assertions++;
        if ($expected !== $actual) {
            throw new Exception("FAIL: {$message} (Expected: {$expected}, Got: {$actual})");
        }
        echo "    [PASS] {$message}\n";
    }

    private function assertNotEmpty($value, $message) {
        TestTracker::$assertions++;
        if (empty($value)) {
            throw new Exception("FAIL: {$message} (Value is empty)");
        }
        echo "    [PASS] {$message}\n";
    }
}
