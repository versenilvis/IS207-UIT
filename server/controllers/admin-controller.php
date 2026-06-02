<?php
/**
 * Controller admin dùng cho các API endpoint của dashboard
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../utils/response.php';

// kiểm tra quyền admin
function checkAdminAccess() {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
        sendError("Forbidden: Bạn không có quyền truy cập", 403);
    }
}

// lấy thống kê tổng quan cho dashboard
function getAdminStats() {
    global $conn;
    checkAdminAccess();

    try {
        $total_users = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_tests = (int)$conn->query("SELECT COUNT(*) FROM tests")->fetchColumn();
        $total_revenue = (int)$conn->query("SELECT COALESCE(SUM(price), 0) FROM transaction_history WHERE status = 'success'")->fetchColumn();
        $total_purchased_users = (int)$conn->query("SELECT COUNT(DISTINCT user_id) FROM transaction_history WHERE status = 'success'")->fetchColumn();

        sendJson([
            'success' => true,
            'data' => [
                'total_users' => $total_users,
                'total_tests' => $total_tests,
                'total_revenue' => $total_revenue,
                'total_purchased_users' => $total_purchased_users
            ]
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// lấy danh sách người dùng phân trang
function getAdminUsers() {
    global $conn;
    checkAdminAccess();

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $role_filter = isset($_GET['role']) ? trim($_GET['role']) : '';
    $status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

    try {
        // tính toán thống kê nhỏ cho tab user
        $total_users = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $new_users = (int)$conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
        $inactive_users = (int)$conn->query("SELECT COUNT(*) FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM attempts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))")->fetchColumn();

        // chuẩn bị truy vấn tìm kiếm và lọc
        $where_clauses = [];
        $params = [];
        
        if ($q !== '') {
            $where_clauses[] = "(email LIKE :q OR first_name LIKE :q OR last_name LIKE :q)";
            $params['q'] = '%' . $q . '%';
        }
        
        if ($role_filter !== '') {
            $where_clauses[] = "role = :role";
            $params['role'] = $role_filter;
        }
        
        if ($status_filter !== '') {
            if ($status_filter === 'banned') {
                $where_clauses[] = "is_banned = 1";
            } elseif ($status_filter === 'active') {
                $where_clauses[] = "is_banned = 0";
            }
        }
        
        $where_clause = "";
        if (count($where_clauses) > 0) {
            $where_clause = " WHERE " . implode(" AND ", $where_clauses);
        }

        // lấy tổng số lượng người dùng khớp tìm kiếm
        $count_stmt = $conn->prepare("SELECT COUNT(*) FROM users" . $where_clause);
        $count_stmt->execute($params);
        $total_filtered = (int)$count_stmt->fetchColumn();

        // lấy danh sách người dùng
        $sql = "SELECT id, uuid, first_name, last_name, email, role, is_banned, is_premium, has_course, premium_plan, premium_until, created_at, avatar,
                       (SELECT COUNT(DISTINCT a2.test_id) FROM attempts a2 WHERE a2.user_id = users.id) AS user_tests_attempted,
                       (SELECT COUNT(*) FROM tests WHERE is_active = 1) AS total_active_tests
                FROM users" . $where_clause . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJson([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total_filtered
            ],
            'stats' => [
                'total_users' => $total_users,
                'new_users_month' => $new_users,
                'inactive_users_7d' => $inactive_users
            ]
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// cập nhật vai trò người dùng và trạng thái ban
function updateAdminUser($userId) {
    global $conn;
    checkAdminAccess();

    $input = json_decode(file_get_contents('php://input'), true);
    $role = $input['role'] ?? null;
    $is_banned = isset($input['is_banned']) ? (int)$input['is_banned'] : null;

    if ($role === null && $is_banned === null) {
        sendError("Không có trường dữ liệu nào được cập nhật", 400);
    }

    try {
        // tạo truy vấn cập nhật động
        $fields = [];
        $params = ['id' => $userId];

        if ($role !== null) {
            if (!in_array($role, ['user', 'admin'])) {
                sendError("Role không hợp lệ", 400);
            }
            $fields[] = "role = :role";
            $params['role'] = $role;
        }

        if ($is_banned !== null) {
            $fields[] = "is_banned = :is_banned";
            $params['is_banned'] = $is_banned;
        }

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        sendJson([
            'success' => true,
            'message' => 'Cập nhật user thành công'
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// lấy danh sách lượt thi phân trang
function getAdminAttempts() {
    global $conn;
    checkAdminAccess();

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

    try {
        // lấy tổng số lượt thi
        $total = (int)$conn->query("SELECT COUNT(*) FROM attempts")->fetchColumn();

        // lấy lượt thi với thông tin chi tiết người dùng và đề thi kèm tiến trình
        $sql = "SELECT 
                    a.id, a.uuid, a.listening_correct, a.reading_correct, a.listening_score, a.reading_score, a.total_score, a.time_spent, a.created_at,
                    u.first_name, u.last_name, u.email, u.is_premium, u.premium_plan, u.has_course, u.avatar,
                    t.title,
                    (SELECT COUNT(DISTINCT a2.test_id) FROM attempts a2 WHERE a2.user_id = a.user_id) AS user_tests_attempted,
                    (SELECT COUNT(*) FROM tests WHERE is_active = 1) AS total_active_tests
                FROM attempts a
                JOIN users u ON a.user_id = u.id
                JOIN tests t ON a.test_id = t.id
                ORDER BY a.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJson([
            'success' => true,
            'data' => $attempts,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total
            ]
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// lấy tóm tắt doanh thu và lịch sử
function getAdminRevenue() {
    global $conn;
    checkAdminAccess();

    try {
        // tổng doanh thu thực tế tháng hiện tại
        $current_month_revenue = (int)$conn->query("SELECT COALESCE(SUM(price), 0) FROM transaction_history WHERE status = 'success' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
        
        // tổng doanh thu thực tế mọi thời gian
        $all_time_revenue = (int)$conn->query("SELECT COALESCE(SUM(price), 0) FROM transaction_history WHERE status = 'success'")->fetchColumn();

        // lấy các chỉ số thống kê giao dịch thành công và hoàn tiền
        $success_count = (int)$conn->query("SELECT COUNT(*) FROM transaction_history WHERE status = 'success'")->fetchColumn();
        $refunded_count = (int)$conn->query("SELECT COUNT(*) FROM transaction_history WHERE status = 'refunded'")->fetchColumn();
        $refunded_amount = (int)$conn->query("SELECT COALESCE(SUM(price), 0) FROM transaction_history WHERE status = 'refunded'")->fetchColumn();

        // tính toán bảng phân rã chi tiết doanh thu theo gói
        $breakdown_sql = "
            SELECT plan_id, plan_name,
                   SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
                   SUM(CASE WHEN status = 'success' THEN price ELSE 0 END) AS success_amount,
                   SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) AS refunded_count,
                   SUM(CASE WHEN status = 'refunded' THEN price ELSE 0 END) AS refunded_amount
            FROM transaction_history
            WHERE status IN ('success', 'refunded')
            GROUP BY plan_id, plan_name
        ";
        $breakdown_raw = $conn->query($breakdown_sql)->fetchAll(PDO::FETCH_ASSOC);

        $breakdown_data = [];
        foreach ($breakdown_raw as $row) {
            $s_count = (int)$row['success_count'];
            $s_amount = (int)$row['success_amount'];
            $r_count = (int)$row['refunded_count'];
            $r_amount = (int)$row['refunded_amount'];
            
            $gross_revenue = $s_amount + $r_amount;
            $net_revenue = $s_amount;
            
            $total_count = $s_count + $r_count;
            $refund_rate = $total_count > 0 ? round(($r_count / $total_count) * 100, 1) : 0;
            
            $breakdown_data[] = [
                'plan_id' => $row['plan_id'],
                'plan_name' => $row['plan_name'],
                'success_count' => $s_count,
                'gross_revenue' => $gross_revenue,
                'refunded_count' => $r_count,
                'refunded_amount' => $r_amount,
                'net_revenue' => $net_revenue,
                'refund_rate' => $refund_rate
            ];
        }

        // lấy danh sách tất cả giao dịch theo thứ tự thời gian tăng dần
        $sql = "SELECT created_at, price, status FROM transaction_history WHERE status = 'success' OR status = 'refunded' ORDER BY created_at ASC";
        $raw_chart = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // tính doanh thu luỹ kế bắt đầu từ 0
        $chart_data = [];
        $chart_data[] = [
            'label' => 'Bắt đầu',
            'total' => 0
        ];
        
        $running_total = 0;
        foreach ($raw_chart as $tx) {
            $price = (int)$tx['price'];
            if ($tx['status'] === 'success') {
                $running_total += $price;
                $chart_data[] = [
                    'label' => date('d/m/Y H:i', strtotime($tx['created_at'])),
                    'total' => $running_total
                ];
            } elseif ($tx['status'] === 'refunded') {
                // cộng tiền thanh toán trước
                $running_total += $price;
                $chart_data[] = [
                    'label' => date('d/m/Y H:i', strtotime($tx['created_at'])) . ' (Thanh toán)',
                    'total' => $running_total
                ];
                // trừ tiền hoàn trả sau
                $running_total -= $price;
                $chart_data[] = [
                    'label' => date('d/m/Y H:i', strtotime($tx['created_at'])) . ' (Hoàn tiền)',
                    'total' => $running_total
                ];
            }
        }

        sendJson([
            'success' => true,
            'data' => [
                'current_month' => $current_month_revenue,
                'all_time' => $all_time_revenue,
                'success_count' => $success_count,
                'refunded_count' => $refunded_count,
                'refunded_amount' => $refunded_amount,
                'breakdown' => $breakdown_data,
                'chart' => $chart_data
            ]
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// lấy danh sách giao dịch phân trang
function getAdminTransactions() {
    global $conn;
    checkAdminAccess();

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

    try {
        // lấy tổng số giao dịch
        $total = (int)$conn->query("SELECT COUNT(*) FROM transaction_history WHERE status = 'success' OR status = 'refunded'")->fetchColumn();

        // lấy danh sách giao dịch
        $sql = "SELECT t.id, t.tx_id, t.plan_id, t.plan_name, t.price, t.period, t.status, t.created_at,
                       u.first_name, u.last_name, u.email, u.avatar
                FROM transaction_history t
                JOIN users u ON t.user_id = u.id
                WHERE t.status = 'success' OR t.status = 'refunded'
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJson([
            'success' => true,
            'data' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total
            ]
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

// tìm file đệ quy trong thư mục
function findFileInDirRecursive($dir, $filename) {
    $dir = rtrim($dir, '/');
    if (!is_dir($dir)) return null;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $res = findFileInDirRecursive($path, $filename);
            if ($res !== null) return $res;
        } elseif (is_file($path) && basename($path) === $filename) {
            return $path;
        }
    }
    return null;
}

// xóa thư mục đệ quy
function helperRemoveDir($dir) {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            helperRemoveDir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

// chuyển tiêu đề thành slug url
function helperSlugify($text) {
    $unicode = array(
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|ẫ|ậ',
        'd'=>'đ|Đ',
        'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'i'=>'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
        'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ'
    );
    foreach ($unicode as $nonUnicode => $uni) {
        $text = preg_replace("/($uni)/i", $nonUnicode, $text);
    }
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

// lấy inner html của một node dom
function getInnerHtmlNode($node) {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $node->ownerDocument->saveHTML($child);
    }
    return trim($html);
}

// phân tích khối câu hỏi từ html
function helperParseQuestion($xpath, $node, $examAudioUrl, &$isFirstQuestion) {
    $q = ['correct_answer' => 'A'];
    $numNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' question-num ')]", $node);
    $numText = '';
    if ($numNodes->length > 0) {
        $numText = rtrim(trim($numNodes->item(0)->textContent), '.');
    }
    $num = (int)$numText;
    if ($num > 0) {
        $q['question_number'] = $num;
    }
    $contentNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' question-content ')]", $node);
    $content = '';
    if ($contentNodes->length > 0) {
        $content = trim($contentNodes->item(0)->textContent);
    }
    if (str_contains($content, 'Mark your answer')) {
        $content = '';
    }
    $q['content'] = $content;
    $imgNodes = $node->getElementsByTagName('img');
    if ($imgNodes->length > 0) {
        $src = $imgNodes->item(0)->getAttribute('src');
        if ($src) {
            $q['image_url'] = basename($src);
        }
    }
    $options = [];
    $optNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' option ')]", $node);
    foreach ($optNodes as $opt) {
        $inputNodes = $xpath->query("descendant::input", $opt);
        $label = '';
        if ($inputNodes->length > 0) {
            $label = trim($inputNodes->item(0)->getAttribute('value') ?? '');
        }
        $labelNodes = $xpath->query("descendant::label", $opt);
        $labelText = '';
        if ($labelNodes->length > 0) {
            $labelText = $labelNodes->item(0)->textContent;
        }
        $cleaned = trim($labelText);
        if ($label !== '') {
            $prefix = "({$label})";
            if (str_starts_with($cleaned, $prefix)) {
                $cleaned = trim(substr($cleaned, strlen($prefix)));
            }
        }
        if (empty($label)) {
            foreach ($opt->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $text = trim($child->textContent);
                    if (preg_match('/\(([A-D])\)/', $text, $matches)) {
                        $label = $matches[1];
                        break;
                    } elseif (strlen($text) === 1 && $text >= 'A' && $text <= 'D') {
                        $label = $text;
                        break;
                    }
                }
            }
            if (!empty($label)) {
                $optContentNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' option-content ')]", $opt);
                if ($optContentNodes->length > 0) {
                    $cleaned = trim($optContentNodes->item(0)->textContent);
                }
            }
        }
        if (!empty($label)) {
            $options[] = [
                'label' => $label,
                'content' => $cleaned
            ];
        }
    }
    $q['options'] = $options;
    if ($isFirstQuestion && !empty($examAudioUrl)) {
        $q['audio_url'] = $examAudioUrl;
        $isFirstQuestion = false;
    }
    return $q;
}

// phân tích nội dung html file đề thi
function helperParseExamHtml($htmlContent) {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    if (!str_contains($htmlContent, '<?xml encoding=')) {
        $htmlContent = '<?xml encoding="UTF-8">' . $htmlContent;
    }
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $duration = 7200;
    $audioUrl = '';
    $scripts = $doc->getElementsByTagName('script');
    foreach ($scripts as $script) {
        $text = $script->textContent;
        if (preg_match('/testDuration\s*=\s*(\d+)/', $text, $matches)) {
            $duration = (int)$matches[1];
        }
        if (preg_match('/listeningAudio\s*=\s*["\']([^"\']+)["\']/', $text, $matches)) {
            $audioUrl = basename($matches[1]);
        }
    }
    if (empty($audioUrl)) {
        $audioSources = $xpath->query('//audio[@id="full-test-audio"]//source');
        if ($audioSources->length > 0) {
            $src = $audioSources->item(0)->getAttribute('src');
            if ($src) {
                $audioUrl = basename($src);
            }
        }
    }
    $titleNode = $doc->getElementsByTagName('title')->item(0);
    $title = $titleNode ? trim($titleNode->textContent) : 'TOEIC Exam';
    $parts = [];
    $partIDs = ['part-1', 'part-2', 'part-3', 'part-4', 'part-5', 'part-6', 'part-7', 'part-8'];
    $isFirstQuestion = true;
    foreach ($partIDs as $partID) {
        $section = $xpath->query("//section[@id='{$partID}']")->item(0);
        if (!$section) continue;
        $partNum = (int)str_replace('part-', '', $partID);
        if ($partNum === 8) {
            $partNum = 7;
        }
        $partData = ['part_number' => $partNum];
        if (in_array($partNum, [1, 2, 5])) {
            $questions = [];
            $mcqNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' mcq-wrapper ')]", $section);
            foreach ($mcqNodes as $s) {
                $parent = $s->parentNode;
                $closestSection = null;
                while ($parent) {
                    if ($parent instanceof DOMElement && $parent->tagName === 'section') {
                        $closestSection = $parent;
                        break;
                    }
                    $parent = $parent->parentNode;
                }
                if ($closestSection !== $section) {
                    continue;
                }
                $q = helperParseQuestion($xpath, $s, null, $isFirstQuestion);
                if (isset($q['question_number'])) {
                    $questions[] = $q;
                }
            }
            $partData['questions'] = $questions;
        } else {
            $passages = [];
            $mcqgNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' mcqg-wrapper ')]", $section);
            foreach ($mcqgNodes as $s) {
                $parent = $s->parentNode;
                $closestSection = null;
                while ($parent) {
                    if ($parent instanceof DOMElement && $parent->tagName === 'section') {
                        $closestSection = $parent;
                        break;
                    }
                    $parent = $parent->parentNode;
                }
                if ($closestSection !== $section) {
                    continue;
                }
                $passage = [];
                $questions = [];
                $mcqNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' mcq-wrapper ')]", $s);
                foreach ($mcqNodes as $qSel) {
                    $parent = $qSel->parentNode;
                    $closestMCQG = null;
                    while ($parent && $parent !== $s) {
                        if ($parent instanceof DOMElement && str_contains($parent->getAttribute('class') ?? '', 'mcqg-wrapper')) {
                            $closestMCQG = $parent;
                            break;
                        }
                        $parent = $parent->parentNode;
                    }
                    if ($closestMCQG !== null) {
                        continue;
                    }
                    $q = helperParseQuestion($xpath, $qSel, null, $isFirstQuestion);
                    if (isset($q['question_number'])) {
                        $questions[] = $q;
                    }
                }
                if (empty($questions)) continue;
                $passage['questions'] = $questions;
                $imgNodes = $s->getElementsByTagName('img');
                if ($imgNodes->length > 0) {
                    $src = $imgNodes->item(0)->getAttribute('src');
                    if ($src) {
                        $passage['image_url'] = basename($src);
                    }
                }
                if (in_array($partNum, [6, 7])) {
                    $pContentNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' paragraph-p7 ') or contains(concat(' ', normalize-space(@class), ' '), ' passage-content ') or contains(concat(' ', normalize-space(@class), ' '), ' reading-passage ') or contains(concat(' ', normalize-space(@class), ' '), ' reading-text-wrapper ') or self::article]", $s);
                    if ($pContentNodes->length > 0) {
                        $passage['content'] = getInnerHtmlNode($pContentNodes->item(0));
                    }
                }
                if (empty($passage['content'])) {
                    $nums = array_column($questions, 'question_number');
                    $min = min($nums);
                    $max = max($nums);
                    $passage['content'] = "Questions {$min} - {$max}:";
                }
                $isFirstQuestion = false;
                $passages[] = $passage;
            }
            $partData['passages'] = $passages;
        }
        $parts[] = $partData;
    }
    $result = [
        'title' => $title,
        'duration' => $duration,
        'parts' => $parts
    ];
    if (!empty($audioUrl)) {
        $result['audio_url'] = $audioUrl;
    }
    return $result;
}

// phân tích nội dung html file đáp án
function helperParseAnswerHtml($htmlContent) {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    if (!str_contains($htmlContent, '<?xml encoding=')) {
        $htmlContent = '<?xml encoding="UTF-8">' . $htmlContent;
    }
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $titleNode = $doc->getElementsByTagName('title')->item(0);
    $title = $titleNode ? trim($titleNode->textContent) : 'TOEIC Answers';
    $answers = [];
    $mcqNodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' mcq-wrapper ')]");
    foreach ($mcqNodes as $s) {
        $numNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' question-num ')]", $s);
        $numText = '';
        if ($numNodes->length > 0) {
            $numText = rtrim(trim($numNodes->item(0)->textContent), '.');
        }
        $num = (int)$numText;
        if ($num <= 0) continue;
        $passageTrans = '';
        $passageEng = '';
        $passageAudio = '';
        $parent = $s->parentNode;
        $parentMCQG = null;
        while ($parent) {
            if ($parent instanceof DOMElement) {
                $classes = explode(' ', $parent->getAttribute('class') ?? '');
                if (in_array('mcqg-wrapper', $classes)) {
                    $parentMCQG = $parent;
                    break;
                }
            }
            $parent = $parent->parentNode;
        }
        if ($parentMCQG) {
            $viDivNode = $xpath->query("descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' reading-text-wrapper ') and contains(concat(' ', normalize-space(@class), ' '), ' text-vi ')]/div", $parentMCQG)->item(0);
            if ($viDivNode) {
                $passageTrans = getInnerHtmlNode($viDivNode);
            }
            $enDivNode = $xpath->query("descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' reading-text-wrapper ') and contains(concat(' ', normalize-space(@class), ' '), ' text-en ')]/div", $parentMCQG)->item(0);
            if ($enDivNode) {
                $passageEng = getInnerHtmlNode($enDivNode);
            }
            $pAudioNode = $xpath->query("descendant::audio//source", $parentMCQG)->item(0);
            if ($pAudioNode) {
                $src = $pAudioNode->getAttribute('src');
                if ($src) {
                    $passageAudio = trim($src);
                    if (str_starts_with($passageAudio, '/')) {
                        $passageAudio = 'https://tienganhmoingay.com' . $passageAudio;
                    }
                }
            }
        }
        $questionAudio = '';
        $qAudioNode = $xpath->query("descendant::audio//source", $s)->item(0);
        if ($qAudioNode) {
            $src = $qAudioNode->getAttribute('src');
            if ($src) {
                $questionAudio = trim($src);
                if (str_starts_with($questionAudio, '/')) {
                    $questionAudio = 'https://tienganhmoingay.com' . $questionAudio;
                }
            }
        }
        $correctLabel = '';
        $optionAnswers = [];
        $optNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' option ')]", $s);
        foreach ($optNodes as $opt) {
            if (!$opt instanceof DOMElement) continue;
            $label = '';
            foreach ($opt->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $text = trim($child->textContent);
                    if (preg_match('/\(([A-D])\)/', $text, $matches)) {
                        $label = $matches[1];
                        break;
                    } elseif (strlen($text) === 1 && $text >= 'A' && $text <= 'D') {
                        $label = $text;
                        break;
                    }
                }
            }
            if (empty($label)) continue;
            $transNodes = $xpath->query("descendant::*[contains(concat(' ', normalize-space(@class), ' '), ' option-translation ')]", $opt);
            $translation = '';
            if ($transNodes->length > 0) {
                $translation = trim($transNodes->item(0)->textContent);
            }
            $optionAnswers[] = [
                'label' => $label,
                'translation' => $translation
            ];
            $class = $opt->getAttribute('class') ?? '';
            if (str_contains($class, 'correct-option')) {
                $correctLabel = $label;
            }
        }
        if (!empty($correctLabel)) {
            $entry = [
                'question_number' => $num,
                'correct_answer' => $correctLabel
            ];
            if (!empty($passageTrans)) {
                $entry['passage_translation'] = $passageTrans;
            }
            if (!empty($passageEng)) {
                $entry['passage_english'] = $passageEng;
            }
            if (!empty($passageAudio)) {
                $entry['passage_audio'] = $passageAudio;
            }
            if (!empty($questionAudio)) {
                $entry['audio_url'] = $questionAudio;
            }
            $entry['options'] = $optionAnswers;
            $answers[] = $entry;
        }
    }
    return [
        'exam_title' => $title,
        'answers' => $answers
    ];
}

// upload và phân tích file zip/html đề thi
function importAdminTest() {
    checkAdminAccess();
    $isPremium = isset($_POST['is_premium']) && ($_POST['is_premium'] === '1' || $_POST['is_premium'] === 'true') ? 1 : 0;
    $isSplit = isset($_POST['import_type']) && $_POST['import_type'] === 'split';

    if ($isSplit) {
        if (empty($_FILES['listening_file']['tmp_name']) || empty($_FILES['listening_answer_file']['tmp_name']) ||
            empty($_FILES['reading_file']['tmp_name']) || empty($_FILES['reading_answer_file']['tmp_name'])) {
            sendError("Vui lòng tải lên đầy đủ file đề và đáp án của cả Listening và Reading", 400);
        }
        $listeningFile = $_FILES['listening_file']['tmp_name'];
        $listeningAnswerFile = $_FILES['listening_answer_file']['tmp_name'];
        $readingFile = $_FILES['reading_file']['tmp_name'];
        $readingAnswerFile = $_FILES['reading_answer_file']['tmp_name'];
    } else {
        if (empty($_FILES['exam_file']['tmp_name']) || empty($_FILES['answer_file']['tmp_name'])) {
            sendError("Vui lòng tải lên cả file đề thi và file đáp án", 400);
        }
        $examFile = $_FILES['exam_file']['tmp_name'];
        $answerFile = $_FILES['answer_file']['tmp_name'];
    }

    $mediaFile = $_FILES['media_file']['tmp_name'] ?? null;
    $mediaAnswerFile = $_FILES['media_answer_file']['tmp_name'] ?? null;
    $tempDir = __DIR__ . '/../../server/uploads/temp_import_' . uniqid();
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
        chmod($tempDir, 0777);
    }
    if ($mediaFile && is_uploaded_file($mediaFile)) {
        $zip = new ZipArchive();
        if ($zip->open($mediaFile) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        }
    }
    if ($mediaAnswerFile && is_uploaded_file($mediaAnswerFile)) {
        $zip = new ZipArchive();
        if ($zip->open($mediaAnswerFile) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        }
    }
    try {
        if ($isSplit) {
            $listeningHtml = file_get_contents($listeningFile);
            $listeningAnswerHtml = file_get_contents($listeningAnswerFile);
            $readingHtml = file_get_contents($readingFile);
            $readingAnswerHtml = file_get_contents($readingAnswerFile);

            $listeningData = helperParseExamHtml($listeningHtml);
            $listeningAnswerData = helperParseAnswerHtml($listeningAnswerHtml);
            $readingData = helperParseExamHtml($readingHtml);
            $readingAnswerData = helperParseAnswerHtml($readingAnswerHtml);

            // dịch chuyển số câu reading lên +100 nếu chúng bắt đầu từ <= 100
            foreach ($readingData['parts'] as &$part) {
                if (isset($part['questions'])) {
                    foreach ($part['questions'] as &$q) {
                        if ((int)$q['question_number'] <= 100) {
                            $q['question_number'] = (int)$q['question_number'] + 100;
                        }
                    }
                    unset($q);
                }
                if (isset($part['passages'])) {
                    foreach ($part['passages'] as &$passage) {
                        if (isset($passage['questions'])) {
                            foreach ($passage['questions'] as &$q) {
                                if ((int)$q['question_number'] <= 100) {
                                    $q['question_number'] = (int)$q['question_number'] + 100;
                                }
                            }
                            unset($q);
                        }
                    }
                    unset($passage);
                }
            }
            unset($part);

            if (!empty($readingAnswerData['answers'])) {
                foreach ($readingAnswerData['answers'] as &$entry) {
                    if ((int)$entry['question_number'] <= 100) {
                        $entry['question_number'] = (int)$entry['question_number'] + 100;
                    }
                }
            }
            unset($entry);

            // gộp parts và đáp án của listening + reading lại
            $combinedTitle = $listeningData['title'] ?? 'Đề thi gộp';
            $combinedTitle = str_replace(['Thi thử Listening', 'Listening', 'listening'], ['Thi thử Full', 'Full', 'full'], $combinedTitle);
            $examData = [
                'title' => $combinedTitle,
                'duration' => 7200,
                'parts' => array_merge($listeningData['parts'], $readingData['parts'])
            ];
            if (!empty($listeningData['audio_url'])) {
                $examData['audio_url'] = $listeningData['audio_url'];
            }
            $answerData = [
                'answers' => array_merge($listeningAnswerData['answers'] ?? [], $readingAnswerData['answers'] ?? [])
            ];
        } else {
            $examHtml = file_get_contents($examFile);
            $answerHtml = file_get_contents($answerFile);
            $examData = helperParseExamHtml($examHtml);
            $answerData = helperParseAnswerHtml($answerHtml);
        }

        if (empty($examData['parts'])) {
            throw new Exception("File đề thi không hợp lệ hoặc không có dữ liệu câu hỏi");
        }
        global $conn;
        $testTitle = $examData['title'] ?? 'Đề thi thử mới';
        $slug = helperSlugify($testTitle);
        $targetImageDir = __DIR__ . '/../../server/uploads/image/' . $slug;
        if (!is_dir($targetImageDir)) {
            mkdir($targetImageDir, 0777, true);
            chmod($targetImageDir, 0777);
        }
        $copyImage = function($imgName, $newBaseName) use ($slug, $targetImageDir, $tempDir) {
            if (empty($imgName)) return null;
            $ext = pathinfo($imgName, PATHINFO_EXTENSION);
            $newFileName = $newBaseName . '.' . $ext;
            $sourceFile = findFileInDirRecursive($tempDir, $imgName);
            if ($sourceFile && file_exists($sourceFile)) {
                copy($sourceFile, $targetImageDir . '/' . $newFileName);
            }
            return "/server/uploads/image/" . $slug . "/" . $newFileName;
        };
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO tests (uuid, title, description, duration, audio_url, is_premium, is_active) VALUES (UUID(), :title, :desc, :duration, :audio_url, :is_premium, 0)");
        $stmt->execute([
            'title' => $testTitle,
            'desc' => 'Đề thi import tự động, ở trạng thái chờ duyệt',
            'duration' => $examData['duration'] ?? 7200,
            'audio_url' => (!empty($examData['audio_url'])) ? "/server/uploads/audio/" . $examData['audio_url'] : null,
            'is_premium' => $isPremium
        ]);
        $testId = $conn->lastInsertId();
        $totalQuestionsCount = 0;
        foreach ($examData['parts'] as $part) {
            $partNumber = $part['part_number'] ?? 1;
            if (isset($part['questions']) && is_array($part['questions'])) {
                foreach ($part['questions'] as $q) {
                    $stmtQ = $conn->prepare("INSERT INTO questions (test_id, part, question_number, content, image_url, audio_url, correct_answer) VALUES (:test_id, :part, :question_number, :content, :image_url, :audio_url, :correct_answer)");
                    $stmtQ->execute([
                        'test_id' => $testId,
                        'part' => $partNumber,
                        'question_number' => $q['question_number'],
                        'content' => $q['content'] ?: null,
                        'image_url' => $copyImage($q['image_url'] ?? null, 'question_' . $q['question_number']),
                        'audio_url' => (!empty($q['audio_url'])) ? "/server/uploads/audio/" . $q['audio_url'] : null,
                        'correct_answer' => $q['correct_answer'] ?? 'A'
                    ]);
                    $questionId = $conn->lastInsertId();
                    $totalQuestionsCount++;
                    if (isset($q['options']) && is_array($q['options'])) {
                        $stmtOpt = $conn->prepare("INSERT INTO options (question_id, label, content) VALUES (:question_id, :label, :content)");
                        foreach ($q['options'] as $opt) {
                            $stmtOpt->execute([
                                'question_id' => $questionId,
                                'label' => $opt['label'],
                                'content' => $opt['content'] ?? ''
                            ]);
                        }
                    }
                }
            }
            if (isset($part['passages']) && is_array($part['passages'])) {
                foreach ($part['passages'] as $passage) {
                    $newBaseName = 'passage';
                    if (!empty($passage['questions'])) {
                        $nums = array_column($passage['questions'], 'question_number');
                        if (!empty($nums)) {
                            $newBaseName = 'question_' . min($nums) . '_' . max($nums);
                        }
                    }
                    $stmtPass = $conn->prepare("INSERT INTO passages (test_id, content, image_url, audio_url) VALUES (:test_id, :content, :image_url, :audio_url)");
                    $stmtPass->execute([
                        'test_id' => $testId,
                        'content' => $passage['content'] ?: null,
                        'image_url' => $copyImage($passage['image_url'] ?? null, $newBaseName),
                        'audio_url' => (!empty($passage['audio_url'])) ? "/server/uploads/audio/" . $passage['audio_url'] : null
                    ]);
                    $passageId = $conn->lastInsertId();
                    if (isset($passage['questions']) && is_array($passage['questions'])) {
                        foreach ($passage['questions'] as $q) {
                            $stmtQ = $conn->prepare("INSERT INTO questions (test_id, passage_id, part, question_number, content, image_url, audio_url, correct_answer) VALUES (:test_id, :passage_id, :part, :question_number, :content, :image_url, :audio_url, :correct_answer)");
                            $stmtQ->execute([
                                'test_id' => $testId,
                                'passage_id' => $passageId,
                                'part' => $partNumber,
                                'question_number' => $q['question_number'],
                                'content' => $q['content'] ?: null,
                                'image_url' => $copyImage($q['image_url'] ?? null, 'question_' . $q['question_number']),
                                'audio_url' => (!empty($q['audio_url'])) ? "/server/uploads/audio/" . $q['audio_url'] : null,
                                'correct_answer' => $q['correct_answer'] ?? 'A'
                            ]);
                            $questionId = $conn->lastInsertId();
                            $totalQuestionsCount++;
                            if (isset($q['options']) && is_array($q['options'])) {
                                $stmtOpt = $conn->prepare("INSERT INTO options (question_id, label, content) VALUES (:question_id, :label, :content)");
                                foreach ($q['options'] as $opt) {
                                    $stmtOpt->execute([
                                        'question_id' => $questionId,
                                        'label' => $opt['label'],
                                        'content' => $opt['content'] ?? ''
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
        $stmtUpdateTotalQ = $conn->prepare("UPDATE tests SET total_questions = ? WHERE id = ?");
        $stmtUpdateTotalQ->execute([$totalQuestionsCount, $testId]);
        if (!empty($answerData['answers'])) {
            $stmtUpdate = $conn->prepare("UPDATE questions SET correct_answer = ? WHERE test_id = ? AND question_number = ?");
            $stmtCheck = $conn->prepare("SELECT id FROM questions WHERE test_id = ? AND question_number = ? LIMIT 1");
            $stmtTranslation = $conn->prepare("UPDATE options SET translation = ? WHERE question_id = ? AND label = ?");
            $stmtPassageId = $conn->prepare("SELECT passage_id FROM questions WHERE id = ? LIMIT 1");
            $stmtUpdatePassage = $conn->prepare("UPDATE passages SET translation = ? WHERE id = ?");
            $stmtUpdatePassageEn = $conn->prepare("UPDATE passages SET translation_en = ? WHERE id = ?");
            $stmtUpdateQuestionAudio = $conn->prepare("UPDATE questions SET audio_url = ? WHERE id = ?");
            $stmtUpdatePassageAudio = $conn->prepare("UPDATE passages SET audio_url = ? WHERE id = ?");
            foreach ($answerData['answers'] as $entry) {
                $num = (int)$entry['question_number'];
                $answer = strtoupper(trim($entry['correct_answer']));
                if (!in_array($answer, ['A', 'B', 'C', 'D'])) continue;
                $stmtCheck->execute([$testId, $num]);
                $qRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if (!$qRow) continue;
                $questionId = $qRow['id'];
                $stmtUpdate->execute([$answer, $testId, $num]);
                if (!empty($entry['options'])) {
                    foreach ($entry['options'] as $opt) {
                        $label = strtoupper(trim($opt['label']));
                        $translation = trim($opt['translation'] ?? '');
                        if ($label && $translation) {
                            $stmtTranslation->execute([$translation, $questionId, $label]);
                        }
                    }
                }
                $qAudio = trim($entry['audio_url'] ?? '');
                if ($qAudio) {
                    $stmtUpdateQuestionAudio->execute([$qAudio, $questionId]);
                }
                $passageTrans = trim($entry['passage_translation'] ?? '');
                $passageEng = trim($entry['passage_english'] ?? '');
                $passageAudio = trim($entry['passage_audio'] ?? '');
                if ($passageTrans || $passageEng || $passageAudio) {
                    $stmtPassageId->execute([$questionId]);
                    $pRow = $stmtPassageId->fetch(PDO::FETCH_ASSOC);
                    if ($pRow && $pRow['passage_id']) {
                        if ($passageTrans) {
                            $stmtUpdatePassage->execute([$passageTrans, $pRow['passage_id']]);
                        }
                        if ($passageEng) {
                            $stmtUpdatePassageEn->execute([$passageEng, $pRow['passage_id']]);
                        }
                        if ($passageAudio) {
                            $stmtUpdatePassageAudio->execute([$passageAudio, $pRow['passage_id']]);
                        }
                    }
                }
            }
        }
        $conn->commit();
        helperRemoveDir($tempDir);
        sendJson([
            'success' => true,
            'message' => 'Đề thi đã được import thành công ở chế độ chờ duyệt',
            'test_id' => $testId
        ]);
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        helperRemoveDir($tempDir);
        sendError("Lỗi khi import đề thi: " . $e->getMessage(), 500);
    }
}

// duyệt và kích hoạt đề thi nháp
function activateAdminTest($uuid) {
    global $conn;
    checkAdminAccess();
    try {
        $stmt = $conn->prepare("UPDATE tests SET is_active = 1, description = NULL WHERE uuid = ?");
        $stmt->execute([$uuid]);
        if ($stmt->rowCount() > 0) {
            sendJson([
                'success' => true,
                'message' => 'Đề thi đã được duyệt và hoạt động thành công'
            ]);
        } else {
            sendError("Không tìm thấy đề thi hoặc đề đã được kích hoạt trước đó", 404);
        }
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}

