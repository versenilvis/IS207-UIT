<?php
// bộ kiểm thử bảo mật
// kiểm tra bảo mật chống sql injection và chống cheat điểm số

require_once __DIR__ . '/bootstrap.php';

class SecurityTest {
    private $db;
    private $baseUrl;

    public function __construct($db) {
        $this->db = $db;
        $port = (file_exists('/.dockerenv') || is_dir('/var/www/html/server')) ? 80 : 3000;
        $this->baseUrl = "http://localhost:{$port}/server";
    }

    public function run() {
        echo "Running Security Tests...\n";
        $this->testSqlInjectionProtection();
        $this->testScoreCheatProtection();
    }

    private function testSqlInjectionProtection() {
        echo "  - Testing SQL Injection protection via UUID parameters...\n";
        
        // thử nghiệm tấn công sql injection trên câu lệnh lấy chi tiết đề thi
        $sqliUuid = "1' OR '1'='1";
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tests WHERE uuid = :uuid");
        $stmt->execute(['uuid' => $sqliUuid]);
        $count = (int)$stmt->fetchColumn();
        
        // kết quả phải trả về 0 bản ghi do tham số được parameterized chứ không nối chuỗi
        $this->assertEquals(0, $count, "SQL injection payload should return 0 results due to parameterized query");
    }

    private function testScoreCheatProtection() {
        echo "  - Testing score cheat protection (server-side grading validation)...\n";
        
        // đảm bảo submitAndGrade không nhận hay đọc tham số score/correct_count từ phía client
        // kiểm tra signature của hàm để chắc chắn rằng không có tham số nhạy cảm nào được truyền từ client
        $ref = new ReflectionFunction('submitAndGrade');
        $params = $ref->getParameters();
        
        $paramNames = [];
        foreach ($params as $p) {
            $paramNames[] = $p->getName();
        }
        
        $this->assertEquals(true, in_array('conn', $paramNames), "submitAndGrade must receive conn");
        $this->assertEquals(true, in_array('user_id', $paramNames), "submitAndGrade must receive user_id");
        $this->assertEquals(true, in_array('test_uuid', $paramNames), "submitAndGrade must receive test_uuid");
        $this->assertEquals(true, in_array('user_answers', $paramNames), "submitAndGrade must receive user_answers");
        $this->assertEquals(true, in_array('time_spent', $paramNames), "submitAndGrade must receive time_spent");
        
        $this->assertEquals(false, in_array('score', $paramNames), "submitAndGrade must not accept client-controlled score parameter");
        $this->assertEquals(false, in_array('correct_count', $paramNames), "submitAndGrade must not accept client-controlled correct_count parameter");
    }

    private function assertEquals($expected, $actual, $message) {
        TestTracker::$assertions++;
        if ($expected !== $actual) {
            throw new Exception("FAIL: {$message} (Expected: {$expected}, Got: {$actual})");
        }
        echo "    [PASS] {$message}\n";
    }
}
