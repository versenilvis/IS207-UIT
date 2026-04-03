$(document).ready(function() {
    // 1. Mock Data (Giả lập dữ liệu từ bảng attempts trong DB của bạn)
    const mockAttempts = [
        { uuid: 'u1', test_title: 'Economy Vol 1 - Test 01', listening_score: 300, reading_score: 250, total_score: 550, time_spent: 5400, created_at: '2026-03-25' },
        { uuid: 'u2', test_title: 'Economy Vol 1 - Test 02', listening_score: 350, reading_score: 300, total_score: 650, time_spent: 6000, created_at: '2026-03-28' },
        { uuid: 'u3', test_title: 'Economy Vol 1 - Test 03', listening_score: 400, reading_score: 380, total_score: 780, time_spent: 4800, created_at: '2026-04-01' }
    ];

    // 2. Hàm định dạng thời gian (giây -> phút)
    function formatTime(seconds) {
        let m = Math.floor(seconds / 60);
        return m + " phút";
    }

    // 3. Đổ dữ liệu vào Stats Cards
    let maxScore = Math.max(...mockAttempts.map(a => a.total_score));
    $('#max-score').text(maxScore);
    $('#total-tests').text(mockAttempts.length);
    $('#avg-time').text(formatTime(mockAttempts[0].time_spent));

    // 4. Render Table
    let tableHtml = '';

mockAttempts.forEach(attempt => {
    tableHtml += `
        <tr>
            <td>${attempt.created_at}</td>
            <td>${attempt.test_title}</td>
            <td class="text-center text-primary fw-bold">${attempt.listening_score}</td>
            <td class="text-center text-success fw-bold">${attempt.reading_score}</td>
            <td class="text-center">
                <span class="badge bg-warning text-dark">${attempt.total_score}</span>
            </td>
            <td class="text-center">${formatTime(attempt.time_spent)}</td>
            
            <td class="text-end">
                <a href="results.html?id=${attempt.uuid}" class="btn btn-sm btn-outline-primary shadow-sm">
                    <i class="fa-solid fa-eye me-1"></i> Chi tiết
                </a>
            </td>
        </tr>
    `;
});
$('#history-body').html(tableHtml);

    // 5. Vẽ biểu đồ bằng Chart.js
    const ctx = document.getElementById('scoreChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: mockAttempts.map(a => a.created_at),
            datasets: [{
                label: 'Tổng điểm TOEIC',
                data: mockAttempts.map(a => a.total_score),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { min: 0, max: 990 } }
        }
    });
});