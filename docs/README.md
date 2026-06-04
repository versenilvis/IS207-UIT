# Mục lục tổng tài liệu dự án PrepHub

## 1. Plan (kiến trúc & thiết kế)

| File | Mô tả |
|------|--------|
| [system.md](plan/system.md) | Kiến trúc tổng thể hệ thống, sơ đồ C-S, API overview và luồng xử lý nghiệp vụ chung |
| [features.md](plan/features.md) | Bảng phân rã chi tiết module và trọn bộ checklist nhóm tính năng cần làm |

## 2. Guide (quy tắc làm việc của team)

| File | Mô tả |
|------|--------|
| [README.md](guide/README.md) | Hướng dẫn chung phương thức pull nhánh khởi tạo hệ thống môi trường lần đầu |
| [db.md](guide/db.md) | **Hướng dẫn kết nối Database, dùng Docker & quy trình đồng bộ Master** |
| [workflow.md](guide/workflow.md) | Chi tiết luồng thao tác kéo push code upstream cá nhân |
| [commit.md](guide/commit.md) | Chuẩn quy ước cấu trúc ghi chú chữ commit trên github |
| [pull-request.md](guide/pull-request.md) | Luật bắt buộc khi tạo PR và cách thức ngăn merge thẳng dính conflict vào dev/main |
| [boxicons.md](guide/boxicons.md) | Hướng dẫn sử dụng và tích hợp Icon set cho giao diện |
| [docs.md](guide/docs.md) | Các quy định ngầm định cách hành văn khi giải nghĩa code |

## 3. Code docs (giải nghĩa hệ thống thư mục)

| File | Mô tả |
|------|--------|
| [folder-structure.md](code/folder-structure.md) | Bản đồ chi tiết vẽ cây ý nghĩa các thư mục dự án tính tới hiện tại |
| [TEMPLATE.md](code/TEMPLATE.md) | Bộ form chuẩn giúp mọi người copy tự viết docs lý giải chức năng hàm do mình code |
| [PROMPT.md](code/PROMPT.md) | Câu lệnh nhắc (prompts) mẫu dành để đưa vào AI nhờ sinh code docs tự động |
