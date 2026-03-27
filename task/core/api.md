# api design

> bảng thiết kế api chi tiết cho backend, dùng để fe và be sync với nhau

## auth

| Router | Endpoint | Mô tả | Method | Input (req.body) | Output (res.body) | Middleware | Errors |
|--------|----------|--------|--------|------------------|-------------------|------------|--------|
| Auth | `/api/auth/register` | đăng ký tài khoản | POST | `{ username, password, email }` | `{ message, user: { id } }` | - | 400, 409 |
| Auth | `/api/auth/login` | đăng nhập | POST | `{ username, password }` | `{ message, token, user: { id } }` | - | 400, 401 |
| Auth | `/api/auth/me` | lấy thông tin user | GET | - | `{ user: { id, username, role } }` | auth | 401 |

## tests (đề thi)

| Router | Endpoint | Mô tả | Method | Input (req.body) | Output (res.body) | Middleware | Errors |
|--------|----------|--------|--------|------------------|-------------------|------------|--------|
| Test | `/api/tests` | lấy danh sách đề | GET | - | `{ tests: [{ id, title, is_premium, is_unlocked }] }` | - | - |
| Test | `/api/tests/:id` | lấy chi tiết đề (làm bài) | GET | - | `{ test: { id, title, questions: [] } }` | auth | 401, 403 |
| Test | `/api/tests` | tạo đề (admin) | POST | `{ title, ... }` | `{ message, test_id }` | adminAuth | 401, 403 |
| Test | `/api/tests/import` | import bằng excel/json | POST | `FormData (file)` | `{ message, test_id }` | adminAuth | 400, 403 |

## scoring (chấm điểm) & result

| Router | Endpoint | Mô tả | Method | Input (req.body) | Output (res.body) | Middleware | Errors |
|--------|----------|--------|--------|------------------|-------------------|------------|--------|
| Score | `/api/attempts` | nộp làm bài | POST | `{ test_id, answers: { q1: 'A' } }` | `{ attempt_id, total_score }` | auth | 400 |
| Score | `/api/attempts/:id` | xem kết quả chi tiết | GET | - | `{ attempt: { score, details: [câu đúng/sai] } }` | auth | 401, 403 |
| Score | `/api/attempts/history`| xem lịch sử user | GET | - | `{ history: [{ id, test_id, score }] }` | auth | 401 |

## payment (mở khóa đề)

| Router | Endpoint | Mô tả | Method | Input (req.body) | Output (res.body) | Middleware | Errors |
|--------|----------|--------|--------|------------------|-------------------|------------|--------|
| Payment| `/api/payments/unlock` | mua/mở đề premium | POST | `{ test_id }` | `{ message, success }` | auth | 400, 402 |

## response format chuẩn

### thành công
```json
{
  "message": "Nộp bài thành công",
  "data": { "score": 850 }
}
```

### lỗi
```json
{
  "error": "Thiếu thông tin bắt buộc"
}
```
