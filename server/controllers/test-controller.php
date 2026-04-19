<?php
// xử lí logic api lấy đề thi, danh sách câu hỏi

// cái này để auto complete cho code editor thôi
// tức là cho editor biết $conn có kiểu PDO để gợi ý method nhanh hơn
/**
 * @var PDO $conn
 */

// lấy full list các đề thi dùng query trong database
// NOTE: nó sẽ trả về data qua endpoint /api/tests, được điều khiển thông qua router (mở folder route để xem)
// 	hàm này trả về data dạng như này:
/* {
{
    "data": [
        {
            "created_at": "2026-04-19 08:30:37",
            "description": "Listening section practice test with 100 questions",
            "duration": 2700,
            "id": "0adf09c9-3bca-11f1-af63-3eb4cc0b50c0",
            "is_active": 1,
            "is_premium": false,
            "is_unlocked": true,
            "title": "TOEIC Listening Mock Test 1",
            "total_questions": 100
        },
        {
            "created_at": "2026-04-19 08:29:37",
            "description": "Another full practice test",
            "duration": 7200,
            "id": "e6ce9c58-3bc9-11f1-af63-3eb4cc0b50c0",
            "is_active": 1,
            "is_premium": false,
            "is_unlocked": true,
            "title": "TOEIC Practice Test 2",
            "total_questions": 200
        }
    ],
    "success": true
}
*/
function getTestList() {
    global $conn;
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user_id = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? 'user';


        // Lấy tất cả (cả ẩn và hiện) để admin quản lý
        $stmt = $conn->prepare("SELECT * FROM tests ORDER BY id DESC");
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
                //Sửa id thành uuid
                'uuid' => $test['uuid'], // Trả về UUID làm định danh công khai
                'title' => $test['title'],
                'description' => $test['description'],
                'duration' => $test['duration'],
                'total_questions' => $test['total_questions'],
                'is_premium' => $is_premium,
                'is_active' => (int)$test['is_active'],
                'created_at' => $test['created_at'],
                'is_unlocked' => $is_unlocked
            ];
        }

        sendJson([
            "success" => true,
            "data" => $formatted_tests
        ]);
    } catch (PDOException $e) {
        sendError($e->getMessage(), 500);
    }
}

// tạo một bài test mới
// gửi 1 POST request qua api/tests để tạo đề thi
// LƯU Ý: là tạo đề thi chứ không phải thêm câu hỏi và câu trả lời vào đề thi
// hàm này chính là hàm trả xử lí cho handleCreateTestSubmit() ở file questions/api.js
function createTest() {
    global $conn;
    try {
        // Hỗ trợ cả dữ liệu gửi từ Form hoặc từ JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        $title = trim($_POST['title'] ?? $input['title'] ?? '');
        $description = trim($_POST['description'] ?? $input['description'] ?? '');
        $is_premium = isset($_POST['is_premium']) ? 1 : ($input['is_premium'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : ($input['is_active'] ?? 1);

        if (empty($title)) {
            sendError("Tiêu đề không được để trống", 400);
        }

        // Kiểm tra tiêu đề trùng lặp
        $stmt_check = $conn->prepare("SELECT id FROM tests WHERE title = :title LIMIT 1");
        $stmt_check->execute(['title' => $title]);
        if ($stmt_check->fetch()) {
            sendError("Tiêu đề đề thi '{$title}' đã tồn tại, vui lòng chọn tên khác", 400);
        }

        // Tạo đề thi mới với UUID tự động từ MySQL
        $stmt = $conn->prepare("
            INSERT INTO tests (uuid, title, description, is_premium, is_active) 
            VALUES (UUID(), :title, :description, :is_premium, :is_active)
        ");
        
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'is_premium' => $is_premium,
            'is_active' => $is_active
        ]);

        $new_id = $conn->lastInsertId();

        // Lấy lại thông tin đề vừa tạo (để lấy được UUID)
        $stmt_get = $conn->prepare("SELECT uuid FROM tests WHERE id = ?");
        $stmt_get->execute([$new_id]);
        $test_info = $stmt_get->fetch();

        sendJson([
            "success" => true,
            "message" => "Tạo đề thi thành công",
            "data" => [
                "id" => $test_info['uuid'],
                "title" => $title
            ]
        ]);
    } catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}


// lấy data 1 đề cụ thể, query trực tiếp vào database để lấy đề ra
// NOTE: nó sẽ trả về data qua endpoint /api/tests/uuid, được điều khiển thông qua router (mở folder route để xem)
function getTestCore($uuid) {
    global $conn;
    try {
        // Tìm đề thi theo UUID
        $stmt = $conn->prepare("SELECT id, uuid, title, duration, is_premium FROM tests WHERE uuid = :uuid AND is_active = 1");
        $stmt->execute(['uuid' => $uuid]);
        $test = $stmt->fetch();

        if (!$test) {
            sendError("Không tìm thấy đề", 404);
        }

		// Lấy ID nội bộ để đi JOIN các bảng khác, vì theo thiết kế ta dùng ID để 
		// JOIN, WHERE, nói chung để query và dùng UUID để show ra người dùng
        $internal_id = (int)$test['id']; 

        // check premium
        /*
        if ($test['is_premium']) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_id = $_SESSION['user_id'] ?? null;
            $role = $_SESSION['role'] ?? 'user';

            if ($role !== 'admin') {
                $stmt_pay = $conn->prepare("SELECT id FROM payments WHERE user_id = :uid AND test_id = :tid");
                $stmt_pay->execute(['uid' => $user_id, 'tid' => $internal_id]);
                if (!$stmt_pay->fetch()) {
                    sendError("Forbidden: Bạn cần mua hoặc mở khóa đề thi này để tiếp tục", 403);
                }
            }
        }
        */
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
        $stmt_q->execute(['test_id' => $internal_id]);
        $questions = $stmt_q->fetchAll();

		// lấy hết tất cả đáp áp mỗi câu hỏi
        $stmt_o = $conn->prepare("
            SELECT o.id, o.question_id, o.label, o.content
            FROM options o
            JOIN questions q ON o.question_id = q.id
            WHERE q.test_id = :test_id
            ORDER BY o.label ASC
        ");
        $stmt_o->execute(['test_id' => $internal_id]);
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

        sendJson([
            "success" => true,
            "data" => $test
        ]);
    } catch (PDOException $e) {
        sendError($e->getMessage(), 500);
    }
}
