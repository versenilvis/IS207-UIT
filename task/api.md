# API Design

> bảng thiết kế API chi tiết cho backend, dùng để FE và BE sync với nhau

## Auth

| Router | Endpoint | Mô tả | Method | Input (req.body) | Output (res.body) | Middleware | Errors | Notes |
|--------|----------|--------|--------|------------------|-------------------|------------|--------|-------|
| Auth | `/api/auth/register` | đăng ký tài khoản mới | POST | `{ username, password }` | `{ message, user: { id, username } }` | - | 400: thiếu field, 409: username đã tồn tại | password hash bằng `password_hash()` |
| Auth | `/api/auth/login` | đăng nhập | POST | `{ username, password }` | `{ message, user: { id, username } }` | - | 400: thiếu field, 401: sai username/password | tạo session sau khi verify |
| Auth | `/api/auth/logout` | đăng xuất | POST | - | `{ message }` | requireAuth | 401: chưa đăng nhập | destroy session |
| Auth | `/api/auth/me` | lấy thông tin user hiện tại | GET | - | `{ user: { id, username } }` | requireAuth | 401: chưa đăng nhập | kiểm tra session |

## Boards

| Router | Endpoint | Mô tả | Method | Input (req.body) | Output (res.body) | Middleware | Errors | Notes |
|--------|----------|--------|--------|------------------|-------------------|------------|--------|-------|
| Board | `/api/boards` | lấy danh sách board của user | GET | - | `{ boards: [{ id, title, thumbnail_url, updated_at }] }` | requireAuth | 401: chưa đăng nhập | chỉ trả board của user đang login, sort by updated_at DESC |
| Board | `/api/boards` | tạo board mới | POST | `{ title?, content? }` | `{ message, board: { id, title } }` | requireAuth | 401: chưa đăng nhập | title mặc định "Untitled", content mặc định "[]" |
| Board | `/api/boards/:id` | lấy chi tiết 1 board | GET | - | `{ board: { id, title, content, updated_at } }` | requireAuth | 401: chưa đăng nhập, 403: không phải board của user, 404: board không tồn tại | content là JSON string, FE parse ra mảng elements |
| Board | `/api/boards/:id` | cập nhật board | PUT | `{ title?, content? }` | `{ message, board: { id, title, updated_at } }` | requireAuth | 401, 403, 404 | dùng cho cả auto-save và manual save |
| Board | `/api/boards/:id` | xoá board | DELETE | - | `{ message }` | requireAuth | 401, 403, 404 | hard delete, cascade |

## Response format chuẩn

### thành công
```json
{
  "message": "Board saved successfully",
  "board": { "id": 1, "title": "My Board" }
}
```

### lỗi
```json
{
  "error": "Username already exists"
}
```

## HTTP Status codes sử dụng

| Code | Ý nghĩa | Khi nào dùng |
|------|----------|-------------|
| 200 | OK | request thành công |
| 201 | Created | tạo mới thành công (register, create board) |
| 400 | Bad Request | thiếu field, data không hợp lệ |
| 401 | Unauthorized | chưa đăng nhập |
| 403 | Forbidden | truy cập resource không phải của mình |
| 404 | Not Found | board không tồn tại |
| 409 | Conflict | username đã tồn tại |
| 500 | Internal Server Error | lỗi server |
