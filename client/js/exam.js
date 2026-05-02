document.addEventListener('DOMContentLoaded', () => {
    // 1. LUÔN LUÔN VẼ SIDEBAR TRƯỚC
    // Đã thêm vào hàm fetchExamData(), không cần vẽ trước đâu.

    // 2. Kích hoạt tính năng Audio chỉ cho nghe 1 lần
    setupAudioOnce();

    // 3. Mọi thứ giao diện đã ổn định, giờ mới đi lấy dữ liệu
    fetchExamData();
});


//Gọi dữ liệu từ db
async function fetchExamData() {
    try {
        const params = new URLSearchParams(window.location.search);
        const uuid = params.get("uuid");

        //Lấy đề thi bằng uuid
        const response = await fetch(`../../api/tests/${uuid}`);
        if (!response.ok) {
            throw new Error("Could not fetch resource (function in exam.js)");
        }
        const questions_lists = await response.json();
        const questions = questions_lists.data.questions;
        const testDuration = Number(questions_lists.data.duration);

        setupExamAudio(questions); //Fetch link audio
        renderQuestions(questions); //In ra question và passage dựa trên test uuid
        renderPartNav(questions); //In ra thanh navBar ở dưới thanh audio

        //Đổi title thành title của đề thi
        let title = document.getElementById("exam-title");
        title.innerHTML = questions_lists.data.title;  
        
        
        renderSidebar(questions); //Render side bar dựa vào tổng số câu hỏi
        setupAnswerTracking(); //Để chọn các options a,b,c,d
        startTimer(Number.isFinite(testDuration) && testDuration > 0 ? testDuration : 120 * 60); //Lấy test duration trong database và đếm ngược

    } catch (error) {
        console.error("Lỗi khi kết nối Database:", error);
        document.getElementById('question-list-container').innerHTML = 
            "<p class='text-danger'>Lỗi tải dữ liệu ở exam.js</p>";
    }
}


//Gán audio của đề thi từ dữ liệu DB
function setupExamAudio(questions) {
    const audioEl = document.getElementById('exam-audio');
    const playBtn = document.getElementById('custom-play-btn');
    const statusText = document.getElementById('audio-status');
    if (!audioEl || !playBtn || !Array.isArray(questions)) return;

    const firstAudioUrl = questions.reduce((foundUrl, q) => {
        if (foundUrl) return foundUrl;
        return q.passage_audio || q.audio_url || '';
    }, '');

    if (!firstAudioUrl) {
        audioEl.removeAttribute('src');
        audioEl.load();
        playBtn.disabled = true;
        playBtn.classList.replace('btn-primary', 'btn-secondary');
        playBtn.innerHTML = '<i class="fas fa-volume-mute me-2"></i> Không có audio';
        if (statusText) {
            statusText.innerText = 'Đề thi này không có audio.';
        }
        return;
    }

    audioEl.src = firstAudioUrl;
    audioEl.load();
    playBtn.disabled = false;
    playBtn.classList.remove('btn-secondary');
    playBtn.classList.add('btn-primary');
    playBtn.innerHTML = '<i class="fas fa-play me-2"></i> Start audio';
    if (statusText) {
        statusText.innerText = '';
        statusText.classList.remove('text-danger', 'text-success');
        statusText.classList.add('text-muted');
    }
}


//Nếu click thi di chuyển đến câu hỏi cần tìm
function scrollToQuestionTarget(targetEl) {
    if (!targetEl) return;

    const topHeader = document.querySelector('.top-header');
    const stickyAudio = document.querySelector('.sticky-audio');
    const headerHeight = topHeader ? topHeader.offsetHeight : 0;
    const audioHeight = stickyAudio ? stickyAudio.offsetHeight : 0;
    const extraOffset = 16;
    const targetTop = window.scrollY + targetEl.getBoundingClientRect().top;

    window.scrollTo({
        top: Math.max(0, targetTop - headerHeight - audioHeight - extraOffset),
        behavior: 'smooth'
    });
}


//In nav link dựa trên số part (nav link sẽ được gán ở dưới thanh audio)
function renderPartNav(questions) {
    const partTabsContainer = document.getElementById('part-tabs-container');
    if (!partTabsContainer || !Array.isArray(questions)) return;

    const firstQuestionByPart = {};
    questions.forEach(q => {
        const part = Number(q.part);
        if (!Number.isInteger(part) || firstQuestionByPart[part]) return;
        firstQuestionByPart[part] = q.question_number;
    });

    const parts = Object.keys(firstQuestionByPart)
        .map(Number)
        .sort((a, b) => a - b);

    partTabsContainer.innerHTML = parts.map((part, index) => `
        <li class="nav-item">
            <a class="nav-link${index === 0 ? ' active' : ''}" href="#question-${firstQuestionByPart[part]}">
                Part ${part}
            </a>
        </li>
    `).join('');

    partTabsContainer.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();

            partTabsContainer.querySelectorAll('.nav-link').forEach(navLink => {
                navLink.classList.remove('active');
            });
            this.classList.add('active');

            const targetId = this.getAttribute('href').slice(1);
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                scrollToQuestionTarget(targetEl);
            }
        });
    });
}


//In questions, passages, options
function renderQuestions(questions) {
    const container = document.getElementById('question-list-container');
    if (!container || !questions) return;

    let htmlContent = '';

    questions.forEach(q => {
        let imageHtml = '';
        if (q.image_url) {
            imageHtml = `<img src="${q.image_url}" class="img-fluid mb-3 rounded shadow-sm" style="max-height: 250px;" alt="Question Image">`;
        }

        let paragraphHtml = '';
        if (q.paragraph) {
            const formattedParagraph = q.paragraph.replace(/\n/g, '<br>');
            paragraphHtml = `<div class="p-3 bg-light border rounded mb-3" style="font-size: 0.95rem;">${formattedParagraph}</div>`;
        }

        let displayContent = q.content;
        if (q.part === 2) {
            displayContent = "<i class='text-muted'>Listen to the audio to answer this question.</i>";
        }

        let optionsHtml = '';
        if (q.options && q.options.length > 0) {
            q.options.forEach(opt => {
                optionsHtml += `
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="q${q.question_number}" id="q${q.question_number}_${opt.label}" value="${opt.label}">
                        <label class="form-check-label" for="q${q.question_number}_${opt.label}">
                            <span class="fw-bold">${opt.label}.</span> ${opt.content}
                        </label>
                    </div>
                `;
            });
        }

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


//In ra thanh đếm tổng số câu hỏi ở cột phải
function renderSidebar(questions) {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer || !Array.isArray(questions)) return;

    // Gom các câu hỏi lại theo part 
    const partsMap = {};

    questions.forEach(q => {
        if (!partsMap[q.part]) {
            partsMap[q.part] = [];
        }
        partsMap[q.part].push(q.question_number);
    });

    let htmlContent = '';

    // Sort các part
    const sortedParts = Object.keys(partsMap).sort((a, b) => Number(a) - Number(b));

    //In ra bảng chứa tổng số câu hỏi ở bên phải
    sortedParts.forEach(part => {
        let questionBoxes = '';
        partsMap[part].sort((a, b) => a - b);

        partsMap[part].forEach(questionNumber => {
            questionBoxes += `
                <div class="q-box" data-target="question-${questionNumber}">
                    ${questionNumber}
                </div>
            `;
        });

        htmlContent += `
            <div class="mb-3">
                <p class="fw-bold mb-2">Part ${part}</p>
                <div class="question-grid">
                    ${questionBoxes}
                </div>
            </div>
        `;
    });

    sidebarContainer.innerHTML = htmlContent;
}


//Chọn đáp án
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
                    box.classList.remove('active');
                }
            });
        });
    });

    //Click ô vuông cuộn tới câu hỏi
    qBoxes.forEach(box => {
        box.addEventListener('click', function() {
            const qNum = this.innerText.trim();
            const targetQuestion = document.getElementById(`question-${qNum}`);
            
            if (targetQuestion) {
                scrollToQuestionTarget(targetQuestion);
                qBoxes.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                window.setTimeout(() => {
                    this.classList.remove('active');
                }, 1200);
            }
        });
    });
}


//Đếm ngược dựa trên test duration
let timerInterval = null;
function startTimer(totalSeconds) {
    // Sửa 1 & 2: Chữ 'd' viết thường và bỏ dấu '#'
    const timerDisplay = document.getElementById('timer-display'); 
    
    // Check an toàn: Nếu không tìm thấy thẻ HTML đồng hồ thì thoát luôn để khỏi lỗi
    if (!timerDisplay) return; 

    if (timerInterval) {
        clearInterval(timerInterval);
    }

    let time = totalSeconds;
    
    timerInterval = setInterval(() => {
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        
        const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Sửa 3: Gán thẳng text vào thẻ luôn, KHÔNG dùng forEach
        timerDisplay.innerText = formattedTime; 
        
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
// 7. HÀM XỬ LÝ AUDIO (NÚT BẤM CUSTOM - CHUẨN TOEIC)
// ==========================================
function setupAudioOnce() {
    const audioEl = document.getElementById('exam-audio');
    const playBtn = document.getElementById('custom-play-btn');
    const statusText = document.getElementById('audio-status');
    
    // Khai báo các thẻ của thanh Progress Bar
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('audio-progress-bar');
    const currentTimeEl = document.getElementById('current-time');
    const totalTimeEl = document.getElementById('total-time'); // Thẻ chứa tổng thời gian
    
    if (!audioEl || !playBtn) return;

    // Hàm chuyển đổi số giây thành format Phút:Giây (VD: 01:30)
    function formatTime(seconds) {
        if (isNaN(seconds) || !isFinite(seconds)) return "00:00";
        const min = Math.floor(seconds / 60);
        const sec = Math.floor(seconds % 60);
        return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
    }

    // ========================================================
    // ĐOẠN CODE XỬ LÝ LẤY TỔNG THỜI GIAN VÀ IN RA NGAY LẬP TỨC
    // ========================================================
    function setTotalTime() {
        if (audioEl.duration) {
            totalTimeEl.innerText = formatTime(audioEl.duration);
            // Hiện sẵn thanh tiến trình màu xám (tuỳ chọn cho đẹp)
            progressContainer.style.display = 'block'; 
        }
    }

    // Kiểm tra xem trình duyệt đã đọc xong file MP3 chưa
    if (audioEl.readyState >= 1) {
        setTotalTime(); // Nếu tải nhanh thì in ra luôn
    } else {
        // Nếu file nặng, chờ tải xong metadata thì mới in ra
        audioEl.addEventListener('loadedmetadata', setTotalTime);
    }
    // ========================================================

    // 1. Khi thí sinh bấm Play
    playBtn.addEventListener('click', function() {
        audioEl.play().catch(e => console.error("Lỗi phát audio:", e));
        
        playBtn.classList.replace('btn-primary', 'btn-warning');
        playBtn.innerHTML = '<i class="fas fa-volume-up me-2 fa-beat"></i> Đang phát...';
        playBtn.disabled = true; // Khóa nút
        
        statusText.classList.replace('text-muted', 'text-danger');
    });

    // 2. LIÊN TỤC CẬP NHẬT THANH CHẠY (Màu vàng) KHI NGHE
    audioEl.addEventListener('timeupdate', function() {
        const current = audioEl.currentTime;
        const duration = audioEl.duration;
        
        if (duration) {
            // Tính % để kéo dài thanh vàng
            const percent = (current / duration) * 100;
            progressBar.style.width = percent + '%';
            // Cập nhật số giây đang chạy ở thẻ span current-time
            currentTimeEl.innerText = formatTime(current);
        }
    });

    // 3. Xử lý khi Audio nghe xong (Hết giờ)
    audioEl.addEventListener('ended', function() {
        playBtn.classList.replace('btn-warning', 'btn-secondary');
        playBtn.innerHTML = '<i class="fas fa-lock me-2"></i> Đã khóa Audio';
        
        statusText.innerText = "Hoàn thành phần nghe.";
        statusText.classList.replace('text-danger', 'text-success');

        progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped', 'bg-warning');
        progressBar.classList.add('bg-secondary');
    });
    
    // Chặn chuột phải
    audioEl.addEventListener('contextmenu', e => e.preventDefault());
}

// Đừng quên gọi hàm này khi trang web tải xong nhé:
document.addEventListener("DOMContentLoaded", function() {
    setupAudioOnce();
});


// Hàm này sẽ được gọi khi bạn bấm nút "Đồng ý" trong Modal
async function submitExam() {
    // 1. Ẩn modal ngay lập tức để user không bấm 2 lần
    var modalElement = document.getElementById('confirmSubmitModal');
    var modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }

    // 2. Thu thập đáp án của người dùng
    // const userAnswers = collectAnswers(); 

    try {
        // 3. Gửi dữ liệu lên API Backend (Đã sửa chuẩn đường dẫn)
        const response = await fetch('/api/exam/submit', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: 2, 
                test_id: 1, 
                // answers: userAnswers 
            })
        });

        const result = await response.json();

        // 4. Nếu thành công, chuyển hướng sang trang KẾT QUẢ
        if (result.status === 'success') {
            // Chuyển hướng và truyền attempt_id do Backend vừa tạo ra
            // Lưu ý: Tùy Backend của bạn trả về ID nằm ở result.attempt_id hay result.data.attempt_id nhé
            const newAttemptId = result.attempt_id || (result.data && result.data.attempt_id);
            
            window.location.href = `/client/pages/results.php?attempt_id=${newAttemptId}`;
        } else {
            alert('Có lỗi xảy ra: ' + result.message);
        }

    } catch (error) {
        console.error('Lỗi khi nộp bài:', error);
        alert('Không thể kết nối đến máy chủ. Vui lòng thử lại sau!');
    }
}
