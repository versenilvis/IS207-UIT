# task server (backend)

> phân chia luồng làm việc cho backend php

## tasks

| # | Task | Trạng thái | Assignee | Ghi chú |
|---|------|-----------|----------|---------|
| 1 | dựng boilerplate index.php và router cơ bản | ⬚ | hoàng | route match |
| 2 | tạo middleware auth và jwt/session handler | ⬚ | hoàng | |
| 3 | code auth controller (login/register) | ⬚ | hoàng | password_hash |
| 4 | implement test controller lấy list đề | ⬚ | hoàng | sql selects |
| 5 | implement get chi tiết đề có query join questions | ⬚ | hoàng | optimize n+1 |
| 6 | code api nộp bài và chấm điểm score module | ⬚ | nhân | mapping log |
| 7 | code dashboard history endpoints | ⬚ | nhân | |
| 8 | code admin import parsing json/excel test | ⬚ | khang | validate format |
| 9 | setup fake payment barrier/logic unlock | ⬚ | chương | optional mvp |

## quy chuẩn

- controller chỉ trả json sử dụng reresponse format chung
- validate dữ liệu cẩn thận chống sql injection trước khi đưa vào model (dùng prepare statement)
- bảo mật file mp3: api stream cấm truy cập thư mục trực tiếp nếu cần thiết