const urlParams = new URLSearchParams(window.location.search);
const ATTEMPT_ID = urlParams.get('attempt_id') || 1; 
const API_URL = `/prephub/IS207-UIT/api/score/details?attempt_id=${ATTEMPT_ID}`;
const DOMAIN = '/prephub/IS207-UIT';

const TOEIC_CONVERSION = {
    listening: { 0: 5, 5: 25, 10: 55, 20: 110, 50: 245, 80: 395, 90: 450, 100: 495 },
    reading: { 0: 5, 5: 20, 10: 50, 20: 105, 50: 235, 80: 380, 90: 440, 100: 495 }
};

const TOEIC_PARTS = [
    { name: "Part 1: Ảnh", range: [1, 6] },
    { name: "Part 2: Câu hỏi ngắn", range: [7, 31] },
    { name: "Part 3: Hội thoại", range: [32, 70] },
    { name: "Part 4: Độc thoại", range: [71, 100] },
    { name: "Part 5: Đọc câu hoàn chỉnh", range: [101, 130] },
    { name: "Part 6: Điền từ", range: [131, 146] },
    { name: "Part 7: Đọc hiểu", range: [147, 200] }
];

function calculateFinalScore(correctCount, section) {
    const table = TOEIC_CONVERSION[section];
    const keys = Object.keys(table).map(Number).sort((a, b) => a - b);
    let lower = keys[0], upper = keys[keys.length - 1];

    for (let i = 0; i < keys.length; i++) {
        if (keys[i] <= correctCount) lower = keys[i];
        if (keys[i] >= correctCount) { upper = keys[i]; break; }
    }
    if (lower === upper) return table[lower];
    
    const ratio = (correctCount - lower) / (upper - lower);
    const score = table[lower] + ratio * (table[upper] - table[lower]);
    return Math.round(score / 5) * 5;
}

$(document).ready(async function() {
    try {
        const response = await fetch(API_URL);
        const json = await response.json();

        if (json.status === 'success' && json.data) {
            processAndRender(json.data);
        } else {
            $('#wrong-questions-list').html('<div class="p-5 text-center text-muted">Không tìm thấy dữ liệu bài làm trong hệ thống.</div>');
        }
    } catch (error) {
        console.error("Lỗi API:", error);
        $('#wrong-questions-list').html('<div class="p-5 text-center text-danger">Đã xảy ra lỗi khi kết nối với máy chủ. Vui lòng thử lại sau.</div>');
    }
});

function processAndRender(questions) {
    let lcCorrect = 0, rcCorrect = 0, totalCorrect = 0;

    questions.forEach(q => {
        q.user_choice = q.user_choice ? q.user_choice.toUpperCase() : '';
        q.correct_option = q.correct_option ? q.correct_option.toUpperCase() : '';
        q.status = (q.user_choice === q.correct_option) && (q.user_choice !== '');

        if (q.status) {
            totalCorrect++;
            if (q.question_id <= 100) lcCorrect++;
            else rcCorrect++;
        }
    });

    const lcScore = calculateFinalScore(lcCorrect, 'listening');
    const rcScore = calculateFinalScore(rcCorrect, 'reading');
    
    $('#total-points').text(lcScore + rcScore);
    $('#listening-points').text(`${lcScore}/495`);
    $('#reading-points').text(`${rcScore}/495`);
    $('#accuracy-rate').text(`${((totalCorrect / 200) * 100).toFixed(1)}%`);

    renderReviewList(questions);
    renderAnswerGrid(questions);
}

function renderReviewList(questions) {
    let html = '';
    questions.forEach(item => {
        const part = TOEIC_PARTS.find(p => item.question_id >= p.range[0] && item.question_id <= p.range[1]) || { name: `Part ${item.part}` };
        
        let mediaHtml = '';
        if (item.image_url) mediaHtml += `<img src="${DOMAIN}${item.image_url}" class="img-fluid rounded mt-2 mb-2" style="max-height:300px; display:block;">`;
        if (item.audio_url) mediaHtml += `<audio controls src="${DOMAIN}${item.audio_url}" class="w-100 mt-2 mb-2"></audio>`;
        
        const contentHtml = item.question_content || (mediaHtml ? '' : '<i>Nội dung câu hỏi đang được tải từ hệ thống...</i>');

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
    $('#wrong-questions-list').html(html);
}

function renderAnswerGrid(questions) {
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
}

function scrollToQuestion(qNo) {
    const target = $(`#question-target-${qNo}`);
    if (target.length) {
        const scrollPos = target.offset().top - 40; 
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
        if ($('.question-item:visible').length === 0 && !$('#no-wrong-msg').length) {
            $('#wrong-questions-list').append('<div id="no-wrong-msg" class="p-5 text-center text-success fw-bold">Tuyệt vời! Bạn không sai câu nào.</div>');
        }
    } else {
        $('.question-item').show();
        $('#no-wrong-msg').remove();
    }
    $('html, body').animate({ scrollTop: 0 }, 300);
});