/**
 * PHẦN 1: THU THẬP ĐÁP ÁN (SUBMIT LOGIC)
 * Nhiệm vụ: Quét toàn bộ các radio button đã chọn 
 */
function getSelectedAnswers() {
    const userAnswers = {};
    
    // Tìm tất cả các input radio đã được check
    // Giả sử P2 đặt name cho mỗi câu là q1, q2, ..., q200
const container = document.getElementById('question-list-container');
if (!container) return userAnswers;
const selectedInputs = container.querySelectorAll('input[type="radio"]:checked');

    selectedInputs.forEach(input => {
        // Lấy số câu từ name (Ví dụ: name="q101" -> lấy 101)
        const questionNumber = input.name.replace('q', ''); 
        userAnswers[questionNumber] = input.value; // Lưu giá trị A, B, C hoặc D
    });

    return userAnswers;
}

 /** 
 * PHẦN 2: XỬ LÝ KHI BẤM NÚT NỘP BÀI
 */
function handleSubmit() {
    // 1. Xác nhận nộp bài
    if (!confirm("Bạn có chắc chắn muốn nộp bài và kết thúc bài thi?")) {
        return;
    }

    // 2. Dừng đồng hồ (nếu có biến timer)
    if (typeof timerInterval !== 'undefined') {
        clearInterval(timerInterval);
    }

    // 3. Thu thập đáp án
    const finalAnswers = getSelectedAnswers();

    // 4. Kiểm tra xem user đã làm được bao nhiêu câu (để nhắc nhở nếu cần)
    const answeredCount = Object.keys(finalAnswers).length;
    console.log(`User đã hoàn thành: ${answeredCount}/200 câu`);

    // 5. Chuyển sang Logic Chấm điểm 
    const scoreResult = calculateScore(finalAnswers);

    // 6. Lưu vào LocalStorage để Dashboard hiển thị
    saveToHistory(scoreResult);
}

/**
 * PHẦN 3: LOGIC CHẤM ĐIỂM (SCORING LOGIC)
 */
function calculateScore(userAnswers) {
    // 1. Khởi tạo bộ đếm
    let listeningCorrect = 0;
    let readingCorrect = 0;
    const details = []; // Lưu chi tiết đúng/sai để dùng cho trang Results 

    // 2. Duyệt qua 200 câu 
    // sampleCorrectAnswers là Object/Mảng chứa đáp án đúng
    for (let i = 1; i <= 200; i++) {
        const uAns = userAnswers[i] || null; // Đáp án user (nếu bỏ trống là null)
        const cAns = sampleCorrectAnswers[i]; // Đáp án đúng từ data mẫu

        const isCorrect = (uAns === cAns);

        if (isCorrect) {
            if (i <= 100) listeningCorrect++; // Từ câu 1-100 là Listening
            else readingCorrect++;           // Từ câu 101-200 là Reading
        }

        // Lưu lại để sau này làm trang Review (Tuần 3)
        details.push({
            question_no: i,
            user_ans: uAns,
            correct_ans: cAns,
            status: isCorrect
        });
    }

    // 3. Quy đổi điểm
    const lScore = listeningCorrect * 5;
    const rScore = readingCorrect * 5;
    const totalScore = lScore + rScore;

    // 4. Đóng gói kết quả
    const finalResult = {
        test_id: 601, // ID đề thi (Ví dụ ETS 2024 Test 1)
        test_title: document.getElementById('exam-title')?.innerText || "TOEIC Test",
        listening_score: lScore,
        reading_score: rScore,
        total_score: totalScore,
        correct_count: (listeningCorrect + readingCorrect),
        listening_correct: listeningCorrect,
        reading_correct: readingCorrect,
        details: details, // Dữ liệu thô để làm Review
        created_at: new Date().toISOString()
    };

    return finalResult;
}
/**
 * PHẦN 4: LƯU TRỮ
 */
function saveToHistory(result) {
    // Lấy dữ liệu cũ từ localStorage
    let history = JSON.parse(localStorage.getItem('toeic_history')) || mockAttempts;
    
    // Thêm kết quả mới vào đầu danh sách
    history.unshift(result);
    
    // Lưu ngược lại vào localStorage
    localStorage.setItem('toeic_history', JSON.stringify(history));

    // Thông báo và chuyển hướng
    alert(`Nộp bài thành công! Tổng điểm: ${result.total_score}`);
    window.location.href = 'attempts.php'; // Chuyển về trang Lịch sử 
}