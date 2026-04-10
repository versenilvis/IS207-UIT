<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết bài thi - Nhân P4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-box {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 5px; margin: 5px; font-weight: bold; font-size: 14px;
            cursor: pointer; border: 1px solid #ddd;
        }
        .correct { background-color: #28a745; color: white; border: none; }
        .wrong { background-color: #dc3545; color: white; border: none; }
        .not-answered { background-color: #f8f9fa; color: #666; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h3 class="mb-4">Chi tiết đáp án - Lần thi #1</h3>
        
        <div class="row">
            <div class="col-md-8">
                <h5>Bảng đáp án (Part 1 - 7)</h5>
                <div id="answer-grid" class="d-flex flex-wrap">
                    </div>
            </div>

            <div class="col-md-4">
<div class="p-3 border rounded bg-white mb-3 shadow-sm">
        <h6 class="fw-bold mb-3 text-center border-bottom pb-2">Kết quả bài thi</h6>
        
        <div class="d-flex justify-content-between mb-2">
            <span>Tổng câu đúng:</span>
            <span class="fw-bold text-success" id="summary-correct">0</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Tổng câu sai:</span>
            <span class="fw-bold text-danger" id="summary-wrong">0</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Listening (LC):</span>
            <span class="fw-bold text-primary" id="summary-lc">0/100</span>
        </div>
        <div class="d-flex justify-content-between mb-3">
            <span>Reading (RC):</span>
            <span class="fw-bold text-info" id="summary-rc">0/100</span>
        </div>

        <a href="dashboard.php" class="btn btn-primary w-100">Quay lại Dashboard</a>
    </div>

    <div class="p-3 border rounded bg-white shadow-sm">
        <h6 class="fw-bold mb-3 text-muted">Chú thích</h6>
        <div class="d-flex align-items-center mb-2">
            <div class="question-box correct m-0 me-2" style="width: 25px; height: 25px;"></div> 
            <span style="font-size: 0.9rem;">Câu trả lời đúng</span>
        </div>
        <div class="d-flex align-items-center">
            <div class="question-box wrong m-0 me-2" style="width: 25px; height: 25px;"></div> 
            <span style="font-size: 0.9rem;">Câu trả lời sai</span>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 1. Lấy dữ liệu từ LocalStorage (Dữ liệu thật từ file scoring.js của Nhân)
    const history = JSON.parse(localStorage.getItem('toeic_history')) || [];
    let latestResult = history[0];

    // 2. Nếu không có dữ liệu trong LocalStorage (lần đầu chạy), tạo dữ liệu giả để demo
    if (!latestResult) {
        console.log("Sử dụng dữ liệu giả lập để demo...");
        const mockDetails = [];
        let correctCount = 0;
        let lcCorrect = 0;
        let rcCorrect = 0;

        for (let i = 1; i <= 200; i++) {
            const isCorrect = Math.random() > 0.4;
            if (isCorrect) {
                correctCount++;
                if (i <= 100) lcCorrect++; else rcCorrect++;
            }
            mockDetails.push({
                question_no: i,
                status: isCorrect,
                user_ans: 'A', // giả lập
                correct_ans: 'A' // giả lập
            });
        }
        
        latestResult = {
            test_title: "ETS 2024 - Test 1 (Demo)",
            correct_count: correctCount,
            listening_correct: lcCorrect,
            reading_correct: rcCorrect,
            details: mockDetails
        };
    }

    // 3. Đổ dữ liệu vào các ô tóm tắt (Summary)
    $('#summary-correct').text(latestResult.correct_count);
    $('#summary-wrong').text(200 - latestResult.correct_count);
    $('#summary-lc').text(`${latestResult.listening_correct}/100`);
    $('#summary-rc').text(`${latestResult.reading_correct}/100`);

    // 4. Đổ dữ liệu vào Grid (200 ô vuông)
    let gridHtml = '';
    latestResult.details.forEach(item => {
        let statusClass = item.status ? 'correct' : 'wrong';
        // Thêm thuộc tính title để khi di chuột vào sẽ hiện đáp án
        let hoverText = `Câu ${item.question_no}: Chọn ${item.user_ans || 'Trống'} - Đáp án đúng: ${item.correct_ans}`;
        
        gridHtml += `<div class="question-box ${statusClass}" title="${hoverText}">${item.question_no}</div>`;
    });
    
    $('#answer-grid').html(gridHtml);
});
</script>
</body>
</html>