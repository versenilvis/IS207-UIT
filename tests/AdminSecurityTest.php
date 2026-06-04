<?php
// bộ kiểm thử bảo mật admin
// kiểm tra phân quyền tài khoản admin và bảo vệ tài nguyên hệ thống

require_once __DIR__ . '/bootstrap.php';

class AdminSecurityTest {
    private $baseUrl;

    public function __construct() {
        $port = (file_exists('/.dockerenv') || is_dir('/var/www/html/server')) ? 80 : 3000;
        $this->baseUrl = "http://localhost:{$port}/server";
    }

    public function run() {
        echo "Running Admin Security Tests...\n";
        $this->testAdminEndpointsUnauthorized();
    }

    private function testAdminEndpointsUnauthorized() {
        echo "  - Testing POST /api/tests without admin rights...\n";
        $resPost = $this->sendPostRequest('/api/tests', ['title' => 'Hacker Test']);
        $this->assertEquals(401, $resPost['http_code'], "POST /api/tests as guest should return 401");

        echo "  - Testing DELETE /api/tests/{uuid} without admin rights...\n";
        $resDelete = $this->sendDeleteRequest('/api/tests/some-uuid');
        $this->assertEquals(401, $resDelete['http_code'], "DELETE /api/tests/{uuid} as guest should return 401");
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

    private function sendDeleteRequest($endpoint) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
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
