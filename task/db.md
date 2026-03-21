# Task Database

> 1 DB dev, phối hợp với FE 1 + FE 2 (cấu trúc element) và BE (kết nối)

## Tasks

| # | Task | Trạng thái | Phase | Dependencies | Ghi chú |
|---|------|-----------|-------|-------------|---------|
| 1 | Ngồi cùng FE 1 + FE 2 xác định cấu trúc element | ⬚ | 3 | - | quyết định các key: x, y, w, h, seed, ... |
| 2 | Vẽ ERD chi tiết | ⬚ | 3 | #1 | users, boards, quan hệ 1-N |
| 3 | Viết schema SQL | ⬚ | 3 | #2 | CREATE TABLE + INDEX |
| 4 | Chạy migration tạo bảng trên MySQL | ⬚ | 3 | #3 | |
| 5 | Viết config/database.php cùng BE | ⬚ | 3 | #4 | PDO connection |
| 6 | Viết models/User.php | ⬚ | 3 | #5 | findByUsername, create |
| 7 | Viết models/Board.php | ⬚ | 3 | #5 | CRUD methods |
| 8 | Test insert/select dữ liệu mẫu | ⬚ | 3 | #6, #7 | |
| 9 | Viết docs ERD vào docs/code/db/erd.md | ⬚ | 3 | #2 | |
| 10 | Viết docs schema vào docs/code/db/schema.md | ⬚ | 3 | #3 | |

## Lưu ý chiến lược lưu trữ

- content là LONGTEXT chứa JSON string `[{element1}, {element2}, ...]`
- **không** tách từng element thành row
- xem chi tiết tại docs/research/db.md

## Review chéo

- BE phải approve PR của DB trước khi merge
- ngồi cùng FE 1 + FE 2 khi quyết định cấu trúc element

## Progress

### ERD
(chưa bắt đầu)

### Schema
(chưa bắt đầu)