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
                    <button onclick="window.location.href='exam.php'" class="btn btn-primary btn-sm px-4">Làm bài</button>
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
function handleNavClick(event) {
    if (window.location.pathname.includes('home.php')) {
        const section = document.getElementById('danh-sach-sach');
        if (section) {
            event.preventDefault(); // Chặn load lại trang
            
            // 1. Cuộn xuống khu vực danh sách
            section.scrollIntoView({ behavior: 'smooth' });

            // 2. Kiểm tra xem có đang bị kẹt ở màn hình "10 test" không
            // Nếu có thì mới ép nó quay về màn hình "Chọn bộ sách"
            const testList = document.getElementById('test-list-section'); // Kiểm tra ID này trong home.php
            const bookList = document.getElementById('book-list-section'); // Kiểm tra ID này trong home.php
            
            if (testList && testList.style.display !== 'none') {
                testList.style.display = 'none';
                if (bookList) bookList.style.display = 'block'; 
            }
        }
    }
}
document.addEventListener("DOMContentLoaded", function() {
    const navLinks = document.querySelectorAll('.nav-link');

    function updateActiveState() {
        const currentPage = window.location.pathname.split("/").pop();
        const currentHash = window.location.hash;

        // Xóa sạch class active cũ
        navLinks.forEach(link => link.classList.remove('active'));

        // Logic nhuộm màu
        if (currentPage === "home.php" || currentPage === "") {
            // Nếu có hash là danh sách đề thi
            if (currentHash === "#book-list-section") {
                document.getElementById('nav-list').classList.add('active');
            } else {
                document.getElementById('nav-home').classList.add('active');
            }
        } else if (currentPage === "premium.php") {
            document.getElementById('nav-premium').classList.add('active');
        }
    }

    // Chạy lần đầu khi vừa load trang
    updateActiveState();

    // Lắng nghe sự kiện thay đổi Hash (khi click Danh sách đề thi trên cùng trang Home)
    window.addEventListener("hashchange", updateActiveState);
    
    // Xử lý riêng cho nút Trang chủ khi đang ở Home mà bấm vào nó (để xóa màu ở Danh sách đề thi)
    document.getElementById('nav-home').addEventListener('click', function() {
        setTimeout(updateActiveState, 10); 
    });
});