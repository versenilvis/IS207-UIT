const urlParams = new URLSearchParams(window.location.search);
const ATTEMPT_ID = urlParams.get('attempt_id');
const API_URL = `/api/score?attempt_id=${ATTEMPT_ID}`;

<<<<<<< HEAD
// Định nghĩa các Part của TOEIC để hiển thị trên Sidebar và Review
const TOEIC_PARTS = [
    { name: "Part 1: Ảnh", range: [1, 6] },
    { name: "Part 2: Câu hỏi ngắn", range: [7, 31] },
    { name: "Part 3: Hội thoại", range: [32, 70] },
    { name: "Part 4: Độc thoại", range: [71, 100] },
    { name: "Part 5: Đọc câu hoàn chỉnh", range: [101, 130] },
    { name: "Part 6: Điền từ", range: [131, 146] },
    { name: "Part 7: Đọc hiểu", range: [147, 200] }
];

$(document).ready(async function() {
    if (!ATTEMPT_ID) {
        alert("Không tìm thấy ID bài làm!");
        return;
    }

    try {
        const response = await fetch(API_URL);
        const json = await response.json();
=======
const TOEIC_PARTS = [
    { name: "Part 1: Ảnh",              range: [1, 6] },
    { name: "Part 2: Câu hỏi ngắn",     range: [7, 31] },
    { name: "Part 3: Hội thoại",         range: [32, 70] },
    { name: "Part 4: Độc thoại",         range: [71, 100] },
    { name: "Part 5: Đọc câu hoàn chỉnh", range: [101, 130] },
    { name: "Part 6: Điền từ",           range: [131, 146] },
    { name: "Part 7: Đọc hiểu",          range: [147, 200] }
];

$(document).ready(async function () {
    if (!ATTEMPT_ID) { alert("Không tìm thấy ID bài làm!"); return; }

    try {
        const res = await fetch(API_URL);
        const json = await res.json();
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8

        if (json.status === 'success' && json.summary) {
            renderResults(json.summary, json.data);
        } else {
<<<<<<< HEAD
            $('#wrong-questions-list').html('<div class="p-5 text-center text-muted">Không tìm thấy dữ liệu bài làm trong hệ thống.</div>');
        }
    } catch (error) {
        console.error("Lỗi API:", error);
        $('#wrong-questions-list').html('<div class="p-5 text-center text-danger">Đã xảy ra lỗi khi kết nối với máy chủ. Vui lòng thử lại sau.</div>');
    }
});

/**
 * Hiển thị kết quả tổng quát và chi tiết
 */
function renderResults(summary, questions) {
    $('#total-points').text(summary.total_score);
    $('#listening-points').text(`${summary.listening_score}/495`);
    $('#reading-points').text(`${summary.reading_score}/495`);
    
    // Gắn link làm lại bài
    if (summary.test_uuid) {
        $('#btn-retake').attr('href', `exam.php?uuid=${summary.test_uuid}`);
    }

    const totalCorrect = parseInt(summary.listening_correct) + parseInt(summary.reading_correct);
    const accuracy = ((totalCorrect / 200) * 100).toFixed(1);
    $('#accuracy-rate').text(`${accuracy}%`);

    questions.forEach(q => {
        q.user_choice = q.user_choice ? q.user_choice.toUpperCase() : '';
        q.correct_option = q.correct_option ? q.correct_option.toUpperCase() : '';
        // Một câu được coi là đúng nếu user chọn trùng với đáp án và không để trống
=======
            $('#wrong-questions-list').html('<div class="p-5 text-center" style="color:#94a3b8;">Không tìm thấy dữ liệu bài làm.</div>');
        }
    } catch (e) {
        console.error(e);
        $('#wrong-questions-list').html('<div class="p-5 text-center text-danger">Lỗi kết nối máy chủ. Thử lại sau.</div>');
    }

    // filter toggle – event delegation so it works after dynamic render
    $(document).on('click', '#filter-all', function () {
        $('#filter-all').addClass('active');
        $('#filter-wrong').removeClass('active');
        $('.question-item').show();
    });

    $(document).on('click', '#filter-wrong', function () {
        $('#filter-wrong').addClass('active');
        $('#filter-all').removeClass('active');
        $('.question-item').each(function () {
            // data-correct="1" means correct → hide; "" means wrong → show
            $(this).attr('data-correct') === '1' ? $(this).hide() : $(this).show();
        });
    });
});

function renderResults(summary, questions) {
    $('#total-points').text(summary.total_score);
    $('#listening-points').html(`${summary.listening_score}<span class="stat-sub">/495</span>`);
    $('#reading-points').html(`${summary.reading_score}<span class="stat-sub">/495</span>`);

    if (summary.test_uuid) $('#btn-retake').attr('href', `exam.php?uuid=${summary.test_uuid}`);

    const total = parseInt(summary.listening_correct) + parseInt(summary.reading_correct);
    $('#accuracy-rate').text(((total / 200) * 100).toFixed(1) + '%');

    questions.forEach(q => {
        q.user_choice    = q.user_choice    ? q.user_choice.toUpperCase()    : '';
        q.correct_option = q.correct_option ? q.correct_option.toUpperCase() : '';
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
        q.status = (q.user_choice === q.correct_option) && (q.user_choice !== '');
    });

    renderReviewList(questions);
    renderAnswerGrid(questions);
}

function renderReviewList(questions) {
    let html = '';
<<<<<<< HEAD
    questions.forEach(item => {
        const part = TOEIC_PARTS.find(p => item.question_id >= p.range[0] && item.question_id <= p.range[1]) || { name: `Part ${item.part}` };
        
        let mediaHtml = '';
        if (item.image_url) mediaHtml += `<img src="${item.image_url}" class="img-fluid rounded mt-2 mb-2" style="max-height:300px; display:block;">`;
        if (item.audio_url) mediaHtml += `<audio controls src="${item.audio_url}" class="w-100 mt-2 mb-2"></audio>`;
        
        const contentHtml = item.question_content || (mediaHtml ? '' : '<i>Nội dung câu hỏi không khả dụng.</i>');

        html += `
            <div class="p-4 border-bottom question-item" id="question-target-${item.question_id}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge ${item.status ? 'bg-success' : 'bg-danger'} rounded-pill px-3">Câu ${item.question_id}</span>
                    <small class="text-muted fw-bold text-uppercase">${part.name}</small>
                </div>
                <div class="p-3 bg-light rounded border-start border-4 ${item.status ? 'border-success' : 'border-danger'} mb-3">
                    <p class="text-secondary small mb-0">${contentHtml}</p>
                    ${mediaHtml}
                </div>
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-2 border rounded ${item.status ? 'bg-white' : 'bg-danger-subtle'}">
                            <small class="d-block text-muted">Bạn chọn</small>
                            <span class="fw-bold fs-5">${item.user_choice || '-'}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-success-subtle">
                            <small class="d-block text-muted">Đáp án đúng</small>
                            <span class="fw-bold fs-5 text-success">${item.correct_option}</span>
                        </div>
                    </div>
                </div>
            </div>`;
    });
=======

    TOEIC_PARTS.forEach(part => {
        const partQuestions = questions.filter(q => q.question_number >= part.range[0] && q.question_number <= part.range[1]);
        if (!partQuestions.length) return;

        // part heading
        html += `<div class="part-section-header">${part.name}</div>`;

        partQuestions.forEach(item => {
            const ok = item.status;
            let mediaHtml = '';
            if (item.image_url) mediaHtml += `<img src="${item.image_url}" class="img-fluid rounded mt-2" style="max-height:280px;">`;
            if (item.audio_url) mediaHtml += `<audio controls src="${item.audio_url}" class="w-100 mt-2"></audio>`;

            const contentHtml = item.question_content || (mediaHtml ? '' : '<i style="color:#94a3b8;">Nội dung không khả dụng.</i>');
            
            // Generate A, B, C, D options with full text
            const numOptions = (item.question_number >= 7 && item.question_number <= 31) ? 3 : 4;
            const optionsList = ['A', 'B', 'C', 'D'].slice(0, numOptions);
            let optionsHtml = '';
            
            optionsList.forEach(opt => {
                let textClass = 'ans-text';
                
                if (opt === item.correct_option) {
                    textClass += ' fw-bold text-success';
                } else if (opt === item.user_choice && opt !== item.correct_option) {
                    textClass += ' fw-bold text-danger';
                }
                
                const optText = (item.options && item.options[opt]) ? item.options[opt] : '';
                const displayOptText = optText ? `<span class="fw-bold me-2">${opt}.</span> ${optText}` : `<span class="fw-bold">${opt}.</span>`;

                optionsHtml += `
                <div class="mb-2">
                    <div class="${textClass}">${displayOptText}</div>
                </div>`;
            });

            html += `
            <div class="question-item" id="question-target-${item.question_number}" data-correct="${ok ? '1' : ''}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="q-num-badge ${ok ? 'correct' : 'wrong'}">
                        <i class="bx ${ok ? 'bx-check' : 'bx-x'}"></i> Câu ${item.question_number}
                    </span>
                </div>
                <div class="q-content ${ok ? 'correct' : 'wrong'}">
                    <span>${contentHtml}</span>
                    ${mediaHtml}
                </div>
                <div class="options-list mt-3">
                    ${optionsHtml}
                </div>
            </div>`;
        });
    });

>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
    $('#wrong-questions-list').html(html);
}

function renderAnswerGrid(questions) {
<<<<<<< HEAD
    let gridHtml = '';
    TOEIC_PARTS.forEach(part => {
        const partQuestions = questions.filter(q => q.question_id >= part.range[0] && q.question_id <= part.range[1]);
        if (partQuestions.length === 0) return;

        gridHtml += `<div class="part-header text-uppercase small fw-bold mt-3 mb-2">${part.name}</div>`;
        gridHtml += `<div class="d-flex flex-wrap gap-1">`;
        
        partQuestions.forEach(item => {
            let statusClass = 'unanswered'; 
            if (item.user_choice !== '') {
                statusClass = item.status ? 'correct' : 'wrong';
            }
            gridHtml += `
                <div class="question-box ${statusClass}" onclick="scrollToQuestion(${item.question_id})">
                    ${item.question_id}
                </div>`;
        });
        gridHtml += `</div>`;
    });
    $('#answer-grid').html(gridHtml);
=======
    let html = '';
    TOEIC_PARTS.forEach(part => {
        const pq = questions.filter(q => q.question_number >= part.range[0] && q.question_number <= part.range[1]);
        if (!pq.length) return;
        html += `<div class="part-label">${part.name}</div><div style="display:flex;flex-wrap:wrap;">`;
        pq.forEach(item => {
            let cls = 'unanswered';
            if (item.user_choice) cls = item.status ? 'correct' : 'wrong';
            html += `<div class="question-box ${cls}" onclick="scrollToQuestion(${item.question_number})">${item.question_number}</div>`;
        });
        html += '</div>';
    });
    $('#answer-grid').html(html);
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
}

function scrollToQuestion(qNo) {
    const target = $(`#question-target-${qNo}`);
<<<<<<< HEAD
    if (target.length) {
        const scrollPos = target.offset().top - 120; 
        $('html, body').animate({ scrollTop: scrollPos }, 500);
        $('.question-item').removeClass('bg-warning-light');
        target.addClass('bg-warning-light');
        setTimeout(() => target.removeClass('bg-warning-light'), 2000);
    }
}

$('.btn-group button').on('click', function() {
    $('.btn-group button').removeClass('active');
    $(this).addClass('active');

    if ($(this).data('filter') === 'wrong') {
        $('.question-item').each(function() {
            $(this).find('.badge.bg-danger').length === 0 ? $(this).hide() : $(this).show();
        });
    } else {
        $('.question-item').show();
    }
});
=======
    if (!target.length) return;
    
    const container = $('#wrong-questions-list');
    container.animate({
        scrollTop: container.scrollTop() + target.offset().top - container.offset().top - 30
    }, 300);
    
    $('.question-item').removeClass('highlight-flash');
    target.addClass('highlight-flash');
    setTimeout(() => target.removeClass('highlight-flash'), 500);
}
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
