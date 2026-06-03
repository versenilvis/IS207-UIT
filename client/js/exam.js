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

        
        setupExamAudio(questions_lists.data, questions);
        renderQuestions(questions);
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
function setupExamAudio(testData, questions) {
    const audioEl = document.getElementById('exam-audio');
    const playBtn = document.getElementById('custom-play-btn');
    const statusText = document.getElementById('audio-status');
    if (!audioEl || !playBtn || !Array.isArray(questions)) return;

    // kiểm tra xem đây có phải đề reading thuần hay không
    const isReadingOnly = questions.every(q => parseInt(q.part) >= 5);
    if (isReadingOnly) {
        const stickyAudio = document.querySelector('.sticky-audio');
        if (stickyAudio) {
            stickyAudio.style.display = 'none';
        }
        const warningBox = document.getElementById('listening-intro-warning');
        if (warningBox) {
            warningBox.style.display = 'none';
        }
        return;
    }

    // láy
    let targetAudioUrl = testData?.audio_url || '';
    if (!targetAudioUrl || targetAudioUrl === 'null' || targetAudioUrl === 'NULL') {
        targetAudioUrl = questions.reduce((foundUrl, q) => {
            if (foundUrl) return foundUrl;
            return q.passage_audio || q.audio_url || '';
        }, '');
    }

    if (!targetAudioUrl || targetAudioUrl === 'null' || targetAudioUrl === 'NULL') {
        audioEl.removeAttribute('src');
        playBtn.disabled = true;
        playBtn.classList.replace('btn-primary', 'btn-secondary');
        playBtn.innerHTML = '<i class="fas fa-volume-mute me-2"></i> Không có audio';
        if (statusText) statusText.innerText = 'Đề thi này không có audio.';
        return;
    }

    audioEl.src = targetAudioUrl;
    audioEl.load();

    const warningBox = document.getElementById('listening-intro-warning');
    if (warningBox) warningBox.style.display = 'block';
}


function scrollToQuestionTarget(targetEl) {
    if (!targetEl) return;
    
    // nếu là câu hỏi, kiểm tra xem có phải câu đầu tiên trong part không để cuộn về đầu part
    let elementToScroll = targetEl;
    if (targetEl.id && targetEl.id.startsWith('question-')) {
        const partSection = targetEl.closest('.part-section');
        if (partSection) {
            const firstQuestion = partSection.querySelector('.d-flex[id^="question-"]');
            if (firstQuestion && firstQuestion.id === targetEl.id) {
                elementToScroll = partSection;
            }
        }
    }
    
    const headerHeight = document.querySelector('.top-header')?.offsetHeight || 0;
    const audioHeight = document.querySelector('.sticky-audio')?.offsetHeight || 0;
    const targetTop = window.scrollY + elementToScroll.getBoundingClientRect().top;

    window.scrollTo({
        top: Math.max(0, targetTop - headerHeight - audioHeight - 16),
        behavior: 'smooth'
    });
}





function renderQuestions(questions) {
    const container = document.getElementById('question-list-container');
    if (!container || !questions) return;

    const partDirections = {
        1: "For each question in this part, you will hear four statements about a picture in your test book. When you hear the statements, you must select the one statement that best describes what you see in the picture. Then find the number of the question on your answer sheet and mark your answer. The statements will not be printed in your test book and will be spoken only one time.",
        2: "You will hear a question or statement and three responses spoken in English. They will not be printed in your test book and will be spoken only one time. Select the best response to the question or statement and mark the letter (A), (B), or (C) on your answer sheet.",
        3: "You will hear some conversations between two or more people. You will be asked to answer three questions about what the speakers say in each conversation. Select the best response to each question and mark the letter (A), (B), (C), or (D) on your answer sheet. The conversations will not be printed in your test book and will be spoken only one time.",
        4: "You will hear some talks given by a single speaker. You will be asked to answer three questions about what the speaker says in each talk. Select the best response to each question and mark the letter (A), (B), (C), or (D) on your answer sheet. The talks will not be printed in your test book and will be spoken only one time.",
        5: "A word or phrase is missing in each of the sentences below. Four answer choices are given below each sentence. Select the best answer to complete the sentence. Then mark the letter (A), (B), (C), or (D) on your answer sheet.",
        6: "Read the texts that follow. A word, phrase, or sentence is missing in some of the sentences. Four answer choices for each question are given below each text. Select the best answer to complete the sentence or text. Then mark the letter (A), (B), (C), or (D) on your answer sheet.",
        7: "In this part you will read a selection of texts, such as magazine and newspaper articles, letters, and advertisements. Each text is followed by several questions. Select the best answer for each question and mark the letter (A), (B), (C), or (D) on your answer sheet."
    };

    // gom nhóm câu hỏi theo part
    const questionsByPart = {};
    questions.forEach(q => {
        if (!questionsByPart[q.part]) {
            questionsByPart[q.part] = [];
        }
        questionsByPart[q.part].push(q);
    });

    let htmlContent = '';
    const parts = Object.keys(questionsByPart).map(Number).sort((a, b) => a - b);

    parts.forEach(part => {
        if (part === 5) {
            htmlContent += `
                <div id="reading-intro-warning" class="mb-5"
                    style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px;">
                    <p class="mb-0 text-dark" style="font-size: 0.9rem; line-height: 1.5; font-weight: 500;">
                        In the Reading test, you will read a variety of texts and answer several different types of reading comprehension questions.
                        The entire Reading test will last 75 minutes. There are three parts, and directions are given for each part. You are
                        encouraged to answer as many questions as possible within the time allowed. You must mark your answers on the
                        separate answer sheet. Do not write your answers in the test book.
                    </p>
                </div>
            `;
        }
        htmlContent += `
            <div class="part-section mb-5" id="part-section-${part}">
                <h3 class="part-header mb-2 text-uppercase fw-bold text-dark" style="font-size: 1.4rem; letter-spacing: 1px;">Part ${part}</h3>
                <div class="part-directions mb-4" style="font-size: 0.9rem; line-height: 1.5; text-align: justify; color: #212529;">
                    <strong>Directions:</strong> ${partDirections[part] || ''}
                </div>
                <div class="questions-grid-container">`;

        let lastParagraph = '';

        questionsByPart[part].forEach(q => {
            // kiểm tra và hiển thị tiêu đề/hình ảnh đoạn văn nếu sang nhóm mới
            if (q.paragraph && q.paragraph !== lastParagraph) {
                const hasImage = !!q.passage_image;
                const headerClass = hasImage ? 'passage-group-header grid-full-width mb-3' : 'passage-group-header grid-full-width no-image';
                
                const hasPassageText = q.passage_translation_en && q.passage_translation_en.trim() !== '';
                const isPlaceholder = !hasPassageText && q.paragraph.trim().startsWith('Questions ') && !q.paragraph.includes('<');
                let passageHtml = '';
                
                if (isPlaceholder) {
                    passageHtml = `<p class="fw-bold mb-1 text-dark" style="font-size: 1rem;">${q.paragraph}</p>`;
                } else {
                    const passageQuestions = questionsByPart[part].filter(item => item.paragraph === q.paragraph);
                    const nums = passageQuestions.map(item => parseInt(item.question_number)).filter(Number.isInteger);
                    const min = Math.min(...nums);
                    const max = Math.max(...nums);
                    
                    let rangeHeader = '';
                    let bodyHtml = '';
                    let titleHtml = '';

                    if (hasPassageText) {
                        // hiển thị tiêu đề nhóm ở content và nội dung ở translation_en
                        rangeHeader = `<p class="fw-bold mb-1 text-dark" style="font-size: 1rem;">${q.paragraph}</p>`;
                        bodyHtml = q.passage_translation_en;
                    } else {
                        // fallback cũ khi nội dung nằm ở content
                        rangeHeader = (Number.isInteger(min) && Number.isInteger(max))
                            ? `<p class="fw-bold mb-1 text-dark" style="font-size: 1rem;">Questions ${min} - ${max}:</p>`
                            : '';
                        bodyHtml = q.paragraph;
                        const headerMatch = q.paragraph.match(/^(\s*<h[1-6]>.*?<\/h[1-6]>)(.*)$/is);
                        if (headerMatch) {
                            titleHtml = headerMatch[1];
                            bodyHtml = headerMatch[2];
                        }
                    }
                    
                    passageHtml = `
                        ${rangeHeader}
                        ${titleHtml}
                        <div class="passage-text-card">
                            ${bodyHtml}
                        </div>
                    `;
                }

                htmlContent += `
                    <div class="${headerClass}">
                        ${passageHtml}
                `;
                if (hasImage) {
                    htmlContent += `
                        <img src="${q.passage_image}" class="passage-image img-fluid mb-3" style="max-height: 350px;">
                    `;
                }
                htmlContent += `</div>`;
                lastParagraph = q.paragraph;
            } else if (!q.paragraph) {
                lastParagraph = '';
            }

            const supportsImage = [1, 3, 4, 7].includes(parseInt(q.part));
            let imageHtml = '';
            if (supportsImage && q.image_url && q.image_url !== 'null' && q.image_url !== 'NULL' && q.image_url.trim() !== '') {
                imageHtml = `<img src="${q.image_url}" class="img-fluid mb-3" style="max-height: 250px; display: block;">`;
            }
            let optionsHtml = '';
            
            q.options.forEach(opt => {
                const trimmed = opt.content.trim();
                const isPlaceholder = trimmed === '' || 
                                    trimmed.toUpperCase() === opt.label.toUpperCase() || 
                                    trimmed.toUpperCase() === `(${opt.label.toUpperCase()})`;
                const optContent = isPlaceholder ? '' : ' ' + opt.content;
                optionsHtml += `
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="q${q.question_number}" data-q-id="${q.id}" id="q${q.question_number}_${opt.label}" value="${opt.label}">
                        <label class="form-check-label" for="q${q.question_number}_${opt.label}">
                            <span class="text-secondary">(${opt.label})</span>${optContent}
                        </label>
                    </div>
                `;
            });

            const displayContent = q.content || (q.part === 2 ? 'Mark your answer on your answer sheet.' : '');

            htmlContent += `
                <div class="d-flex mb-4" id="question-${q.question_number}">
                    <div class="q-number me-3">${q.question_number}</div>
                    <div class="grow">
                        ${imageHtml}
                        <p class="mb-2" style="font-size: 0.95rem; line-height: 1.4; color: #333;">${displayContent}</p>
                        ${optionsHtml}
                    </div>
                </div>
            `;
        });

        htmlContent += `
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
