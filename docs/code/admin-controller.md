# Tài liệu Kỹ thuật: Admin Controller

Tài liệu này đặc tả chi tiết các API điều khiển nghiệp vụ quản trị hệ thống thuộc tệp tin [admin-controller.php](file:///home/verse/dev/github/prephub/server/controllers/admin-controller.php). Các API này phục vụ trực tiếp cho trang quản trị tích hợp.

---

## 1. Cơ chế Bảo mật & Xác thực

Tất cả các hàm trong bộ điều khiển quản trị đều bắt buộc gọi hàm kiểm tra quyền truy cập `checkAdminAccess()`.

* **Cơ chế**: Kiểm tra sự tồn tại của session `user_id` và giá trị quyền hạn `$_SESSION['role'] === 'admin'`
* **Phản hồi khi lỗi**: Trả về lỗi 403 Forbidden nếu không đủ thẩm quyền truy cập

---

## 2. Chi tiết các API Endpoint

### 2.1. Lấy thống kê tổng quan (`getAdminStats()`)
* **Đường dẫn**: `GET /api/admin/stats`
* **Mô tả**: Trả về tổng số học viên, tổng số đề thi hiện có, doanh thu tích lũy thành công và tổng số người dùng duy nhất đã thực hiện mua gói dịch vụ
* **Định dạng dữ liệu trả về**:
  ```json
  {
    "success": true,
    "data": {
      "total_users": 150,
      "total_tests": 12,
      "total_revenue": 5880000,
      "total_purchased_users": 24
    }
  }
  ```

### 2.2. Danh sách học viên phân trang (`getAdminUsers()`)
* **Đường dẫn**: `GET /api/admin/users?page={page}&limit={limit}&q={search}&role={role}&status={status}`
* **Mô tả**: Lấy danh sách học viên theo trang và giới hạn, hỗ trợ tìm kiếm theo họ tên hoặc email, đồng thời lọc theo quyền hạn và trạng thái khóa
* **Thuộc tính tính toán**:
  * `user_tests_attempted`: Số lượng đề thi thực tế học viên đã làm qua
  * `total_active_tests`: Tổng số lượng đề thi đang hoạt động trong hệ thống
* **Định dạng dữ liệu trả về**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 12,
        "uuid": "8d89163d-42ba-4b68-80f0-3330b62e4975",
        "first_name": "A",
        "last_name": "Nguyễn Văn",
        "email": "user@prephub.com",
        "role": "user",
        "is_banned": 0,
        "is_premium": 1,
        "premium_plan": "pro",
        "premium_until": "2026-06-28 12:00:00",
        "created_at": "2026-04-19 08:30:00",
        "user_tests_attempted": 3,
        "total_active_tests": 12
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 1
    },
    "stats": {
      "total_users": 150,
      "new_users_month": 15,
      "inactive_users_7d": 8
    }
  }
  ```

### 2.3. Cập nhật thông tin học viên (`updateAdminUser()`)
* **Đường dẫn**: `PUT /api/admin/users/{userId}`
* **Mô tả**: Cập nhật vai trò (`role`: 'user' hoặc 'admin') hoặc trạng thái khóa tài khoản (`is_banned`: 0 hoặc 1)
* **Định dạng Request Body**:
  ```json
  {
    "role": "admin",
    "is_banned": 0
  }
  ```
* **Định dạng dữ liệu trả về**:
  ```json
  {
    "success": true,
    "message": "Cập nhật user thành công"
  }
  ```

### 2.4. Danh sách lượt thi thử phân trang (`getAdminAttempts()`)
* **Đường dẫn**: `GET /api/admin/attempts?page={page}&limit={limit}`
* **Mô tả**: Xem lịch sử các lượt thi của học viên, sắp xếp giảm dần theo thời gian làm bài gần nhất
* **Định dạng dữ liệu trả về**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 45,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "listening_correct": 42,
        "reading_correct": 38,
        "listening_score": 410,
        "reading_score": 380,
        "total_score": 790,
        "time_spent": 5400,
        "created_at": "2026-05-28 10:15:30",
        "first_name": "A",
        "last_name": "Nguyễn Văn",
        "email": "user@prephub.com",
        "is_premium": 1,
        "premium_plan": "pro",
        "has_course": 0,
        "title": "Đề thi thử TOEIC số 1",
        "user_tests_attempted": 3,
        "total_active_tests": 12
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 142
    }
  }
  ```

### 2.5. Doanh thu định kỳ tháng (`getAdminRevenue()`)
* **Đường dẫn**: `GET /api/admin/revenue`
* **Mô tả**: Trả về doanh thu tích lũy tháng hiện tại, doanh thu trọn đời và mảng dữ liệu phục vụ vẽ biểu đồ doanh thu của 12 tháng gần nhất
* **Định dạng dữ liệu trả về**:
  ```json
  {
    "success": true,
    "data": {
      "current_month": 1250000,
      "all_time": 5880000,
      "chart": [
        {
          "month": "2026-04",
          "total": "2840000"
        },
        {
          "month": "2026-05",
          "total": "3040000"
        }
      ]
    }
  }
  ```

### 2.6. Lịch sử giao dịch mua gói (`getAdminTransactions()`)
* **Đường dẫn**: `GET /api/admin/transactions?page={page}&limit={limit}`
* **Mô tả**: Liệt kê chi tiết các giao dịch mua gói dịch vụ thành công của người dùng hệ thống
* **Định dạng dữ liệu trả về**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 89,
        "tx_id": "PH8B9C0A1D",
        "plan_id": "pro_year",
        "plan_name": "Premium Năm",
        "price": 588000,
        "period": "năm",
        "created_at": "2026-05-27 15:30:00",
        "first_name": "A",
        "last_name": "Nguyễn Văn",
        "email": "user@prephub.com"
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 89
    }
  }
  ```

---

## 3. Cơ chế Phân trang phía Server

Hệ thống sử dụng các tham số giới hạn an toàn `LIMIT` và vị trí bắt đầu `OFFSET` để truy vấn cơ sở dữ liệu:

1. **Bước 1: Đếm tổng số bản ghi**: Chạy câu lệnh `SELECT COUNT(*)` với các điều kiện lọc tương đương để lấy giá trị `total` phục vụ tính số trang
2. **Bước 2: Lấy dữ liệu phân trang**: 
   * Tính vị trí: `offset = (page - 1) * limit`
   * Ràng buộc kiểu dữ liệu: Liên kết tham số `:limit` và `:offset` dưới định dạng số nguyên `PDO::PARAM_INT` trước khi thực thi
