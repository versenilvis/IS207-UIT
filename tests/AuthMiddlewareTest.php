<?php
// bộ kiểm thử middleware auth
// kiểm tra phân quyền, xác thực session cookie và khả năng hiển thị đề ẩn

require_once __DIR__ . '/bootstrap.php';

class AuthMiddlewareTest {
    private $db;
    private $baseUrl;

    public function __construct($db) {
        $this->db = $db;
        $port = (file_exists('/.dockerenv') || is_dir('/var/www/html/server')) ? 80 : 3000;
        $this->baseUrl = "http://localhost:{$port}/server";
    }

    public function run() {
        echo "Running Auth Middleware Tests...\n";
        $this->testScoreUnauthorized();
        $this->testInactiveTestAccess();
    }

    private function testScoreUnauthorized() {
        echo "  - Testing GET /api/score without session...\n";
        $res = $this->sendGetRequest('/api/score?attempt_id=1');
        $this->assertEquals(401, $res['http_code'], "GET /api/score without session should return 401");
        
        $data = json_decode($res['body'], true);
        $this->assertEquals(false, $data['success'] ?? null, "GET /api/score success field should be false");

        echo "  - Testing POST /api/score without session...\n";
        $resPost = $this->sendPostRequest('/api/score', ['test_uuid' => 'dummy', 'answers' => [], 'time_spent' => 10]);
        $this->assertEquals(401, $resPost['http_code'], "POST /api/score without session should return 401");
    }

    private function testInactiveTestAccess() {
        echo "  - Testing GET /api/tests/{uuid} for inactive test...\n";

        // thêm một đề thi ẩn tạm thời vào DB
        $testUuid = 'test-inactive-' . bin2hex(random_bytes(4));
        $stmt = $this->db->prepare("INSERT INTO tests (uuid, title, total_questions, duration, is_active) VALUES (?, 'Inactive Test', 0, 3600, 0)");
        $stmt->execute([$testUuid]);
        $testId = $this->db->lastInsertId();

        try {
            // gọi API E2E với tư cách là khách
            $res = $this->sendGetRequest('/api/tests/' . $testUuid);
            $this->assertEquals(401, $res['http_code'], "GET /api/tests/{inactive_uuid} as guest should be blocked with 401 by requireAuth");
        } finally {
            // dọn dẹp dữ liệu
            $stmtDel = $this->db->prepare("DELETE FROM tests WHERE id = ?");
            $stmtDel->execute([$testId]);
            echo "  - Temporary inactive test cleaned from DB\n";
        }
    }

    private function sendGetRequest($endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['http_code' => $httpCode, 'body' => $body];
    }

    private function sendPostRequest($endpoint, $payload) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['http_code' => $httpCode, 'body' => $body];
    }

    private function assertEquals($expected, $actual, $message) {
        TestTracker::$assertions++;
        if ($expected !== $actual) {
            throw new Exception("FAIL: {$message} (Expected: {$expected}, Got: {$actual})");
        }
        echo "    [PASS] {$message}\n";
    }
}
