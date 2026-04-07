// ==========================================
// BIẾN TOÀN CỤC CHỨA DỮ LIỆU ĐỀ THI
// ==========================================
let currentQuestions = []; 

// ==========================================
// KHỞI CHẠY TẤT CẢ TÍNH NĂNG KHI WEB TẢI XONG
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. LUÔN LUÔN VẼ SIDEBAR TRƯỚC: Để đảm bảo giao diện không bị vỡ dù mạng chậm
    renderSidebar();

    // 2. Đi lấy dữ liệu từ PHP (API)
    fetchExamData();

    // 3. Khởi động đồng hồ đếm ngược (120 phút = 7200 giây)
    startTimer(120 * 60);

    // 4. Kích hoạt tính năng Audio chỉ cho nghe 1 lần
    setupAudioOnce();
});


// ==========================================
// HÀM: GỌI DỮ LIỆU TỪ PHP (API)
// ==========================================
async function fetchExamData() {
    try {
        // Gọi file PHP (đường dẫn đã khớp với hình ảnh cây thư mục của bạn)
        const response = await fetch('../api/get_question.php?test_id=1');
        currentQuestions = await response.json();
        
        if (currentQuestions && currentQuestions.length > 0) {
            // Có dữ liệu thì in ra màn hình và bật theo dõi click
            renderQuestions(currentQuestions);
            setupAnswerTracking(); 
        } else {
            console.error("Không có dữ liệu câu hỏi.");
            document.getElementById('question-list-container').innerHTML = 
                "<p class='text-danger fw-bold'>Không tìm thấy câu hỏi nào cho đề thi này.</p>";
        }
    } catch (error) {
        console.error("Lỗi khi kết nối Database:", error);
        document.getElementById('question-list-container').innerHTML = 
            "<p class='text-danger'>Không thể tải dữ liệu. Vui lòng bật XAMPP và kiểm tra lại.</p>";
    }
}


// ==========================================
// 1. HÀM TỰ ĐỘNG IN CÂU HỎI TỪ DATABASE (CỘT TRÁI)
// ==========================================
function renderQuestions(questions) {
    const container = document.getElementById('question-list-container');
    if (!container || !questions) return;

    let htmlContent = '';

    questions.forEach(q => {
        // 1. Ảnh Part 1
        let imageHtml = '';
        if (q.image_url) {
            imageHtml = `<img src="${q.image_url}" class="img-fluid mb-3 rounded shadow-sm" style="max-height: 250px;" alt="Question Image">`;
        }

        // 2. Đoạn văn Part 6, 7
        let paragraphHtml = '';
        if (q.paragraph) {
            const formattedParagraph = q.paragraph.replace(/\n/g, '<br>');
            paragraphHtml = `<div class="p-3 bg-light border rounded mb-3" style="font-size: 0.95rem;">${formattedParagraph}</div>`;
        }

        // 3. Ẩn chữ Part 2
        let displayContent = q.content;
        if (q.part === 2) {
            displayContent = "<i class='text-muted'>Listen to the audio to answer this question.</i>";
        }

        // 4. Các đáp án
        let optionsHtml = '';
        if (q.options && q.options.length > 0) {
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
        }

        // 5. Gộp vào giao diện
        htmlContent += `
            <div class="d-flex mb-5" id="question-${q.question_number}">
                <div class="q-number me-3">${q.question_number}</div>
                <div class="flex-grow-1">
                    ${imageHtml}
                    ${paragraphHtml}
                    <p class="fw-bold mb-2">${displayContent}</p>
                    ${optionsHtml}
                </div>
            </div>
        `;
    });

    container.innerHTML = htmlContent;
}


// ==========================================
// 2. HÀM TỰ ĐỘNG IN 200 Ô VUÔNG (SIDEBAR)
// ==========================================
function renderSidebar() {
    const partRanges = [
        { part: 1, start: 1, end: 6 },
        { part: 2, start: 7, end: 31 },
        { part: 3, start: 32, end: 70 },
        { part: 4, start: 71, end: 100 },
        { part: 5, start: 101, end: 130 },
        { part: 6, start: 131, end: 146 },
        { part: 7, start: 147, end: 200 }
    ];

    partRanges.forEach(range => {
        const container = document.getElementById(`sidebar-part-${range.part}`);
        if (!container) return;

        let htmlContent = '';
        for (let i = range.start; i <= range.end; i++) {
            htmlContent += `<div class="q-box">${i}</div>`;
        }

        container.innerHTML = htmlContent;
    });
}


// ==========================================
// 3. HÀM TƯƠNG TÁC ĐÁP ÁN & SIDEBAR
// ==========================================
function setupAnswerTracking() {
    const radioInputs = document.querySelectorAll('.form-check-input');
    const qBoxes = document.querySelectorAll('.q-box');

    // A. Tô màu xanh khi chọn đáp án
    radioInputs.forEach(input => {
        input.addEventListener('change', function() {
            const qNumber = this.name.replace('q', '');
            qBoxes.forEach(box => {
                if (box.innerText.trim() === qNumber) {
                    box.classList.add('answered');
                }
            });
        });
    });

    // B. Click ô vuông cuộn tới câu hỏi
    qBoxes.forEach(box => {
        box.addEventListener('click', function() {
            const qNum = this.innerText.trim();
            const targetQuestion = document.getElementById(`question-${qNum}`);
            
            if (targetQuestion) {
                targetQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                qBoxes.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}


// ==========================================
// 4. HÀM XỬ LÝ ĐỒNG HỒ ĐẾM NGƯỢC
// ==========================================
function startTimer(totalSeconds) {
    const timerDisplays = document.getElementByID('#timer-display');
    let time = totalSeconds;
    
    const timerInterval = setInterval(() => {
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        
        const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        timerDisplays.forEach(display => {
            display.innerText = formattedTime;
        });
        
        if (time <= 0) {
            clearInterval(timerInterval);
            alert('Đã hết thời gian làm bài! Hệ thống tự động nộp bài.');
            // TODO: Bổ sung code tự động bấm nút nộp bài ở đây
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

        audioEl.addEventListener('timeupdate', function() {
            if (!this.seeking) {
                previousTime = this.currentTime;
            }
        });

        audioEl.addEventListener('seeking', function() {
            this.currentTime = previousTime;
        });

        audioEl.addEventListener('ended', function() {
            this.removeAttribute('controls'); 
            this.style.pointerEvents = 'none'; 
            
            const notice = document.createElement('span');
            notice.className = "badge bg-danger ms-3 mt-2";
            notice.innerHTML = "<i class='fas fa-lock me-1'></i>Đã khóa Audio";
            this.parentElement.appendChild(notice);
        });
    }
}