# Database Schema Design

> Thiết kế cơ sở dữ liệu cho nền tảng luyện thi TOEIC PrepHub

## ERD (Entity Relationship Diagram)

<img width="1309" height="1109" alt="Untitled" src="https://github.com/user-attachments/assets/bfcdb7bf-f0a9-4ab6-be88-1cfe5b034cdc" />

## Chi tiết từng bảng

> [!NOTE]
> AI là auto increment (Tự động tăng)   
> PK là primary key  
> FK là foreign key

### 1. users

> Lưu thông tin tài khoản, phân quyền và trạng thái người dùng

| Cột        | Kiểu                 | Ràng buộc                   | Mô tả                                          |
| ---------- | -------------------- | --------------------------- | ---------------------------------------------- |
| id         | INT                  | PK, AI                      | khóa chính, id nội bộ (dùng cho JOIN)          |
| uuid       | CHAR(36)             | UNIQUE, NOT NULL            | id công khai (dùng cho API response)           |
| last_name  | VARCHAR(50)          | NOT NULL                    | họ (tự lấy nếu từ Google)                      |
| first_name | VARCHAR(50)          | NOT NULL                    | tên (tự lấy nếu từ Google)                     |
| email      | VARCHAR(100)         | UNIQUE, NOT NULL            | địa chỉ email (dùng để đăng nhập)              |
| password   | VARCHAR(255)         | DEFAULT NULL                | mật khẩu hash (null nếu đăng nhập qua google)  |
| avatar     | VARCHAR(255)         | DEFAULT NULL                | đường dẫn ảnh đại diện (lấy từ google khi tạo) |
| role       | ENUM('user','admin') | DEFAULT 'user'              | phân quyền rbac                                |
| is_banned  | TINYINT(1)           | DEFAULT 0                   | cờ khóa tài khoản (admin ban)                  |
| created_at | TIMESTAMP            | DEFAULT CURRENT_TIMESTAMP   | ngày tạo                                       |
| updated_at | TIMESTAMP            | ON UPDATE CURRENT_TIMESTAMP | ngày cập nhật gần nhất                         |

**Index:** `idx_uuid` (uuid), `idx_email` (email)
> [!NOTE]
> `is_banned` chỉ xảy ra khi người dùng:
> - 1 acc Premium dùng chung nhiều IPs
> - Cào dữ liệu để lấy data bài thi (phức tạp, làm cuối)
> - Cheat điểm (VD: gửi qua API endpoint) hoặc SQLi, brute force
> - Vi phạm điều khoản thanh toán (demo nên phần thanh toán sẽ ít tập trung xử lí vi phạm)

---

### 2. tests

> Đại diện cho 1 bộ đề thi TOEIC hoàn chỉnh (200 câu, 7 parts)

| Cột             | Kiểu         | Ràng buộc                 | Mô tả                                                |
| --------------- | ------------ | ------------------------- | ---------------------------------------------------- |
| id              | INT          | PK, AI                    | khóa chính nội bộ                                    |
| uuid            | CHAR(36)     | UNIQUE, NOT NULL          | id cho url/api public                                |
| title           | VARCHAR(200) | NOT NULL                  | tên bộ đề (vd: "TOEIC Practice Test 01")             |
| description     | TEXT         | DEFAULT NULL              | mô tả ngắn về đề                                     |
| duration        | INT          | DEFAULT 7200              | thời gian làm bài tính bằng giây (mặc định 120 phút) |
| total_questions | INT          | DEFAULT 200               | tổng số câu hỏi                                      |
| is_premium      | TINYINT(1)   | DEFAULT 0                 | đề miễn phí hay trả phí                              |
| is_active       | TINYINT(1)   | DEFAULT 1                 | ẩn/hiện đề trên danh sách                            |
| created_at      | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | ngày tạo                                             |

**Index:** `idx_uuid` (uuid), `idx_premium_active` (is_premium, is_active)

---

### 3. passages

> Đoạn văn/đoạn hội thoại dùng chung cho nhóm câu hỏi (part 3, 4, 6, 7)
>
> Bảng `passages` tách riêng để nhiều câu hỏi cùng trỏ chung 1 đoạn văn hoặc 1 file audio, tránh lặp data


| Cột       | Kiểu         | Ràng buộc     | Mô tả                         |
| --------- | ------------ | ------------- | ----------------------------- |
| id        | INT          | PK, AI        | khóa chính                    |
| test_id   | INT          | FK > tests.id | thuộc đề nào                  |
| content   | TEXT         | DEFAULT NULL  | nội dung đoạn văn (part 6, 7) |
| audio_url | VARCHAR(255) | DEFAULT NULL  | link file mp3 (part 3, 4)     |
| image_url | VARCHAR(255) | DEFAULT NULL  | link ảnh minh họa (part 1)    |

---

### 4. questions

> Từng câu hỏi riêng lẻ, gắn vào 1 part cụ thể của đề thi

| Cột             | Kiểu         | Ràng buộc                  | Mô tả                                   |
| --------------- | ------------ | -------------------------- | --------------------------------------- |
| id              | INT          | PK, AI                     | khóa chính                              |
| test_id         | INT          | FK > tests.id              | thuộc đề nào                            |
| passage_id      | INT          | FK > passages.id, NULLABLE | thuộc đoạn văn nào (null nếu câu đơn)   |
| part            | TINYINT      | NOT NULL (1-7)             | part toeic (1 đến 7)                    |
| question_number | INT          | NOT NULL                   | số thứ tự câu hỏi trong đề (1-200)      |
| content         | TEXT         | DEFAULT NULL               | nội dung câu hỏi (part 2,5,6,7 có text) |
| audio_url       | VARCHAR(255) | DEFAULT NULL               | link audio riêng cho câu (part 1, 2)    |
| image_url       | VARCHAR(255) | DEFAULT NULL               | link ảnh riêng cho câu (part 1)         |
| correct_answer  | CHAR(1)      | NOT NULL                   | đáp án đúng (A/B/C/D)                   |
| explanation     | TEXT         | DEFAULT NULL               | giải thích đáp án (hiển thị khi review) |

**Index:** `idx_test_part` (test_id, part), `idx_test_number` (test_id, question_number)

---

### 5. options

> Các lựa chọn A/B/C/D/... cho mỗi câu hỏi
>
> Câu hỏi TOEIC không chỉ có mỗi ABCD, mà tuỳ theo part có số lượng lựa chọn khác nhau

| Cột         | Kiểu    | Ràng buộc         | Mô tả             |
| ----------- | ------- | ----------------- | ----------------- |
| id          | INT     | PK, AI            | khóa chính        |
| question_id | INT     | FK > questions.id | thuộc câu hỏi nào |
| label       | CHAR(1) | NOT NULL          | nhãn đáp án       |
| content     | TEXT    | NOT NULL          | nội dung lựa chọn |

**Index:** `idx_question` (question_id)

---

### 6. attempts

> Mỗi lần user làm xong 1 bộ đề = 1 record attempt
>
> `attempts` chia rõ điểm listening và reading riêng biệt trước khi cộng tổng, đúng chuẩn format toeic quốc tế

| Cột               | Kiểu      | Ràng buộc                 | Mô tả                                       |
| ----------------- | --------- | ------------------------- | ------------------------------------------- |
| id                | INT       | PK, AI                    | khóa chính nội bộ                           |
| uuid              | CHAR(36)  | UNIQUE, NOT NULL          | id cho api public (vd: /api/attempts/:uuid) |
| user_id           | INT       | FK > users.id             | ai làm                                      |
| test_id           | INT       | FK > tests.id             | làm đề nào                                  |
| listening_correct | INT       | DEFAULT 0                 | tổng câu đúng phần listening (part 1-4)     |
| reading_correct   | INT       | DEFAULT 0                 | tổng câu đúng phần reading (part 5-7)       |
| listening_score   | INT       | DEFAULT 0                 | điểm listening sau mapping (5-495)          |
| reading_score     | INT       | DEFAULT 0                 | điểm reading sau mapping (5-495)            |
| total_score       | INT       | DEFAULT 0                 | tổng điểm toeic (10-990)                    |
| time_spent        | INT       | DEFAULT 0                 | thời gian thực tế đã dùng (giây)            |
| created_at        | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | thời điểm nộp bài                           |

**Index:** `idx_uuid` (uuid), `idx_user_created` (user_id, created_at DESC), `idx_test` (test_id)

> [!NOTE]
> Bảng này nhằm để lưu lại thành tích cuối cùng  
> Giúp hiển thị lịch sử, thời gian làm bài, vẽ biểu đồ tăng trường điểm  
> Phù hợp cho việc phân tích tổng quát qua các đề thi

---

### 7. attempt_answers

> Chi tiết từng câu trả lời của user trong 1 lần attempt (phục vụ review)

| Cột             | Kiểu       | Ràng buộc         | Mô tả                                    |
| --------------- | ---------- | ----------------- | ---------------------------------------- |
| id              | INT        | PK, AI            | khóa chính                               |
| attempt_id      | INT        | FK > attempts.id  | thuộc lần làm bài nào                    |
| question_id     | INT        | FK > questions.id | câu hỏi nào                              |
| selected_answer | CHAR(1)    | DEFAULT NULL      | đáp án user đã chọn (null = bỏ trống)    |
| is_correct      | TINYINT(1) | NOT NULL          | đúng hay sai (server tự tính khi submit) |

**Index:** `idx_attempt` (attempt_id)

> [!NOTE]
> Bảng này nhằm để lưu lại các câu đúng sai để sau này phân tích thí sinh hay sai part nào nhất  
> Ngoài ra nhờ đó mà ta còn biết câu nào các thí sinh hay sai để đánh dấu là câu khó  
> Giúp thí sinh xem lại bài làm của mình ở các câu sai (dùng `explanation` ở bảng `question`)

---

### 8. payments

> Ghi nhận giao dịch mở khóa đề premium (demo, dùng VietQR)
>
> Hiện tại xây dựng mức cơ bản, sau này sẽ mở rộng cho VietQR

| Cột        | Kiểu      | Ràng buộc                 | Mô tả         |
| ---------- | --------- | ------------------------- | ------------- |
| id         | INT       | PK, AI                    | khóa chính    |
| user_id    | INT       | FK > users.id             | ai mua        |
| test_id    | INT       | FK > tests.id             | mua đề nào    |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | thời điểm mua |

**Unique:** `uq_user_test` (user_id, test_id) ngăn mua trùng lặp

---

### 9. oauth_accounts

> Liên kết tài khoản Google với user nội bộ (hỗ trợ đăng nhập bằng Google OAuth 2.0)
>
> Khi user login bằng Google lần đầu, hệ thống tự tạo record `users` mới với `last_name`, `first_name`, `email`, `avatar` kéo từ Google profile. Lần sau login lại chỉ cần tra `google_id` là xong

| Cột        | Kiểu         | Ràng buộc                 | Mô tả                                       |
| ---------- | ------------ | ------------------------- | ------------------------------------------- |
| id         | INT          | PK, AI                    | khóa chính                                  |
| user_id    | INT          | FK > users.id, UNIQUE     | liên kết 1-1 với user nội bộ                |
| google_id  | VARCHAR(255) | UNIQUE, NOT NULL          | id tài khoản google (sub claim từ id_token) |
| created_at | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP | ngày liên kết                               |

**Index:** `idx_google_id` (google_id)

---

## Quan hệ giữa các bảng (tóm tắt)

| Bảng A    | Quan hệ | Bảng B          | Giải thích                                |
| --------- | ------- | --------------- | ----------------------------------------- |
| users     | 1 > N   | attempts        | 1 user làm nhiều lần thi                  |
| users     | 1 > N   | payments        | 1 user mua nhiều đề                       |
| tests     | 1 > N   | questions       | 1 đề chứa 200 câu hỏi                     |
| tests     | 1 > N   | passages        | 1 đề chứa nhiều đoạn văn/audio            |
| tests     | 1 > N   | attempts        | 1 đề được nhiều người thi                 |
| passages  | 1 > N   | questions       | 1 đoạn văn gom nhóm nhiều câu             |
| questions | 1 > N   | options         | 1 câu có 4 lựa chọn A/B/C/D               |
| attempts  | 1 > N   | attempt_answers | 1 lần thi lưu chi tiết 200 câu trả lời    |
| users     | 1 > 1   | oauth_accounts  | 1 user có thể liên kết 1 tài khoản google |

## Chống trùng lặp file

Thay vì nhúng trực tiếp blob file vào database, hệ thống lưu trữ kiểu **path ref**:
- File mp3/ảnh được lưu trên cloud
- Cột `audio_url` / `image_url` chỉ lưu đường dẫn tương đối
- Nhiều câu hỏi cùng part 3 hoặc part 4 có thể trỏ chung 1 file audio thông qua bảng `passages`

## Primary Key (INT + UUID kết hợp)

INT làm khóa chính nội bộ, UUID làm định danh công khai

**Nguyên tắc hoạt động:**
- Mọi câu lệnh `JOIN`, `WHERE` nội bộ giữa các bảng đều chạy trên cột `id` (INT) để tận dụng tối đa tốc độ B-Tree index nhỏ gọn 4 bytes
- Khi API trả dữ liệu ra frontend hoặc hiển thị trên URL, hệ thống dùng cột `uuid` (CHAR 36) thay thế, người dùng không bao giờ nhìn thấy id số nguyên
- UUID được sinh tự động bằng hàm `UUID()` của MySQL hoặc `bin2hex(random_bytes(16))` từ PHP ngay tại thời điểm INSERT

**Bảng được gắn UUID:**

| Bảng     | Lý do cần UUID                                           |
| -------- | -------------------------------------------------------- |
| users    | trả về trong token payload và api `/api/auth/me`         |
| tests    | hiển thị trên url `/api/tests/:uuid` tránh lộ tổng số đề |
| attempts | đường dẫn xem kết quả `/api/attempts/:uuid`              |

**Bảng giữ nguyên INT (không cần UUID):**

| Bảng            | Lý do                                                                      |
| --------------- | -------------------------------------------------------------------------- |
| questions       | không bao giờ truy cập trực tiếp qua url, luôn nằm trong response của test |
| options         | dữ liệu con của question, frontend không gọi riêng                         |
| passages        | chỉ dùng nội bộ để gom nhóm câu hỏi                                        |
| attempt_answers | dữ liệu con của attempt, trả kèm trong response chi tiết                   |
| payments        | chỉ kiểm tra nội bộ qua user_id + test_id, không expose ra ngoài           |
