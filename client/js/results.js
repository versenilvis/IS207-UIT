(function() {
    const answers = ['A', 'B', 'C', 'D'];
    const fakeDetails = [];
    let lcCorrect = 0; let rcCorrect = 0;

    for (let i = 1; i <= 200; i++) {
        const correctAns = answers[Math.floor(Math.random() * answers.length)];
        let userAns = '';
        let status = false;

        const rand = Math.random();
        if (rand > 0.15) { // 85% có làm bài
            userAns = answers[Math.floor(Math.random() * answers.length)];
            status = (userAns === correctAns);
        } else { // 15% bỏ trống
            userAns = ''; 
            status = false;
        }

        if (status) {
            if (i <= 100) lcCorrect++; else rcCorrect++;
        }

        fakeDetails.push({
            question_no: i,
            status: status,
            user_ans: userAns,
            correct_ans: correctAns
        });
    }

    const fakeHistory = [{
        test_title: "ETS 2024 - FULL TEST MOCKUP",
        created_at: new Date().toLocaleString('vi-VN'),
        correct_count: lcCorrect + rcCorrect,
        listening_correct: lcCorrect,
        reading_correct: rcCorrect,
        details: fakeDetails
    }];

    localStorage.setItem('toeic_history', JSON.stringify(fakeHistory));
    console.log("✅ Mock Data: Sẵn sàng với 3 trạng thái (Đúng/Sai/Trống)");
})();

// --- 2. CẤU HÌNH & LOGIC TÍNH ĐIỂM ---
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

// --- 3. XỬ LÝ KHI TRANG SẴN SÀNG ---
$(document).ready(function() {
    const history = JSON.parse(localStorage.getItem('toeic_history')) || [];
    const data = history[0];

    if (!data) {
        $('#wrong-questions-list').html('<div class="p-5 text-center text-muted">Chưa có dữ và liệu bài làm.</div>');
        return;
    }

    // Hiển thị điểm số
    const lcScore = calculateFinalScore(data.listening_correct, 'listening');
    const rcScore = calculateFinalScore(data.reading_correct, 'reading');
    $('#total-points').text(lcScore + rcScore);
    $('#listening-points').text(`${lcScore}/495`);
    $('#reading-points').text(`${rcScore}/495`);
    $('#accuracy-rate').text(`${((data.correct_count / 200) * 100).toFixed(1)}%`);

    // Render nội dung
    renderReviewList(data.details);
    renderAnswerGrid(data.details);
});

// --- 4. CÁC HÀM RENDER UI ---
function renderReviewList(details) {
    let html = '';
    details.forEach(item => {
        const part = TOEIC_PARTS.find(p => item.question_no >= p.range[0] && item.question_no <= p.range[1]);
        html += `
            <div class="p-4 border-bottom question-item" id="question-target-${item.question_no}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge ${item.status ? 'bg-success' : 'bg-danger'} rounded-pill px-3">Câu ${item.question_no}</span>
                    <small class="text-muted fw-bold text-uppercase">${part.name}</small>
                </div>
                <div class="p-3 bg-light rounded border-start border-4 ${item.status ? 'border-success' : 'border-danger'} mb-3">
                    <p class="text-secondary small mb-0"><i>Nội dung câu hỏi đang được tải từ hệ thống...</i></p>
                </div>
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-2 border rounded ${item.status ? 'bg-white' : 'bg-danger-subtle'}">
                            <small class="d-block text-muted">Bạn chọn</small>
                            <span class="fw-bold fs-5">${item.user_ans || '-'}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-success-subtle">
                            <small class="d-block text-muted">Đáp án đúng</small>
                            <span class="fw-bold fs-5 text-success">${item.correct_ans}</span>
                        </div>
                    </div>
                </div>
            </div>`;
    });
    $('#wrong-questions-list').html(html);
}

function renderAnswerGrid(details) {
    let gridHtml = '';
    TOEIC_PARTS.forEach(part => {
        const partQuestions = details.filter(q => q.question_no >= part.range[0] && q.question_no <= part.range[1]);

        gridHtml += `<div class="part-header text-uppercase small fw-bold mt-3 mb-2">${part.name}</div>`;
        gridHtml += `<div class="d-flex flex-wrap gap-1">`;
        
        partQuestions.forEach(item => {
            // Logic xác định class màu sắc
            let statusClass = 'unanswered'; 
            if (item.user_ans !== '') {
                statusClass = item.status ? 'correct' : 'wrong';
            }

            gridHtml += `
                <div class="question-box ${statusClass}" onclick="scrollToQuestion(${item.question_no})">
                    ${item.question_no}
                </div>`;
        });
        gridHtml += `</div>`;
    });
    $('#answer-grid').html(gridHtml);
}

// --- 5. HÀM ĐIỀU HƯỚNG CUỘN (SCROLL) ---
function scrollToQuestion(qNo) {
    const container = $('#wrong-questions-list');
    const target = $(`#question-target-${qNo}`);
    
    if (target.length) {
        const scrollPos = target.offset().top - container.offset().top + container.scrollTop();
        
        container.animate({
            scrollTop: scrollPos - 10
        }, 500);
        
        $('.question-item').removeClass('bg-warning-light');
        target.addClass('bg-warning-light');
        setTimeout(() => target.removeClass('bg-warning-light'), 2000);
    }
}

// --- LOGIC LỌC CÂU HỎI (TẤT CẢ / CÂU SAI) ---
$('.btn-group button').on('click', function() {
    // 1. Thay đổi trạng thái hiển thị của nút
    $('.btn-group button').removeClass('active');
    $(this).addClass('active');

    const filterType = $(this).data('filter');

    if (filterType === 'wrong') {
        // 2. Ẩn các câu đúng, chỉ hiện câu sai
        $('.question-item').each(function() {
            // Kiểm tra nếu không có badge 'bg-danger' (tức là câu đúng) thì ẩn đi
            if ($(this).find('.badge.bg-danger').length === 0) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        
        // Thông báo nếu không có câu sai nào
        if ($('.question-item:visible').length === 0) {
            if (!$('#no-wrong-msg').length) {
                $('#wrong-questions-list').append('<div id="no-wrong-msg" class="p-5 text-center text-success fw-bold">Tuyệt vời! Bạn không sai câu nào.</div>');
            }
        }
    } else {
        // 3. Hiện lại tất cả các câu
        $('.question-item').show();
        $('#no-wrong-msg').remove();
    }
    
    // Cuộn lên đầu danh sách sau khi lọc để người dùng dễ theo dõi
    $('#wrong-questions-list').animate({ scrollTop: 0 }, 300);
});