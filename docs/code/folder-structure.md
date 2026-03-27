# Project folder structure

whiteboard/
├── client/                # frontend (vanilla js + bootstrap)
│   ├── index.html         # entry point
│   ├── src/
│   │   ├── main.js        # khởi tạo app & event listeners
│   │   ├── canvas.js      # core logic vẽ (rough.js, render loop)
│   │   ├── state.js       # quản lí elements[], history, selectedId
│   │   ├── math.js        # thuật toán hit testing, coordinates
│   │   ├── api.js         # các hàm fetch gọi lên server
│   │   ├── components/    # ui components (toolbar, sidebar, v.v)
│   │   └── utils/         # helper functions (uuid, debounce, v.v)
│   ├── styles/
│   │   └── main.css       # bootstrap & custom styles
│   ├── package.json       # quản lí dependencies (rough.js, bootstrap)
│   └── bun.lockb          # bunjs
│
├── server/                # backend (php thuần)
│   ├── index.php          # entry point & router
│   ├── config/
│   │   └── database.php   # kết nối pdo mysql
│   ├── controllers/       # xử lí logic api
│   │   ├── AuthController.php
│   │   └── BoardController.php
│   ├── models/            # làm việc trực tiếp với database
│   │   ├── User.php
│   │   └── Board.php
│   ├── middleware/        # kiểm tra auth, validate
│   │   └── AuthMiddleware.php
│   └── utils/             # response & validator helpers
│       ├── Response.php
│       └── Validator.php
│
├── docs/                  # tài liệu dự án (đã hoàn thành)
│   ├── README.md          # mục lục tổng
│   ├── research/          # kiến thức nền (system, frontend, backend, db)
│   ├── guide/             # quy tắc (commit, pr, general, docs)
│   └── code/              # template & prompt viết docs cho hàm
│
├── task/                  # quản lí tiến độ (đã hoàn thành)
│   ├── general.md         # checklist phase & xoay tua
│   ├── api.md             # thiết kế bảng endpoint chi tiết
│   ├── client.md          # task cho 3 fe dev
│   ├── server.md          # task cho be dev
│   └── db.md              # task cho db dev
│
└── .gitignore             # loại bỏ node_modules, config nhạy cảm
