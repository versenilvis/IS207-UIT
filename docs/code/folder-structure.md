# project folder structure

prephub/
├── client/                # frontend (vanilla js + bootstrap)
│   ├── .gitignore
│   ├── .prettierrc
│   ├── package.json       # cấu hình dependencies
│   ├── bun.lock           # bunjs lock file
│   ├── index.html         # entry point (trang chủ)
│   ├── src/
│   │   ├── main.js        # khởi tạo app, router đơn giản chuyển trang
│   │   ├── api.js         # file dùng chung để gọi fetch/axios lên server php
│   │   ├── exam.js        # logic thi toeic (thời gian, highlight, chuyển câu)
│   │   ├── admin.js       # logic dashboard admin (form tạo đề, import json/excel)
│   │   ├── auth.js        # logic login, register, lưu auth token
│   │   └── components/    # ui components (sidebar, modal kết quả, part 1-7)
│   ├── styles/
│   │   └── main.css       # bootstrap custom, styles cho highlight, đáp án
│   └── assets/
│       ├── audios/        # file mp3 listening part 1-4
│       └── images/        # ảnh part 1, part 3, 4, 7
│
├── server/                # backend api (php thuần)
│   ├── Dockerfile         # file thiết lập môi trường php-apache
│   ├── index.php          # entry point & api router (điều hướng request)
│   ├── config/
│   │   └── database.php   # kết nối pdo mysql
│   ├── controllers/       # xử lý logic các api endpoint
│   │   ├── auth-controller.php
│   │   ├── question-controller.php
│   │   └── score-controller.php
│   ├── db/                # chứa code migration hoặc dump SQL tự nạp
│   ├── models/            # tương tác trực tiếp với db thông qua pdo
│   │   ├── user.php
│   │   ├── test.php
│   │   ├── question.php
│   │   ├── attempt.php
│   │   └── payment.php    # đối tượng xử lý việc check khóa đề vip
│   ├── middleware/        # kiểm tra vòng bảo mật token, phân quyền rbac
│   │   └── auth.php
│   └── utils/             # response json & helpers
│       ├── response.php
│       └── validator.php
│
├── docs/                  # tài liệu dự án tổng hợp
│   ├── README.md          # mục lục thư mục docs
│   ├── code/
│   │   ├── folder-structure.md # sơ đồ cây thư mục đang xem này
│   │   ├── PROMPT.md           # form mẫu xài ai sinh docs
│   │   └── TEMPLATE.md         # bản base chuẩn cho cấu trúc docs
│   ├── guide/             # thư viện các quy tắc chuẩn làm việc cho team
│   │   ├── README.md           # guide tổng cách clone source và set up
│   │   ├── commit.md           # chuẩn đánh dẫu chữ github commit
│   │   ├── docs.md             # chuẩn quy tắc viết document cho team
│   │   ├── pull-request.md     # chuẩn workflow đẩy code
│   │   └── workflow.md         # luồng thao tác kéo code origin/upstream
│   └── plan/              # các tài liệu hoạch định architecture và system tính năng
│       ├── features.md         # bảng checklist liệt kê tính năng của web
│       └── system.md           # mô hình component flow user và server
│
├── task/                  # khu vực theo dõi phân đồ tiến độ công việc
│   ├── README.md          # list khó khăn chung, phân bổ khối lượng, deadline
│   ├── core/              # bản vẽ thiết kế nền tảng cho lập trình viên
│   │   ├── api.md
│   │   ├── client.md
│   │   ├── db.md
│   │   └── server.md
│   ├── p1.md              # log công việc và tick cho backend/db
│   ├── p2.md              # log công việc exam ui 
│   ├── p3.md              # log công việc form admin ui
│   ├── p4.md              # log công việc module scoring tự chấm 
│   └── p5.md              # log công việc mảng thanh toán
│
├── docker-compose.yml     # file tổng quản tự động dựng 3 node mysql, php và bun
├── Makefile               # tệp config tổ hợp chuỗi phím rảnh tay (make up, make down)
├── .env.example           # form mẫu config credentials chia sẻ cho git
├── .env                   # cục bộ local của bạn, chứa thông số đã ghi thật
├── .editorconfig          # config gò file ép xuống các dev chung
└── .gitignore             # loại trừ che giấu file .env thật tránh dính rò rỉ mã bảo mật
