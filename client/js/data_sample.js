$(document).ready(function() {
    // 1. Mock Data (Giả lập dữ liệu từ bảng attempts trong DB của bạn)
    const mockAttempts = [
        { uuid: 'u1', test_title: 'ETS 2024 - Test 1 ', listening_score: 300, reading_score: 250, total_score: 550, time_spent: 5400, created_at: '2026-03-25' },
        { uuid: 'u2', test_title: 'ETS 2024 - Test 02', listening_score: 350, reading_score: 300, total_score: 650, time_spent: 6000, created_at: '2026-03-28' },
        { uuid: 'u3', test_title: 'ETS 2024 - Test 03', listening_score: 400, reading_score: 380, total_score: 780, time_spent: 4800, created_at: '2026-04-01' }
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
                <a href="results.php" class="btn btn-sm btn-outline-primary shadow-sm">
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

const ctx = document.getElementById('scoreChart').getContext('2d');

// Tạo Gradient (IOT thường dùng cái này để biểu đồ nhìn sang hơn)
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(78, 115, 223, 0.2)'); // Xanh nhạt ở trên
gradient.addColorStop(1, 'rgba(78, 115, 223, 0)');   // Trong suốt ở dưới

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(item => item.created_at.split(' ')[0]),
        datasets: [{
            label: 'Tổng điểm',
            data: chartData.map(item => item.total_score),
            borderColor: '#4e73df', // Màu xanh đặc trưng
            borderWidth: 3,
            backgroundColor: gradient, // Dùng gradient đã tạo ở trên
            fill: true,
            tension: 0.4, // Tạo đường cong mềm mại
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#4e73df',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false } // Ẩn legend để giống IOT (tối giản)
        },
        scales: {
            x: {
                grid: { display: false }, // Ẩn lưới dọc
                ticks: { color: '#999', font: { size: 12 } }
            },
            y: {
                beginAtZero: true,
                max: 990,
                grid: { color: '#f0f0f0' }, // Lưới ngang nhạt
                ticks: { stepSize: 200, color: '#999' }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        }
    }
});

$(document).ready(function() {
    const ctx = document.getElementById('scoreChart').getContext('2d');

    // 1. Tạo Gradient Fill (Màu xanh IOT nhạt dần xuống dưới)
    const chartGradient = ctx.createLinearGradient(0, 0, 0, 300);
    chartGradient.addColorStop(0, 'rgba(78, 115, 223, 0.3)'); // Màu xanh nhạt phía trên
    chartGradient.addColorStop(1, 'rgba(255, 255, 255, 0)');  // Trong suốt phía dưới

    // Giả lập dữ liệu hoặc lấy từ history của Nhân
    const history = JSON.parse(localStorage.getItem('toeic_history')) || [];
    const chartLabels = history.map(item => item.created_at.split(' ')[0]).reverse();
    const chartScores = history.map(item => item.total_score).reverse();

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Tổng điểm',
                data: chartScores,
                // --- Style giống IOT ---
                borderColor: '#4e73df',       // Màu xanh Navy chuẩn
                borderWidth: 3,               // Đường kẻ dày hơn
                backgroundColor: chartGradient, // Đổ màu vùng bên dưới
                fill: true,                   // Bật chế độ đổ màu
                tension: 0.4,                 // Bo cong đường (không bị gãy khúc)
                pointRadius: 4,               // Điểm nút nhỏ lại
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#4e73df'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Để nó tự fit vào thẻ cha
            plugins: {
                legend: { display: false } // Ẩn chú thích (IOT thường ẩn để cho thoáng)
            },
            scales: {
                x: {
                    grid: { display: false }, // Ẩn lưới dọc cho giống IOT
                    ticks: { color: '#858796', font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    max: 990,
                    grid: { 
                        color: '#f1f1f1',
                        drawBorder: false // Ẩn đường viền trục
                    },
                    ticks: { 
                        stepSize: 200, 
                        color: '#858796' 
                    }
                }
            },
            // Hiệu ứng khi rê chuột vào (Tooltip)
            interaction: {
                intersect: false,
                mode: 'index',
            }
        }
    });
});