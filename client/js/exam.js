document.addEventListener('DOMContentLoaded', () => {
    // --- TÍNH NĂNG MỚI: Phân biệt F5  và Vào mới ---
    // Kiểm tra xem user nhấn F5 hay là mới đi từ trang ngoài vào
    const navEntries = performance.getEntriesByType("navigation");
    const isReload = navEntries.length > 0 
        ? navEntries[0].type === "reload" 
        : (window.performance && performance.navigation.type === 1);
    if (!isReload) {
        clearExamData(); 
    }

    // Kích hoạt tính năng Audio chỉ cho nghe 1 lần
    setupAudioOnce();

    // Mọi thứ giao diện đã ổn định, giờ mới đi lấy dữ liệu
    fetchExamData();
});

//Gọi dữ liệu từ db
async function fetchExamData() {
    try {
        const params = new URLSearchParams(window.location.search);
        const uuid = params.get("uuid") || params.get("test_id");
       // Lấy đề thi bằng uuid
        const response = await fetch(`/api/tests/${uuid}`);
        // 1. CHẶN NGAY TỪ CỬA NẾU BACKEND BÁO LỖI 403 (CHƯA MUA ĐỀ)
        if (response.status === 403) {
            alert("Đề thi này dành riêng cho tài khoản đã mua!");
            window.location.href = "/client/pages/premium.php"; 
            return; // Dừng tại đây, không tải dữ liệu nữa
        }

        // 2. Bắt các lỗi khác (ví dụ 404 không tìm thấy đề, 500 lỗi server...)
        if (!response.ok) {
            throw new Error("Could not fetch resource (function in exam.js)");
        }

        // 3. Nếu mọi thứ ok (response 200) thì mới lôi data ra 
        const questions_lists = await response.json();
        const questions = questions_lists.data.questions;
        const examData = questions_lists.data;
        const testDuration = Number(questions_lists.data.duration);

        setupExamAudio(questions); // Fetch link audio
        renderQuestions(questions); // In ra question và passage
        renderPartNav(questions); // In ra thanh navBar ở dưới thanh audio

        // Đổi title thành title của đề thi
        let title = document.getElementById("exam-title");
        title.innerHTML = questions_lists.data.title;  
        
        renderSidebar(questions); // Render side bar dựa vào tổng số câu hỏi
        
        // Truyền uuid vào để làm khóa lưu trữ riêng biệt
        setupAnswerTracking(uuid); 
        // Lấy test duration trong database và đếm ngược
        startTimer(Number.isFinite(testDuration) && testDuration > 0 ? testDuration : 120 * 60, uuid); 

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
function setupAnswerTracking(examUuid) {
    const radioInputs = document.querySelectorAll('.form-check-input');
    const qBoxes = document.querySelectorAll('.q-box');
// --- TÍNH NĂNG MỚI: Khôi phục đáp án nếu lỡ F5 ---
    const storageKey = `exam_answers_${examUuid}`;
    let savedAnswers = JSON.parse(localStorage.getItem(storageKey)) || {};
    radioInputs.forEach(input => {
        const qNumber = input.name.replace('q', '');
        
        // 1. Kiểm tra xem câu này lúc trước đã chọn chưa, nếu có thì tích lại
        if (savedAnswers[qNumber] === input.value) {
            input.checked = true;
            qBoxes.forEach(box => {
                if (box.innerText.trim() === qNumber) {
                    box.classList.add('answered');
                }
            });
        }

        // 2. Lắng nghe hành động user chọn đáp án mới
        input.addEventListener('change', function() {
            // Lưu vào localStorage ngay lập tức
            savedAnswers[qNumber] = this.value;
            localStorage.setItem(storageKey, JSON.stringify(savedAnswers));

            qBoxes.forEach(box => {
                if (box.innerText.trim() === qNumber) {
                    box.classList.add('answered');
                    box.classList.remove('active');
                }
            });
        });
    });

    // Phần click cuộn trang giữ nguyên
    qBoxes.forEach(box => {
        box.addEventListener('click', function() {
            const qNum = this.innerText.trim();
            const targetQuestion = document.getElementById(`question-${qNum}`);
            if (targetQuestion) {
                scrollToQuestionTarget(targetQuestion);
                qBoxes.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                window.setTimeout(() => { this.classList.remove('active'); }, 1200);
            }
        });
    });
}


//Đếm ngược dựa trên test duration
let timerInterval = null;
function startTimer(totalSeconds, examUuid) {
    const timerDisplay = document.getElementById('timer-display'); 
    if (!timerDisplay) return; 

    if (timerInterval) clearInterval(timerInterval);

    // --- TÍNH NĂNG MỚI: Logic chống hack giờ bằng LocalStorage ---
    const storageKey = `exam_endTime_${examUuid}`;
    let endTime = localStorage.getItem(storageKey);

    if (!endTime) {
        // Nếu user mới vào lần đầu, lấy Giờ hiện tại + Tổng số giây làm bài
        endTime = Date.now() + (totalSeconds * 1000);
        localStorage.setItem(storageKey, endTime);
    } else {
        // Nếu user F5, lấy lại cái mốc giờ đã lưu
        endTime = parseInt(endTime, 10);
    }
    
    timerInterval = setInterval(() => {
        const now = Date.now();
        // Tính thời gian còn lại (đổi từ mili-giây ra giây)
        const timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerDisplay.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`; 
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            localStorage.removeItem(storageKey); // Xóa bộ nhớ giờ
            alert('Đã hết thời gian làm bài! Hệ thống tự động nộp bài.');
            submitExam(); // Tự động gọi hàm nộp bài
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

// Hàm này sẽ được gọi khi bạn bấm nút "Đồng ý" trong Modal
async function submitExam() {
    var modalElement = document.getElementById('confirmSubmitModal');
    if (modalElement) {
        var modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modalInstance.hide();
    }

    const params = new URLSearchParams(window.location.search);
    const uuid = params.get("uuid") || params.get("test_id");

    // Lấy nguyên cục đáp án từ bộ nhớ mà lúc nãy mình lưu
    const storageKey = `exam_answers_${uuid}`;
    const userAnswers = JSON.parse(localStorage.getItem(storageKey)) || {};

    // Gom dữ liệu chuẩn bị gửi
    const payload = {
        test_uuid: uuid, 
        answers: userAnswers 
    };

    try {
        // ---  POST tới /api/score ---
        const response = await fetch('/api/score', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.status === 'success' || response.ok) {
            // Xóa sạch dữ liệu bài làm cũ
            localStorage.removeItem(`exam_endTime_${uuid}`);
            localStorage.removeItem(storageKey);

            // ---  redirect sang results.php?attempt_id={uuid} ---
            const attemptId = result.attempt_id || uuid;
            window.location.href = `/client/pages/results.php?attempt_id=${attemptId}`;
        } else {
            alert('Có lỗi xảy ra: ' + (result.message || 'Lưu bài thất bại'));
        }

    } catch (error) {
        console.error('Lỗi khi nộp bài:', error);
        alert('Không thể kết nối đến máy chủ. Vui lòng thử lại sau!');
    }
}
function clearExamData() {
    const params = new URLSearchParams(window.location.search);
    const uuid = params.get("uuid") || params.get("test_id");
    if (uuid) {
        localStorage.removeItem(`exam_endTime_${uuid}`);
        localStorage.removeItem(`exam_answers_${uuid}`);
    }
}