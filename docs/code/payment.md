### 1. Bảng giá "Chim mồi"
Giá trên UI vẫn chính xác, chỉ là nó tính theo tháng để nhìn trông rẻ hơn thôi, khi thanh toán sẽ thu tiền tổng bằng thuộc tính `data-total`.

*   **KHOÁ HỌC (Mua lẻ):**
    *   Giá gốc (Gạch đi): `350.000đ`
    *   Giá thu thật / UI hiển thị: **`249.000đ / vĩnh viễn`**.

*   **PREMIUM:**
    *   **Tháng**: `69.000đ` (Gạch đi `99.000đ`).
    *   **Năm**: Thu thật **`588.000đ`**.
        *   UI hiển thị: **`49.000đ / tháng`** (Gạch đi `828.000đ`).

*   **TRỌN BỘ THÁNG (Khoá học + Premium 1 Tháng):**
    *   Giá gốc ảo (Gạch đi): `419.000đ`.
    *   Thu thật / UI hiển thị: **`289.000đ / khoá`**.

*   **TRỌN BỘ NĂM (Khoá học + Premium 1 Năm):**
    *   Giá gốc ảo (Gạch đi): `938.000đ`.
    *   Thu thật: **`749.000đ`**.
    *   UI hiển thị: **`62.000đ / tháng`** (Lấy 749k chia 12).

---

### 2. Logic nghiệp vụ:
Hệ thống tự động nhận diện gói hiện tại của user để trừ đi số tiền tương ứng họ ĐÃ THANH TOÁN (Trừ cứng theo đúng giá trị cấu hình, không trừ lố).

*   **Kịch bản 1: Đang là Premium (Tháng/Năm) -> Mua Trọn Bộ (Tháng)**
    *   *Logic:* Trừ đi số tiền 69.000đ (Giá Premium Tháng).
    *   *Khách trả:* `289.000đ - 69.000đ` = **`220.000đ`**.

*   **Kịch bản 2: Đang là Premium (Tháng/Năm) -> Mua Trọn Bộ (Năm)**
    *   *Logic:* Trừ đi số tiền 69.000đ (Giá Premium Tháng).
    *   *Khách trả:* `749.000đ - 69.000đ` = **`680.000đ`** (UI chim mồi: `56.000đ / tháng`).

*   **Kịch bản 3: Đã có Trọn Bộ (Tháng) -> Mua Trọn Bộ (Năm) [QUAN TRỌNG]**
    *   *Logic:* User đã sở hữu Vĩnh viễn Khoá học, nên tuyệt đối không được thu tiền Khoá học nữa. Họ chỉ mua thêm gói Premium Năm (588k) nhưng vẫn được hưởng ưu đãi bundle.
    *   *Công thức:* Lấy giá Trọn Bộ Năm trừ đi tiền Trọn Bộ Tháng họ đã mua (Vì Trọn bộ tháng đã bao gồm luôn Khoá học + Premium 1 tháng).
    *   *Khách trả:* `749.000đ - 289.000đ` = **`460.000đ`**.
    *   *UI chim mồi hiển thị:* **`38.000đ / tháng`** (460k / 12).

---

### 3. Kịch bản Test QA chi tiết:
Dưới đây là kịch bản test để verify logic cộng dồn giá và block mua trùng lặp trong một vòng đời User (User Journey) phức tạp nhất.

**Chuẩn bị:** 
- Tài khoản mới tinh (Free). 
- Bật tab Pricing.

**Bước 1: Mua Premium Tháng (`pro`)**
- **Action:** Click "Đăng ký ngay" ở cột Premium (tab Tháng).
- **Verify UI:** QR code hiện đúng giá **69.000đ**.
- **Result:** Tài khoản nâng cấp thành Premium Tháng. (Cột Premium Tháng hiện `Gói hiện tại`).

**Bước 2: Nâng cấp lên Trọn Bộ Tháng (`ultra`)**
- **Action:** Click "Nâng cấp lên Trọn Bộ" ở cột Trọn Bộ (tab Tháng).
- **Verify UI:** Giá thanh toán trên QR code hiện **220.000đ** (Đã tự động trừ đi 69k của Premium Tháng).
- **Result:** Tài khoản nâng lên Trọn Bộ. 
- **Verify Block:**
    - Cột Trọn Bộ (Tháng) hiện `Gói hiện tại`.
    - Thẻ Khoá học mặc định không cho mua lẻ nữa vì đã sở hữu vĩnh viễn toàn bộ khoá (Block Khoá học).

**Bước 3: Nâng cấp lên Premium Năm (`pro_year`)**
- **Action:** Gạt toggle sang tab Năm. Click "Nâng cấp lên Năm" ở cột Premium.
- **Verify UI:** QR code hiển thị thanh toán **519.000đ** (Vì đã có Premium tháng 69k, `588k - 69k = 519k` - *Lưu ý: Tuỳ thuộc logic code trừ cứng hay chỉ cho up thẳng 588k*).
- **Result:** Tài khoản gia hạn thành công Premium lên 1 năm (`pro_year`).

**Bước 4: Cố tình mua tiếp Trọn Bộ Năm (`ultra_year`)**
- **Action:** Vẫn ở tab Năm, user nhìn sang cột Trọn Bộ và cố tình nhấn nâng cấp.
- **Verify Block:** 
    - Nút bấm phải bị **DISABLE / Ẩn** hoặc hiện trạng thái `Đã sở hữu` / `Gói hiện tại`. 
    - **Lý do:** User lúc này đã có Premium Năm (từ Bước 3) và đã sở hữu Vĩnh viễn Khoá học (từ Bước 2). Việc mua thêm Trọn Bộ Năm là dư thừa và không mang lại giá trị gì mới. Hệ thống tuyệt đối không được cho phép User mua lại gói `ultra_year` để tránh bị charge tiền oan (Block hoàn toàn).