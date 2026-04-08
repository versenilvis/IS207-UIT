<?php
// xử lí logic api lấy đề thi, danh sách câu hỏi

// cái này để auto complete cho code editor thôi
// tức là cho editor biết $conn có kiểu PDO để gợi ý method nhanh hơn
/**
 * @var PDO $conn
 */

// lấy full list các đề thi
function getTestList() {
    global $conn;
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user_id = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? 'user';


        $stmt = $conn->prepare("SELECT id, title, is_premium, total_questions FROM tests WHERE is_active = 1 ORDER BY id DESC");
        $stmt->execute();
        $tests = $stmt->fetchAll();

        // lấy danh sách ID các bài test đã mua
        $paid_map = [];
		// vì admin đã auto unlock hết nên không cần check cho admin nữa
        if ($user_id && $role !== 'admin') {
            $stmt_pay = $conn->prepare("SELECT test_id FROM payments WHERE user_id = :uid");
            $stmt_pay->execute(['uid' => $user_id]);
            $paid_map = $stmt_pay->fetchAll(PDO::FETCH_COLUMN);
			// array trong php nó có dạng là key => value
			// lật ngược lại sẽ là value => key
			// nếu mình giữ nguyên như ban đầu mà không lật thì chỉ có thể check theo key, 
			// mà key thì không phải giá trị cần tìm
			// Ví dụ như: trong mảng ta có 101, nếu ta tìm paid_map[101] thì nó sẽ trả về false
			// vì nó tìm tới index thứ 101 chứ không phải giá trị 101 đã có
			// khi ta lật lại, value thành key, check key tìm paid_map[101] -> tồn tại
			// khi đó mình sẽ dùng isset($paid_map[$test_id]), nó như hash map vậy
            $paid_map = array_flip($paid_map);
        }

        $formatted_tests = [];
        foreach ($tests as $test) {
            $test_id = (int)$test['id'];
            $is_premium = (bool)$test['is_premium'];
            
            // hiện unlock full nếu là admin
            // hiện unlock một vài đề free nếu là đề free
            // hiện unlock cho các đề đã mua nếu là đề premium nhưng user đã mua (có trong paid_map)
            $is_unlocked = ($role === 'admin') || !$is_premium || isset($paid_map[$test_id]);

            $formatted_tests[] = [
                'id' => $test_id,
                'title' => $test['title'],
                'is_premium' => $is_premium,
                'is_unlocked' => $is_unlocked
            ];
        }

        sendJson(["tests" => $formatted_tests]);
    } catch (PDOException $e) {
        sendError($e->getMessage(), 500);
    }
}

function getTestCore($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, uuid, title, duration, is_premium FROM tests WHERE id = :id AND is_active = 1");
        $stmt->execute(['id' => $id]);
        $test = $stmt->fetch();

        if (!$test) {
            sendError("Không tìm thấy đề", 404);
        }

        // check premium
        if ($test['is_premium']) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_id = $_SESSION['user_id'] ?? null;
            $role = $_SESSION['role'] ?? 'user';

            if ($role !== 'admin') {
                $stmt_pay = $conn->prepare("SELECT id FROM payments WHERE user_id = :uid AND test_id = :tid");
                $stmt_pay->execute(['uid' => $user_id, 'tid' => $id]);
                if (!$stmt_pay->fetch()) {
                    sendError("Forbidden: Bạn cần mua hoặc mở khóa đề thi này để tiếp tục", 403);
                }
            }
        }

        // JS có 1 vấn đề như sau
		// PDO nó luôn trả về string
		// nếu không ép kiểu sang bool chẳng hạn mà check if (is_premium) trong JS thì "0" nó sẽ luôn là True
        $test['id'] = (int)$test['id'];
        $test['duration'] = (int)$test['duration'];
        $test['is_premium'] = (bool)$test['is_premium'];

		// như trong thiết kế của database, ta tách riêng đoạn văn và câu hỏi thường ra
		// tương tự với img, audio của chúng
		// đầu tiên mình sẽ query lấy toàn các câu hỏi của đề

		// giải thích 1 chút về left join
		// vì không phải part nào cũng có đoạn văn, nếu dùng join nó sẽ lấy các cột có chung ở 2 bảng
		// vơi left join, những câu nào ở question có mà passage không có thì vẫn được giữ lại
        $stmt_q = $conn->prepare("
            SELECT q.id, q.part, q.question_number, q.content, q.audio_url, q.image_url,
                   p.content as paragraph, p.audio_url as passage_audio, p.image_url as passage_image
            FROM questions q
            LEFT JOIN passages p ON q.passage_id = p.id
            WHERE q.test_id = :test_id
            ORDER BY q.part ASC, q.question_number ASC
        ");
        $stmt_q->execute(['test_id' => $id]);
        $questions = $stmt_q->fetchAll();

		// lấy hết tất cả đáp áp mỗi câu hỏi
        $stmt_o = $conn->prepare("
            SELECT o.id, o.question_id, o.label, o.content
            FROM options o
            JOIN questions q ON o.question_id = q.id
            WHERE q.test_id = :test_id
            ORDER BY o.label ASC
        ");
        $stmt_o->execute(['test_id' => $id]);
        $options = $stmt_o->fetchAll();

        // đưa các đáp án vào hash map dựa trên question_id để dùng index
        $options_by_qid = [];
        foreach ($options as $opt) {
            $q_id = $opt['question_id'];
            if (!isset($options_by_qid[$q_id])) {
                $options_by_qid[$q_id] = [];
            }
            
            // bỏ question_id vì thừa
            unset($opt['question_id']);
            $opt['id'] = (int)$opt['id'];
            
			// cách viết tắt của push vào cuối mảng
            $options_by_qid[$q_id][] = $opt;
        }

        // merge đáp án vào list câu hỏi
        foreach ($questions as &$q) {
            $q['id'] = (int)$q['id'];
            $q['part'] = (int)$q['part'];
            $q['question_number'] = (int)$q['question_number'];
			// lấy ID hịện tại theo vòng lặp $q['id']
			// sau đó lôi value của map option_by_id với key là $q['id']
			// gán vào $q['options']
			// ?? [] nhằm mục đích, nếu câu này trong đề chưa có đáp án thì trả về rỗng
            $q['options'] = $options_by_qid[$q['id']] ?? [];
        }
		// unset($q) để tránh lỗi reference khi loop
		unset($q);
        
        $test['questions'] = $questions;

        sendJson(["test" => $test]);
    } catch (PDOException $e) {
        sendError($e->getMessage(), 500);
    }
}
