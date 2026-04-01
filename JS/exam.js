// ==========================================
// KHỞI CHẠY TẤT CẢ TÍNH NĂNG KHI WEB TẢI XONG
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Tự động in danh sách 200 câu hỏi (Cột trái)
    renderQuestions();

    // 2. Tự động in danh sách 200 ô vuông (Cột phải - Sidebar)
    renderSidebar();

    // 3. Kích hoạt tính năng theo dõi đáp án và click Sidebar
    // (LƯU Ý QUAN TRỌNG: Hàm này BẮT BUỘC phải nằm dưới 2 hàm render ở trên)
    setupAnswerTracking();

    // 4. Khởi động đồng hồ đếm ngược (120 phút = 7200 giây)
    startTimer(120 * 60);

    // 5. Kích hoạt tính năng Audio chỉ cho nghe 1 lần
    setupAudioOnce();
});


// ==========================================
// 1. HÀM TỰ ĐỘNG IN CÂU HỎI TỪ DATA.JS (CỘT TRÁI)
// ==========================================
function renderQuestions() {
    const container = document.getElementById('question-list-container');
    if (!container) return;

    let htmlContent = '';

    // Lặp qua từng câu hỏi trong mảng sampleQuestions (từ file data.js)
    sampleQuestions.forEach(q => {
        // Kiểm tra xem câu hỏi có hình ảnh không (dành cho Part 1)
        let imageHtml = '';
        if (q.image_url) {
            imageHtml = `<img src="${q.image_url}" class="img-fluid mb-3 rounded shadow-sm" style="max-height: 250px;" alt="Question Image">`;
        }

        // Tạo danh sách các đáp án (A, B, C, D)
        let optionsHtml = '';
        q.options.forEach(opt => {
            optionsHtml += `
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="q${q.question_number}" id="q${q.question_number}_${opt.label}" value="${opt.label}">
                    <label class="form-check-label" for="q${q.question_number}_${opt.label}">
                        <span class="fw-bold">${opt.label}.</span> ${opt.text}
                    </label>
                </div>
            `;
        });

        // Gộp tất cả lại thành 1 câu hỏi hoàn chỉnh
        htmlContent += `
            <div class="d-flex mb-5" id="question-${q.question_number}">
                <div class="q-number me-3">${q.question_number}</div>
                
                <div class="flex-grow-1">
                    ${imageHtml}
                    <p class="fw-bold mb-2">${q.content}</p>
                    ${optionsHtml}
                </div>
            </div>
        `;
    });

    // Đổ toàn bộ HTML vừa tạo vào khung chứa
    container.innerHTML = htmlContent;
}


// ==========================================
// 2. HÀM TỰ ĐỘNG IN 200 Ô VUÔNG (SIDEBAR)
// ==========================================
function renderSidebar() {
    // Khai báo cấu trúc đề TOEIC: Part nào từ câu mấy đến câu mấy
    const partRanges = [
        { part: 1, start: 1, end: 6 },
        { part: 2, start: 7, end: 31 },
        { part: 3, start: 32, end: 70 },
        { part: 4, start: 71, end: 100 },
        { part: 5, start: 101, end: 130 },
        { part: 6, start: 131, end: 146 },
        { part: 7, start: 147, end: 200 }
    ];

    // Vòng lặp 1: Chạy qua 7 Part
    partRanges.forEach(range => {
        // Tìm hộp chứa của Part tương ứng (VD: sidebar-part-1)
        const container = document.getElementById(`sidebar-part-${range.part}`);
        if (!container) return;

        let htmlContent = '';
        
        // Vòng lặp 2: Chạy từ câu bắt đầu đến câu kết thúc của Part đó
        for (let i = range.start; i <= range.end; i++) {
            htmlContent += `<div class="q-box">${i}</div>`;
        }

        // Đổ các ô vuông vào HTML
        container.innerHTML = htmlContent;
    });
}


// ==========================================
// 3. HÀM TƯƠNG TÁC ĐÁP ÁN & SIDEBAR
// ==========================================
function setupAnswerTracking() {
    const radioInputs = document.querySelectorAll('.form-check-input');
    const qBoxes = document.querySelectorAll('.q-box');

    // Tính năng A: Click đáp án -> Sidebar đổi màu xanh (đã làm)
    radioInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Lấy số câu hỏi từ tên input (VD: 'q32' -> '32')
            const qNumber = this.name.replace('q', ''); 
            
            // Tìm ô vuông tương ứng và thêm class 'answered'
            qBoxes.forEach(box => {
                if (box.innerText === qNumber) {
                    box.classList.add('answered');
                }
            });
        });
    });

    // Tính năng B: Click Sidebar -> Cuộn tới đúng câu hỏi
    qBoxes.forEach(box => {
        box.addEventListener('click', function() {
            const qNum = this.innerText; 
            // Tìm thẻ div bọc câu hỏi đó bên cột trái
            const targetQuestion = document.getElementById(`question-${qNum}`);
            
            if (targetQuestion) {
                // Cuộn mượt mà đến câu đó
                targetQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Xóa viền đỏ ở các ô khác và thêm viền đỏ vào ô vừa click
                qBoxes.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            } else {
                console.log(`Chưa có dữ liệu cho câu ${qNum}`);
            }
        });
    });
}


// ==========================================
// 4. HÀM XỬ LÝ ĐỒNG HỒ ĐẾM NGƯỢC
// ==========================================
function startTimer(totalSeconds) {
    const timerDisplays = document.querySelectorAll('#timer-display');
    let time = totalSeconds;
    
    const timerInterval = setInterval(() => {
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        
        // Ép định dạng 00:00
        const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        timerDisplays.forEach(display => { 
            display.innerText = formattedTime; 
        });
        
        if (time <= 0) {
            clearInterval(timerInterval);
            alert('Đã hết thời gian làm bài! Hệ thống tự động nộp bài.');
        } else {
            time--;
        }
    }, 1000);
}


// ==========================================
// 5. HÀM XỬ LÝ AUDIO (KHÔNG TUA, NGHE 1 LẦN)
// ==========================================
function setupAudioOnce() {
    const audioEl = document.querySelector('audio');
    
    if (audioEl) {
        let previousTime = 0;

        // 1. Liên tục lưu lại mốc thời gian bài nghe đang phát
        audioEl.addEventListener('timeupdate', function() {
            // Nếu người dùng KHÔNG CÓ hành động tua, ta ghi nhận mốc thời gian hiện tại
            if (!this.seeking) {
                previousTime = this.currentTime;
            }
        });

        // 2. Chặn hành động kéo thanh tiến trình (tua đi / tua lại)
        audioEl.addEventListener('seeking', function() {
            // Ngay khi phát hiện người dùng kéo thanh thời gian, 
            // lập tức ép cái cục chạy (currentTime) giật ngược lại vị trí previousTime
            this.currentTime = previousTime;
        });

        // 3. Khóa vĩnh viễn sau khi nghe xong
        audioEl.addEventListener('ended', function() {
            this.removeAttribute('controls'); // Xóa thanh điều khiển
            this.style.pointerEvents = 'none'; // Chặn click chuột
            
            // Hiện thông báo đỏ
            const notice = document.createElement('span');
            notice.className = "badge bg-danger ms-3";
            notice.innerHTML = "<i class='fas fa-lock me-1'></i>Đã khóa Audio";
            this.parentElement.appendChild(notice);
        });
    }
}