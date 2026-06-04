/**
 * PHẦN 1: THU THẬP ĐÁP ÁN (SUBMIT LOGIC)
 */
function getSelectedAnswers() {
    const userAnswers = {};
    const container = document.getElementById('question-list-container');
    if (!container) return userAnswers;

    // Tìm tất cả checkbox đã chọn bằng data-q-id
    const selectedInputs = container.querySelectorAll('input[type="radio"]:checked');

    selectedInputs.forEach(input => {
        const questionId = input.getAttribute('data-q-id'); 
        userAnswers[questionId] = input.value; // Lưu A, B, C, D
    });

    return userAnswers;
}

/**
 * PHẦN 2: XỬ LÝ KHI BẤM NÚT NỘP BÀI
 */
async function handleSubmit() {
    if (!confirm("Bạn có chắc chắn muốn nộp bài và kết thúc bài thi?")) {
        return;
    }

    // 1. Thu thập dữ liệu
    const params = new URLSearchParams(window.location.search); // /exam.php?uuid=xxxx-yyyy-zzzz
    const testUuid = params.get("uuid"); // uuid = xxxx-yyyy-zzzz
    
    const finalAnswers = getSelectedAnswers();
    
    // Tính thời gian đã làm bài (phút) = (Tổng - Còn lại) / 60
    const secondsSpent = (typeof totalDuration !== 'undefined' && typeof timeLeft !== 'undefined') 
                        ? (totalDuration - timeLeft) 
                        : 0;
    const timeSpent = Math.floor(secondsSpent / 60);

    try {
        // Hiện loading hoặc khóa nút nộp để tránh bấm nhiều lần
        console.log("Đang nộp bài cho đề thi UUID:", testUuid);

        // 2. Gửi lên Server để chấm điểm
        const response = await fetch('/api/score', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                test_id: testUuid, // Gửi UUID, Backend sẽ tự tìm ID
                answers: finalAnswers,
                time_spent: timeSpent
            })
        });

        const result = await response.json();

        if (result.status === 'success') {
            // 3. Xóa trạng thái lưu tạm
            localStorage.removeItem(`exam_progress_${testUuid}`);
            
            alert(`Nộp bài thành công! Tổng điểm của bạn: ${result.total_score}`);
            
            // 4. Chuyển hướng bằng attempt_id thật từ DB
            window.location.href = `results.php?attempt_id=${result.attempt_id}`;
        } else {
            alert("Lỗi khi nộp bài: " + result.message);
        }

    } catch (error) {
        console.error("Lỗi kết nối:", error);
        alert("Không thể kết nối đến máy chủ. Vui lòng thử lại sau!");
    }
}