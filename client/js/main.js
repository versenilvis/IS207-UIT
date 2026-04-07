// Gọi hàm in danh sách sách khi trang web vừa tải xong
document.addEventListener('DOMContentLoaded', () => {
    renderBooks();
});

// Hàm 1: Đọc mảng bookData và in ra các thẻ sách ở Trang chủ
function renderBooks() {
    const bookContainer = document.getElementById('book-container');
    if (!bookContainer) return;

    let htmlContent = '';
    
    // Vòng lặp lấy dữ liệu từ file data.js
    bookData.forEach(book => {
        htmlContent += `
            <div class="col">
                <div class="card h-100 exam-card shadow-sm border-0 ${book.colorClass}" onclick="showTests('${book.title}')">
                    <div class="row g-0 align-items-center">
                        <div class="col-4 text-center p-3">
                            <i class="${book.icon} fa-3x icon-soft"></i>
                        </div>
                        <div class="col-8 card-body">
                            <h5 class="card-title fw-bold mb-1">${book.title}</h5>
                            <p class="card-text mb-3 small">${book.description}</p>
                            <button class="btn btn-outline-dark btn-sm rounded-pill w-100 fw-bold">Xem chi tiết</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    bookContainer.innerHTML = htmlContent;
}

// Hàm 2: Hiển thị 10 đề thi khi bấm vào một cuốn sách
function showTests(bookName) {
    const bookSection = document.getElementById("book-list-section");
    const testSection = document.getElementById("test-list-section");
    const testContainer = document.getElementById("test-container");
    const bookNameHeader = document.getElementById("current-book-name");

    // Ẩn trang chọn sách, hiện trang danh sách test
    bookSection.style.display = "none";
    testSection.style.display = "block";

    // Cập nhật tên sách lên breadcrumb (thanh điều hướng)
    bookNameHeader.innerText = bookName;

    // Vòng lặp tạo ra 10 bài test
    testContainer.innerHTML = "";
    for (let i = 1; i <= 10; i++) {
        testContainer.innerHTML += `
            <div class="col">
                <div class="card shadow-sm border-0 d-flex flex-row align-items-center p-3">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">${bookName} - Test ${i}</h6>
                        <small class="text-muted">120 phút | 200 câu | 7 phần thi</small>
                    </div>
                    <button onclick="window.location.href='exam.html'" class="btn btn-primary btn-sm px-4">Làm bài</button>
                </div>
            </div>
        `;
    }
}

// Hàm 3: Nút quay lại (Ẩn danh sách test, hiện lại danh sách sách)
function showBooks() {
    document.getElementById("test-list-section").style.display = "none";
    document.getElementById("book-list-section").style.display = "block";
}