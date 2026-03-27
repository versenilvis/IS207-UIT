# task client (frontend)

> phân chia luồng làm việc cho team frontend (bootstrap + js)

## tasks

| # | Task | Trạng thái | Assignee | Ghi chú |
|---|------|-----------|----------|---------|
| 1 | dựng base layout html (header, footer) | ⬚ | chương | bootstrap 5 |
| 2 | code trang chức năng auth (login, register) | ⬚ | chương | fetch json |
| 3 | dựng ui list danh sách đề thi (với mockup) | ⬚ | hoàng | flex/grid |
| 4 | dựng trang làm bài (exam ui) với sidebar đếm giờ | ⬚ | hoàng | js timer |
| 5 | tích hợp audio player cho part 1-4 | ⬚ | hoàng | html5 audio |
| 6 | tạo trang result chi tiết hiển thị câu đúng/sai | ⬚ | nhân | highlight xanh/đỏ |
| 7 | dựng ui dashboard thống kê điểm số | ⬚ | nhân | table chart |
| 8 | tích hợp ghép nối api lấy đề & nộp bài | ⬚ | chương | |
| 9 | xây form cho admin (nhập excel/json create test) | ⬚ | khang | validate client |

## quy chuẩn

- tách component ra các file module `.js` riêng nạp vào `main.js`
- không dùng inline style, dùng file `main.css` chung để dễ override custom layout
- fetch errors phải hiển thị modal/toast báo lỗi cho người dùng