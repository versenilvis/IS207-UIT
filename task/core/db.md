# task database

> 1 db dev, phối hợp với các dev fe và be khác

## tasks

| # | Task | Trạng thái | Phase | Dependencies | Ghi chú |
|---|------|-----------|-------|-------------|---------|
| 1 | chốt kiến trúc các module đề thi (question, passage, part) | ⬚ | 1 | - | quyết định cách lưu group_id, liên kết tệp media để không lặp dữ liệu |
| 2 | vẽ erd chi tiết core mảng người dùng và làm bài | ⬚ | 1 | #1 | users (role, is_banned, avatar), tests, questions, options, attempts |
| 3 | bổ sung erd cho mảng tính năng phụ (vip) | ⬚ | 1 | #2 | thiết kế bảng quản lý giao dịch thanh toán (payments) |
| 4 | viết schema sql hoàn chỉnh | ⬚ | 1 | #3 | create table + đánh index (đặc biệt index cho hệ thống list đề thi giúp phân trang nhanh) |
| 5 | chạy file init tạo bảng mysql vào docker | ⬚ | 1 | #4 | |
| 6 | viết file config kết nối database (dùng class pdo) | ⬚ | 1 | #5 | |
| 7 | dựng models entity: `User.php`, `Payment.php` | ⬚ | 1 | #6 | viết các logic sql crud, update profile, query khóa (ban) user |
| 8 | dựng models entity: `Test.php`, `Question.php`, `Attempt.php` | ⬚ | 1 | #6 | cụm lệnh query lấy danh sách đề, hàm tính phân trang, câu filter dữ liệu |
| 9 | test lệnh insert/select dữ liệu (với file excel mẫu) | ⬚ | 1 | #7, #8 | |
| 10| đóng gói viết docs erd database lưu sang `docs/research/db-schema.md` | ⬚ | 1 | #4 | |

## lưu ý chiến lược lưu trữ hệ thống

- xây dựng thiết kế bảng schema dạng module rời rạc để chống nhân bản record thừa: part 1 tới part 4 phải trỏ chung link tệp media url
- bảng `users` được chỉ cấu hình khai báo sẵn các field `avatar`, `role` (phân định admin/user) và cờ `is_banned` phục vụ đúng cho cụm chức năng auth nâng cao
- bảng kết quả `attempts` yêu cầu kỹ thuật phải index các cột `user_id` và `created_at` từ ban đầu để support thuật toán truy xuất bộ lọc danh sách trôi chảy
- bảng hệ đề thi `tests` bắt buộc thiết lập cột true/false `is_premium` hỗ trợ api chốt chặn luồng fake payment

## process review chéo

- nhóm dev nhánh backend bắt buộc rà soát logic duyệt qua code database pull request trước khi cấp quyền merge
- phải thống nhất quy chuẩn hiển thị table với nhóm dev layout json (tiến tới chốt cấu trúc media map vào layout)