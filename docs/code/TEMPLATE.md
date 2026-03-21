# Các hàm hỗ trợ cho API

> Source code: [utils/api.ts](https://www.google.com/search?q=../utils/api.ts)

## `getuserlocation(screenname, locationcache)`

Giao diện chính để lấy vị trí người dùng dựa trên handle twitter. hàm tích hợp bộ nhớ đệm (cache) cục bộ và hàng đợi yêu cầu tập trung để tối ưu hiệu suất và tuân thủ giới hạn lưu lượng (rate limits)

### ví dụ

```typescript
const location = await getUserLocation('elvbrian', myCache)
```

Trả về:

  - `"San Francisco, CA"` (nếu tìm thấy)
  - `null` (nếu không tìm thấy hoặc xảy ra lỗi/quá thời gian chờ)

-----

## `setratelimitresettime(resettime)`

Cập nhật trình theo dõi `rateLimitResetTime` toàn cục. hàm này thường được gọi bởi middleware hoặc bộ xử lý phản hồi khi phát hiện trạng thái 429 hoặc header giới hạn lưu lượng từ twitter

## `makelocationrequest(screenname)`

Điều phối giao tiếp tin cậy giữa các ngữ cảnh (cross-context) thông qua window messaging

  - **Vấn đề**: việc gửi tin nhắn bất đồng bộ giữa các ngữ cảnh script khác nhau dễ bị lỗi tranh chấp dữ liệu (race conditions) và rò rỉ bộ nhớ nếu phản hồi bị mất
  - **Giải pháp**: bao bọc `window.postMessage` trong một promise với cơ chế theo dõi `requestId` duy nhất. một trình bảo vệ timeout 10 giây đảm bảo trình lắng nghe sự kiện được dọn dẹp ngay cả khi nền tảng không phản hồi
  - **Ví dụ**:
    ```typescript
    const location = await makeLocationRequest('screen_name')
    ```

### `processrequestqueue()`

Khởi chạy việc xử lý hàng đợi các yêu cầu. hàm này được gọi tự động bởi `getUserLocation` nhưng có thể export để kích hoạt thủ công nếu cần

  - **Lưu ý**: chỉ có một thực thể của trình xử lý hàng đợi được chạy tại một thời điểm

### Hành vi

Khi thời gian reset được thiết lập, `processRequestQueue` sẽ tự động tạm dừng tất cả yêu cầu gửi đi cho đến khi dấu thời gian unix (tính bằng giây) được chỉ định trôi qua

-----

## Quản lý hàng đợi yêu cầu (request queue management)

Module duy trì một hàng đợi nội bộ để xử lý các vấn đề phức tạp khi tương tác api trên trình duyệt

### Vấn đề: tắc nghẽn api & giới hạn lưu lượng

Việc thực hiện nhiều yêu cầu lấy vị trí liên tục có thể dẫn đến việc bị chặn bởi cơ chế chống crawl/giới hạn lưu lượng của twitter. ngoài ra, window messaging có thể trở nên không đáng tin cậy nếu có quá nhiều trình lắng nghe (listener) cùng hoạt động

### Giải pháp: kiểm soát thực thi

Hàng đợi áp dụng các quy tắc xử lý nghiêm ngặt:

  - **Khả năng chịu lỗi giới hạn lưu lượng**: kiểm tra `rateLimitResetTime` trước mỗi đợt gửi
  - **Kiểm soát điều tiết (throttle control)**: khoảng cách tối thiểu 2 giây (`MIN_REQUEST_INTERVAL`) giữa các yêu cầu
  - **Giới hạn đồng thời**: tối đa 2 (`MAX_CONCURRENT_REQUESTS`) yêu cầu hoạt động cùng lúc
  - **Tính toàn vẹn tin nhắn**: sử dụng `requestId` duy nhất cho mỗi cuộc gọi `window.postMessage` để đảm bảo trình xử lý bỏ qua các phản hồi không liên quan hoặc bị trùng lặp

```typescript
// logic nội bộ đảm bảo chỉ một trình xử lý chạy
if (isProcessingQueue || requestQueue.length === 0) return
```