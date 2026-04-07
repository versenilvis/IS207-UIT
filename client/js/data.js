// 1. Danh mục sách (Dùng để hiển thị ở trang chủ)
const bookData = [
    { id: 1, title: "ETS 2019", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-blue", icon: "fas fa-star" },
    { id: 2, title: "ETS 2020", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-amber", icon: "fas fa-book" },
    { id: 3, title: "ETS 2021", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-grey", icon: "fas fa-star" },
    { id: 4, title: "ETS 2022", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-blue", icon: "fas fa-book" },
    { id: 5, title: "ETS 2023", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-amber", icon: "fas fa-star" },
    { id: 6, title: "ETS 2024", description: "10 Bài Test mới nhất", colorClass: "bg-pastel-grey", icon: "fas fa-book" },
];

// 2. Danh sách đề thi (Bảng tests trong ERD)
const tests = [
    { id: 101, book_id: 1, title: "ETS 2019 - Test 1", duration: 120, total_questions: 200 },
    { id: 102, book_id: 1, title: "ETS 2019 - Test 2", duration: 120, total_questions: 200 },
    { id: 103, book_id: 1, title: "ETS 2019 - Test 3", duration: 120, total_questions: 200 },
    { id: 104, book_id: 1, title: "ETS 2019 - Test 4", duration: 120, total_questions: 200 },
    { id: 105, book_id: 1, title: "ETS 2019 - Test 5", duration: 120, total_questions: 200 },
    { id: 106, book_id: 1, title: "ETS 2019 - Test 6", duration: 120, total_questions: 200 },
    { id: 107, book_id: 1, title: "ETS 2019 - Test 7", duration: 120, total_questions: 200 },
    { id: 108, book_id: 1, title: "ETS 2019 - Test 8", duration: 120, total_questions: 200 },
    { id: 109, book_id: 1, title: "ETS 2019 - Test 9", duration: 120, total_questions: 200 },
    { id: 110, book_id: 1, title: "ETS 2019 - Test 10", duration: 120, total_questions: 200 },
    { id: 201, book_id: 2, title: "ETS 2020 - Test 1", duration: 120, total_questions: 200 },
    { id: 202, book_id: 2, title: "ETS 2020 - Test 2", duration: 120, total_questions: 200 },
    { id: 203, book_id: 2, title: "ETS 2020 - Test 3", duration: 120, total_questions: 200 },
    { id: 204, book_id: 2, title: "ETS 2020 - Test 4", duration: 120, total_questions: 200 },
    { id: 205, book_id: 2, title: "ETS 2020 - Test 5", duration: 120, total_questions: 200 },
    { id: 206, book_id: 2, title: "ETS 2020 - Test 6", duration: 120, total_questions: 200 },
    { id: 207, book_id: 2, title: "ETS 2020 - Test 7", duration: 120, total_questions: 200 },
    { id: 208, book_id: 2, title: "ETS 2020 - Test 8", duration: 120, total_questions: 200 },
    { id: 209, book_id: 2, title: "ETS 2020 - Test 9", duration: 120, total_questions: 200 },
    { id: 210, book_id: 2, title: "ETS 2020 - Test 10", duration: 120, total_questions: 200 },
    { id: 301, book_id: 3, title: "ETS 2021 - Test 1", duration: 120, total_questions: 200 },
    { id: 302, book_id: 3, title: "ETS 2021 - Test 2", duration: 120, total_questions: 200 },
    { id: 303, book_id: 3, title: "ETS 2021 - Test 3", duration: 120, total_questions: 200 },
    { id: 304, book_id: 3, title: "ETS 2021 - Test 4", duration: 120, total_questions: 200 },
    { id: 305, book_id: 3, title: "ETS 2021 - Test 5", duration: 120, total_questions: 200 },
    { id: 306, book_id: 3, title: "ETS 2021 - Test 6", duration: 120, total_questions: 200 },
    { id: 307, book_id: 3, title: "ETS 2021 - Test 7", duration: 120, total_questions: 200 },
    { id: 308, book_id: 3, title: "ETS 2021 - Test 8", duration: 120, total_questions: 200 },
    { id: 309, book_id: 3, title: "ETS 2021 - Test 9", duration: 120, total_questions: 200 },
    { id: 310, book_id: 3, title: "ETS 2021 - Test 10", duration: 120, total_questions: 200 },
    { id: 401, book_id: 4, title: "ETS 2022 - Test 1", duration: 120, total_questions: 200 },
    { id: 402, book_id: 4, title: "ETS 2022 - Test 2", duration: 120, total_questions: 200 },
    { id: 403, book_id: 4, title: "ETS 2022 - Test 3", duration: 120, total_questions: 200 },
    { id: 404, book_id: 4, title: "ETS 2022 - Test 4", duration: 120, total_questions: 200 },
    { id: 405, book_id: 4, title: "ETS 2022 - Test 5", duration: 120, total_questions: 200 },
    { id: 406, book_id: 4, title: "ETS 2022 - Test 6", duration: 120, total_questions: 200 },
    { id: 407, book_id: 4, title: "ETS 2022 - Test 7", duration: 120, total_questions: 200 },
    { id: 408, book_id: 4, title: "ETS 2022 - Test 8", duration: 120, total_questions: 200 },
    { id: 409, book_id: 4, title: "ETS 2022 - Test 9", duration: 120, total_questions: 200 },
    { id: 410, book_id: 4, title: "ETS 2022 - Test 10", duration: 120, total_questions: 200 },
    { id: 501, book_id: 5, title: "ETS 2023 - Test 1", duration: 120, total_questions: 200 },
    { id: 502, book_id: 5, title: "ETS 2023 - Test 2", duration: 120, total_questions: 200 },
    { id: 503, book_id: 5, title: "ETS 2023 - Test 3", duration: 120, total_questions: 200 },
    { id: 504, book_id: 5, title: "ETS 2023 - Test 4", duration: 120, total_questions: 200 },
    { id: 505, book_id: 5, title: "ETS 2023 - Test 5", duration: 120, total_questions: 200 },
    { id: 506, book_id: 5, title: "ETS 2023 - Test 6", duration: 120, total_questions: 200 },
    { id: 507, book_id: 5, title: "ETS 2023 - Test 7", duration: 120, total_questions: 200 },
    { id: 508, book_id: 5, title: "ETS 2023 - Test 8", duration: 120, total_questions: 200 },
    { id: 509, book_id: 5, title: "ETS 2023 - Test 9", duration: 120, total_questions: 200 },
    { id: 510, book_id: 5, title: "ETS 2023 - Test 10", duration: 120, total_questions: 200 },
    { id: 601, book_id: 6, title: "ETS 2024 - Test 1", duration: 120, total_questions: 200 },
    { id: 602, book_id: 6, title: "ETS 2024 - Test 2", duration: 120, total_questions: 200 },
    { id: 603, book_id: 6, title: "ETS 2024 - Test 3", duration: 120, total_questions: 200 },
    { id: 604, book_id: 6, title: "ETS 2024 - Test 4", duration: 120, total_questions: 200 },
    { id: 605, book_id: 6, title: "ETS 2024 - Test 5", duration: 120, total_questions: 200 },
    { id: 606, book_id: 6, title: "ETS 2024 - Test 6", duration: 120, total_questions: 200 },
    { id: 607, book_id: 6, title: "ETS 2024 - Test 7", duration: 120, total_questions: 200 },
    { id: 608, book_id: 6, title: "ETS 2024 - Test 8", duration: 120, total_questions: 200 },
    { id: 609, book_id: 6, title: "ETS 2024 - Test 9", duration: 120, total_questions: 200 },
    { id: 610, book_id: 6, title: "ETS 2024 - Test 10", duration: 120, total_questions: 200 },
];
// ==========================================
// CẤU HÌNH CHUNG CHO TOÀN BỘ TRANG THI
// ==========================================

// 1. Đường dẫn API (Sau này up host thật chỉ cần sửa ở duy nhất chỗ này)
const API_BASE_URL = "http://localhost/prehub/api/";

// 2. Thời gian làm bài mặc định (120 phút = 7200 giây)
const EXAM_TIME_SECONDS = 120 * 60;

// 3. Cấu trúc chia 7 Part của TOEIC (Dùng để vẽ Sidebar)
const TOEIC_PART_RANGES = [
    { part: 1, start: 1, end: 6 },
    { part: 2, start: 7, end: 31 },
    { part: 3, start: 32, end: 70 },
    { part: 4, start: 71, end: 100 },
    { part: 5, start: 101, end: 130 },
    { part: 6, start: 131, end: 146 },
    { part: 7, start: 147, end: 200 }
];
