# Định hướng
> [!WARNING]
> Lưu ý là các thiết kế chỉ tham khảo chứ chưa chính thức, dựa vào đó tưởng tượng ra trước

## 1. Trang Chủ (home.php)

> [!NOTE]
> Link thiết kế: https://ibb.co/vxjfnDSH

**Hiện tại đang làm gì:**
- Hiển thị danh sách sách/bộ đề theo nhóm (dùng `data.js` hard-code)
- Bấm vào một bộ sách thì mở ra danh sách đề trong bộ đó
- Navbar có nút Đăng xuất nhưng chưa phân biệt trạng thái đăng nhập/chưa đăng nhập

**Còn thiếu / Cần làm:**
#### Về phần thiết kế:
- Có 1 pop-up banner hình vuông như shopee, lazada hay hiện khi mở app lên ấy (sẽ thiết kế)
- Bám sát thiết kế ban đầu
- Phần bảng giá sẽ thiết kế lại, vẫn chia thành 4 gói free, premium (unlock mọi đề), gói khoá học (full khoá học), gói trọn bộ (premium+full khoá học)
- Mỗi card đề thi cần hiển thị tag trạng thái: 🔒 Premium / ✅ Đã làm / 🆓 Miễn phí

#### Về các bài test:
- Thay `data.js` hard-code bằng cách gọi `GET /api/tests` thật từ database
- Khi bấm vào một đề -> redirect sang `exam.php?test_id={uuid}` (truyền UUID vào URL)
- Nếu là đề Premium và user chưa mua -> hiện popup mua hoặc redirect trang Premium

#### Về đăng nhập/đăng ký trong homepage
- Nếu chưa đăng nhập thì hiện nút "Đăng nhập", nếu đã đăng nhập thì hiện avatar người dùng hình tròn + dropdown (Dashboard, Đăng xuất), chứ không phải gắn dashboard vào navbar, sẽ có thiết kế sau
- Phần đăng nhập/đăng ký ta sẽ chia ra làm 2, 1 bên có điều khoản và sử dụng, 1 bên là phần đăng ký, sẽ có 1 nút Tôi đồng ý với điều khoản..., khi người dùng kéo xuống hết, tự động đánh dấu nút này, hoặc có thể đánh dấu thủ công. Ban đầu nút đăng ký sẽ bị block nếu người dùng không chịu bấm đồng ý, bắt buộc phải bấm đồng ý mới cho phép đăng ký. Đăng nhập thì bình thường không hiện gì cả

#### Về nút dropdownm menu profile người dùng (Không phải trang profile người dùng nhé)
> [!NOTE]
> Nhìn chung sẽ kiểu kiểu như này  
> Cần thiết kế lại giao diện

<img width="627" height="894" alt="image" src="https://github.com/user-attachments/assets/a2138ac9-c93b-4b83-b047-4cce9afb2c65" />

- Sẽ có 1 nút dropdown menu, có email người dùng phía dưới, avatar bên phải, nếu premium, hiện icon premium trên avatar
- Bao gồm:
+ Profile: mở trang setting profile ra (bao gồm profile + dashboard)
+ Thông báo: thông báo khoá học, thong báo tin mới, ... (cái này demo thôi không cần làm noti system)
+ Nâng cấp: nâng cấp gói, hoặc mua khoá học
+ Phím tắt
+ Hỗ trợ: đơn giản mấy dòng văn bản soạn sẵn hỗ trợ
+ Đăng xuất màu đỏ

#### Khác
- Sẽ có phần đánh giá dùng đánh giá giả để tiết kiệm thời gian thêm 1 bảng trong database
- Footer bám sát thiết kế

---

## 2. Trang Làm Bài (exam.php)
> [!NOTE]
> Cần thiết kế lại giao diện

**Hiện tại đang làm gì:**
- Layout 2 cột đã có (trái: nội dung câu hỏi, phải: sidebar)
- Audio player và đồng hồ đếm ngược đã có
- `fetchExamData()` đang gọi sai endpoint (`../api/get_question.php?test_id=1` - endpoint không tồn tại, test_id cứng)

**Còn thiếu / Cần làm:**
- Nếu là đề Premium và user chưa mua -> hiện popup mua hoặc redirect trang Premium
- **SỬA NGAY:** Đổi `fetchExamData()` để gọi đúng endpoint `GET /api/tests/{uuid}` lấy UUID từ URL:
  ```js
  const uuid = new URLSearchParams(window.location.search).get('test_id');
  const response = await fetch(`/api/tests/${uuid}`);
  ```
- Xử lý đúng theo từng Part khi render (Part 1: ảnh, Part 2: ẩn nội dung, Part 7: đoạn văn...)
- Sidebar: Hiển thị danh sách số câu (1 → 200), highlight câu đã chọn đáp án
- Lưu đáp án vào `localStorage` (chống mất khi F5 tai nạn), lưu liên tục đáp án mỗi khi thay đổi, nếu người dùng đổi đáp án hay gì, mỗi lần là một lần ghi vào `localStorage` (yên tâm không ảnh hưởng hiệu năng) -> Để đảm bảo lỡ F5 hay gì vẫn làm tiếp
- Với thời gian, ta khong lưu vào localStorage mà dùng 1 cách như sau:
  - Lấy thời gian hiện tại + thời lượng đề thi (ví dụ 60p) = endTime.
Lưu endTime này vào localStorage
  - Lấy endTime từ localStorage ra. Tính: Thời gian còn lại = endTime - Thời gian hiện tại. Nếu kết quả > 0: Tiếp tục chạy đồng hồ từ số đó. Nếu kết quả <= 0: Tự động kích hoạt nộp bài ngay lập tức (vì đã hết giờ).

-> Để tránh gian lận reload tiết kiệm vài giây

- **Logic nộp bài:** POST tới `/api/score`, sau đó redirect sang `results.php?attempt_id={uuid}`
- Block seek audio cho Part 1-4 (không cho tua)

---

## 3. Trang Kết Quả (results.php)
> [!NOTE]
> Cần thiết kế lại giao diện

<img width="2982" height="1739" alt="image" src="https://github.com/user-attachments/assets/8d6d35f5-a7d2-433e-8dd4-ddb3277a142e" />
<br>
<img width="3455" height="1388" alt="image" src="https://github.com/user-attachments/assets/c4cbe12f-2a06-4865-b1d0-155124e18b53" />


**Hiện tại đang làm gì:**
- UI hiển thị Tổng điểm, Listening, Reading, Độ chính xác đã có
- `score-controller.php` đã có backend chấm điểm
- Trang này sử dụng kết quả từ model `attempt.php` và `score-controller.php`

**Còn thiếu / Cần làm:**
- Đọc `attempt_id` từ URL của trang
- Cần thêm endpoint `GET /api/attempts/{uuid}` ở Backend để lấy chi tiết bài đã làm
- Hiển thị bảng review từng câu: Số câu / Đáp án mình chọn / Đáp án đúng / Đúng-Sai
- Tô xanh/đỏ trực tiếp trên giao diện
- Có giải thích cho các câu sai
- Nút "Làm lại" và "Về trang chủ"
- ĐẶC BIỆT: lịch sử làm bài xếp theo mới nhất->cũ nhất từ trên xuống dưới, đơn giản chỉ cần hiện ra các bảng review từng câu đã từng làm trong quá khứ với hiện tại, nếu như tốt hơn, tạo chức năng so sánh để xem bản thân đã phát triển tốt phần nào
- CHÚ Ý: lúc nộp bài cần demo nhanh chứ khong ai rảnh ngồi chờ làm hết đề nên cần có data mẫu để show trang kết quả nhanh

---

## 4. Profile + Dashboard (gộp thành user.php)
<img width="3444" height="1825" alt="image" src="https://github.com/user-attachments/assets/17747064-9e3d-4d0c-8d4e-6250de392763" />
<br>
<img width="3455" height="1388" alt="image" src="https://github.com/user-attachments/assets/c4cbe12f-2a06-4865-b1d0-155124e18b53" />

**Hiện tại đang làm gì:**
- UI 3 thẻ thống kê và biểu đồ đường đã có
- API `GET /api/dashboard` đã hoàn chỉnh và bảo mật (lấy user_id từ Session)

**Còn thiếu / Cần làm:**
- **SỬA NGAY:** Đường dẫn CSS dùng `\` (Windows) - cần đổi sang `/` để chạy trên Linux/Docker
- Hiển thị thông tin cá nhân + có thể chỉnh sửa (rieng email không cho sửa vì nó là key chính, sửa có thể xảy ra lỗi database trong quá trình query update): Họ tên, Email, Ngày tham gia, Avatar
- Biểu đồ, thống kê nhanh: Tổng số bài đã làm, Điểm cao nhất
- Form Đổi mật khẩu (cần thêm endpoint `PUT /api/auth/password` ở Backend)
- Kết nối `dashboard.js` với API thật: gọi `GET /api/dashboard` và đổ dữ liệu vào các ô
- Kiểm tra session ở phía PHP, nếu chưa đăng nhập thì redirect về login (cái này khi người dùng gõ thủ công endpoint `/user.php`)
- Vẫn show lại lịch sử làm bài
- Link trong bảng lịch sử -> click vào tên đề thì sang `results.php?attempt_id=...`

---

## 5. Trang Admin (adminTest.php + questions.php)

**Hiện tại đang làm gì:**
- `adminTest.php`: Danh sách đề thi đã có
- `questions.php`: Form nhập câu hỏi đầy đủ - gần hoàn chỉnh

**Còn thiếu / Cần làm:**
- Ta phải sửa lại chỗ này, không có route question.php nữa, adminTest đổi thành admin.php, ta gộp chung lại thành 1, khi tạo đề thi thì hiên form tạo với `?action=create` trong page admin
- Kiểm tra quyền admin ở phía PHP trước khi render trang
- Có 1 thanh sidebar chọn từng phần
- Ở trang chính: hiện tổng số lượng user (lấy theo database), tổng doanh thu, tổng đề thi, số lượng user đã mua (bất kỳ gói nào)
#### Về các chức năng trong sidebar
- **Quản lí đề thi**: Thêm nút Sửa/Xóa cho mỗi dòng (Backend đã có `PUT` và `DELETE` rồi, chỉ cần viết Frontend), đã có sẵn thêm đề thi, danh sách đề thi

- **Quản lí user**: Hiển thị dưới dạng bảng, tổng user, tổng user mới tháng này, số user không hoạt động trong 7 ngày qua
  - Cho phép kiểm soát user (cấm, khoá tài khoản, xử lí vi phạm thủ công, có chức năng cho lên 1 role đặc biệt ví dụ như tester,...)
  - Theo dõi lịch sử làm bài
  - Có show các gói đã mua cho từng user
  - Hiện số lượng bài đã làm trên tổng bài của user đó (kèm theo thanh progress bar)
  - Sắp xếp theo user mới đến cũ

- **Quản lí dòng tiền**:
  - Tổng doanh thu tháng này (+hiện tên tháng hiện tại bên cạnh), tổng dòng tiền mọi thời gian
  - Biểu đồ cột tổng doanh thu mỗi tháng, 12 tháng (tính từ tháng hiện tại trừ đi 12)
  - Lịch sử thanh toán lần cuối do ai và bao nhiêu tiền, gói nào

> [!IMPORTANT]
> Số lượng user và đề thi, lịch sử thanh toán, ... rất lớn, ta phải phân trang, ta sẽ cần vài kĩ thuật để tối ưu chỗ này không bị chậm
---

## 6. Navbar / Component Dùng Chung

**Vấn đề hiện tại:**
- Navbar luôn hiện nút "Đăng xuất" kể cả khi chưa đăng nhập
- `navBar.php` đang có thêm `<html>`, `<head>`, `<body>` bên trong - gây lồng HTML không hợp lệ

**Cần làm:**
- Sửa `navBar.php`: Chỉ giữ lại thẻ `<nav>`, bỏ hết các thẻ HTML lớn bên trong
- Navbar thông minh theo PHP session: hiện "Đăng nhập" nếu chưa login, hiện tên + dropdown nếu đã login

## 7. Payment (thanh toán):
- Hiện popup thay vì là 1 route riêng, xong nếu mua thì mới redirect sang trang thanh toán

## 8. Các thông tin phụ
- Điều khoản sử dụng
- Hướng dẫn
- Giới thiệu
- Liên hệ
- Q&A, ...
