document.addEventListener('DOMContentLoaded', () => {
    const navEntries = performance.getEntriesByType("navigation");
    const isReload = navEntries.length > 0 
        ? navEntries[0].type === "reload" 
        : false;
    
    if (!isReload) {
        clearExamData(); 
    }

    setupAudioOnce();
    fetchExamData();
});

let totalDuration = 0; // Tổng thời gian làm bài (tính bằng giây) lấy từ Database
let timeLeft = 0;      // Thời gian còn lại thực tế của người dùng
let timerInterval = null; // Biến giữ trình đếm ngược để có thể dừng khi cần

/**
 * Lấy UUID từ URL -> Gọi API lấy chi tiết đề -> Kiểm tra quyền truy cập (Premium) -> 
 * Render giao diện (Audio, Câu hỏi, Navigation) -> Bắt đầu tính giờ.
 */
async function fetchExamData() {
    try {
        const params = new URLSearchParams(window.location.search);
        const uuid = params.get("uuid") || params.get("test_id");

        const response = await fetch(`/api/tests/${uuid}`);
        
        if (response.status === 403) {
            alert("Đề thi này dành riêng cho tài khoản đã mua!");
            window.location.href = "pricing.php"; 
            return;
        }

        if (!response.ok) throw new Error("Could not fetch resource");

        const questions_lists = await response.json();
        const questions = questions_lists.data.questions;
        const testDuration = Number(questions_lists.data.duration);

        
        setupExamAudio(questions);
        renderQuestions(questions);
        renderPartNav(questions);
        document.getElementById("exam-title").innerHTML = questions_lists.data.title;  
        renderSidebar(questions); 
        
        
        setupAnswerTracking(uuid); 
        startTimer(Number.isFinite(testDuration) && testDuration > 0 ? testDuration : 120 * 60, uuid); 

    } catch (error) {
        console.error("Lỗi khi kết nối Database:", error);
        document.getElementById('question-list-container').innerHTML = 
            "<p class='text-danger'>Lỗi tải dữ liệu đề thi. Vui lòng thử lại sau!</p>";
    }
}

/**
 * Tìm link audio đầu tiên có trong danh sách câu hỏi -> Gán vào thẻ <audio> -> 
 * Nếu không có audio thì khóa nút Play.
 */
function setupExamAudio(questions) {
    const audioEl = document.getElementById('exam-audio');
    const playBtn = document.getElementById('custom-play-btn');
    const statusText = document.getElementById('audio-status');
    if (!audioEl || !playBtn || !Array.isArray(questions)) return;

    // Tìm URL audio đầu tiên xuất hiện trong đề
    const firstAudioUrl = questions.reduce((foundUrl, q) => {
        if (foundUrl) return foundUrl;
        return q.passage_audio || q.audio_url || '';
    }, '');

    if (!firstAudioUrl) {
        audioEl.removeAttribute('src');
        playBtn.disabled = true;
        playBtn.classList.replace('btn-primary', 'btn-secondary');
        playBtn.innerHTML = '<i class="fas fa-volume-mute me-2"></i> Không có audio';
        if (statusText) statusText.innerText = 'Đề thi này không có audio.';
        return;
    }

    audioEl.src = firstAudioUrl;
    audioEl.load();
}


function scrollToQuestionTarget(targetEl) {
    if (!targetEl) return;
    const headerHeight = document.querySelector('.top-header')?.offsetHeight || 0;
    const audioHeight = document.querySelector('.sticky-audio')?.offsetHeight || 0;
    const targetTop = window.scrollY + targetEl.getBoundingClientRect().top;

    window.scrollTo({
        top: Math.max(0, targetTop - headerHeight - audioHeight - 16),
        behavior: 'smooth'
    });
}


function renderPartNav(questions) {
    const partTabsContainer = document.getElementById('part-tabs-container');
    if (!partTabsContainer || !Array.isArray(questions)) return;

    const firstQuestionByPart = {};
    questions.forEach(q => {
        if (!firstQuestionByPart[q.part]) firstQuestionByPart[q.part] = q.question_number;
    });

    const parts = Object.keys(firstQuestionByPart).map(Number).sort((a, b) => a - b);

    partTabsContainer.innerHTML = parts.map((part, index) => `
        <li class="nav-item">
            <a class="nav-link${index === 0 ? ' active' : ''}" href="#question-${firstQuestionByPart[part]}">Part ${part}</a>
        </li>
    `).join('');

    partTabsContainer.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetEl = document.getElementById(this.getAttribute('href').slice(1));
            if (targetEl) scrollToQuestionTarget(targetEl);
        });
    });
}


function renderQuestions(questions) {
    const container = document.getElementById('question-list-container');
    if (!container || !questions) return;

    let htmlContent = '';
    questions.forEach(q => {
        let imageHtml = q.image_url ? `<img src="${q.image_url}" class="img-fluid mb-3 rounded shadow-sm" style="max-height: 250px;">` : '';
        let paragraphHtml = q.paragraph ? `<div class="p-3 bg-light border rounded mb-3">${q.paragraph.replace(/\n/g, '<br>')}</div>` : '';
        let optionsHtml = '';
        
        q.options.forEach(opt => {
            optionsHtml += `
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="q${q.question_number}" data-q-id="${q.id}" id="q${q.question_number}_${opt.label}" value="${opt.label}">
                    <label class="form-check-label" for="q${q.question_number}_${opt.label}">
                        <span class="fw-bold">${opt.label}.</span> ${opt.content}
                    </label>
                </div>
            `;
        });

        htmlContent += `
            <div class="d-flex mb-5" id="question-${q.question_number}">
                <div class="q-number me-3">${q.question_number}</div>
                <div class="flex-grow-1">
                    ${imageHtml}${paragraphHtml}
                    <p class="fw-bold mb-2">${q.content || ''}</p>
                    ${optionsHtml}
                </div>
            </div>
        `;
    });
    container.innerHTML = htmlContent;
}


function renderSidebar(questions) {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    const partsMap = {};
    questions.forEach(q => {
        if (!partsMap[q.part]) partsMap[q.part] = [];
        partsMap[q.part].push(q.question_number);
    });

    sidebarContainer.innerHTML = Object.keys(partsMap).sort((a,b)=>a-b).map(part => `
        <div class="mb-3">
            <p class="fw-bold mb-2 small text-muted">Part ${part}</p>
            <div class="question-grid">
                ${partsMap[part].sort((a,b)=>a-b).map(num => `<div class="q-box">${num}</div>`).join('')}
            </div>
        </div>
    `).join('');

    setupAnswerTracking(new URLSearchParams(window.location.search).get("uuid"));
}

/**
 * Khôi phục đáp án cũ từ LocalStorage (nếu có) -> Lắng nghe sự kiện chọn Radio (tức là các checkbox) -> 
 * Lưu đáp án mới vào LocalStorage theo ID câu hỏi -> Đánh dấu ô vuông bên Sidebar.
 */
function setupAnswerTracking(examUuid) {
    const radioInputs = document.querySelectorAll('.form-check-input');
    const qBoxes = document.querySelectorAll('.q-box');
    const storageKey = `exam_answers_${examUuid}`;
    let savedAnswers = JSON.parse(localStorage.getItem(storageKey)) || {};

    radioInputs.forEach(input => {
        const qId = input.getAttribute('data-q-id');
        const qNum = input.name.replace('q', '');

        // Tích lại đáp án cũ
        if (savedAnswers[qId] === input.value) {
            input.checked = true;
            updateSidebarBox(qNum, true);
        }

        // Lưu đáp án mới khi click
        input.addEventListener('change', function() {
            savedAnswers[qId] = this.value;
            localStorage.setItem(storageKey, JSON.stringify(savedAnswers));
            updateSidebarBox(qNum, true);
        });
    });

    function updateSidebarBox(num, answered) {
        qBoxes.forEach(box => {
            if (box.innerText.trim() === num) {
                if (answered) box.classList.add('answered');
            }
        });
    }

    qBoxes.forEach(box => {
        box.addEventListener('click', function() {
            const target = document.getElementById(`question-${this.innerText.trim()}`);
            if (target) scrollToQuestionTarget(target);
        });
    });
}

/**
 * Tính toán thời điểm kết thúc (Date.now + duration) -> Lưu vào LocalStorage để chống F5 -> 
 * Chạy Interval mỗi giây để cập nhật giao diện -> Tự động nộp bài khi hết giờ.
 */
function startTimer(totalSeconds, examUuid) {
    const timerDisplay = document.getElementById('timer-display'); 
    if (!timerDisplay) return; 

    const storageKey = `exam_endTime_${examUuid}`;
    let endTime = localStorage.getItem(storageKey);

    if (!endTime) {
        endTime = Date.now() + (totalSeconds * 1000);
        localStorage.setItem(storageKey, endTime);
    } else {
        endTime = parseInt(endTime, 10);
    }
    
    totalDuration = totalSeconds;
    timerInterval = setInterval(() => {
        const now = Date.now();
        timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));
        
        const min = Math.floor(timeLeft / 60);
        const sec = timeLeft % 60;
        timerDisplay.innerText = `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`; 
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            localStorage.removeItem(storageKey);
            alert('Hết thời gian làm bài! Hệ thống đang nộp bài tự động.');
            submitExam();
        }
    }, 1000);
}


function setupAudioOnce() {
    const audioEl = document.getElementById('exam-audio');
    const playBtn = document.getElementById('custom-play-btn');
    if (!audioEl || !playBtn) return;

    playBtn.addEventListener('click', () => {
        audioEl.play().catch(e => console.error("Audio play failed:", e));
        playBtn.disabled = true;
        playBtn.innerHTML = '<i class="fas fa-volume-up me-2 fa-beat"></i> Đang phát...';
    });

    audioEl.addEventListener('timeupdate', () => {
        const bar = document.getElementById('audio-progress-bar');
        if (bar && audioEl.duration) {
            bar.style.width = (audioEl.currentTime / audioEl.duration * 100) + '%';
        }
    });
}

/**
 * Ẩn Modal xác nhận -> Thu thập đáp án từ LocalStorage -> Tính thời gian làm thực tế -> 
 * Gửi POST request lên Server -> Xóa dữ liệu tạm và chuyển hướng trang kết quả.
 */
async function submitExam() {
    const modalElement = document.getElementById('confirmSubmitModal');
    if (modalElement) {
        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modalInstance.hide();
    }

    const uuid = new URLSearchParams(window.location.search).get("uuid");
    const storageKey = `exam_answers_${uuid}`;
    const userAnswers = JSON.parse(localStorage.getItem(storageKey)) || {};
    
    // Tính số phút đã làm bài
    const timeSpent = Math.floor((totalDuration - timeLeft) / 60);

    try {
        const response = await fetch('/api/score', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                test_uuid: uuid, 
                answers: userAnswers,
                time_spent: timeSpent
            })
        });

        const result = await response.json();
        if (result.status === 'success') {
            localStorage.removeItem(`exam_endTime_${uuid}`);
            localStorage.removeItem(storageKey);
            
            window.location.href = `results.php?attempt_id=${result.attempt_id}`;
        } else {
            alert('Lỗi nộp bài: ' + (result.error || 'Vui lòng thử lại!'));
        }
    } catch (e) {
        console.error("Submit error:", e);
        alert('Lỗi kết nối máy chủ khi nộp bài!');
    }
}

function clearExamData() {
    const uuid = new URLSearchParams(window.location.search).get("uuid");
    if (uuid) {
        localStorage.removeItem(`exam_endTime_${uuid}`);
        localStorage.removeItem(`exam_answers_${uuid}`);
    }
}
