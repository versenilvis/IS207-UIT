// client/js/dashboard.js

// Lấy ID người dùng (tạm thời hardcode là 1 giống bài trước, hoặc bạn có thể truyền qua session/URL)
const USER_ID = 2; 
const API_URL = `/api/dashboard/stats?user_id=${USER_ID}`;

$(document).ready(async function() {
    try {  
        // 1. Gọi API lấy dữ liệu tổng hợp
        const response = await fetch(API_URL);
        const json = await response.json();

        if (json.status === 'success' && json.data) {
            // 2. Cập nhật các con số tổng quan
            updateOverview(json.data.overview);
            
            // 3. Vẽ biểu đồ tiến độ
            if (json.data.chartData && json.data.chartData.length > 0) {
                renderChart(json.data.chartData);
            }

            // 4. Đổ dữ liệu vào bảng lịch sử
            if (json.data.history && json.data.history.length > 0) {
                renderHistoryTable(json.data.history);
            } else {
                $('#history-body').html('<tr><td colspan="7" class="text-center text-muted py-4">Bạn chưa có lịch sử làm bài nào.</td></tr>');
            }
        } else {
            console.error("Lỗi từ API:", json.message);
        }
    } catch (error) {
        console.error("Lỗi kết nối API:", error);
        $('#history-body').html('<tr><td colspan="7" class="text-center text-danger py-4">Không thể tải dữ liệu từ máy chủ.</td></tr>');
    }
});

// Hàm cập nhật 3 ô thống kê phía trên
function updateOverview(overviewData) {
    if (!overviewData) return;
    
    // Nếu điểm cao nhất chưa có, để số 0
    $('#max-score').text(overviewData.maxScore || '0');
    
    $('#total-tests').text(overviewData.totalTests || '0');
    
    // Xử lý thời gian trung bình (giả sử backend trả về số phút)
    const avgTime = overviewData.avgTimeMinutes ? Math.round(overviewData.avgTimeMinutes) + 'm' : '0m';
    $('#avg-time').text(avgTime);
}

// Hàm vẽ biểu đồ đường bằng Chart.js
function renderChart(chartData) {
    const ctx = document.getElementById('scoreChart').getContext('2d');
    
    // Tách dữ liệu từ mảng backend trả về
    const labels = chartData.map(item => item.date); // Trục X: Ngày tháng
    const scores = chartData.map(item => item.total_score); // Trục Y: Tổng điểm
    const listeningScores = chartData.map(item => item.listening_score); // Trục Y phụ
    const readingScores = chartData.map(item => item.reading_score); // Trục Y phụ

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tổng điểm',
                    data: scores,
                    borderColor: '#0d6efd', // Màu xanh Bootstrap
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    tension: 0.3, // Độ cong của đường
                    fill: true
                },
                {
                    label: 'Listening',
                    data: listeningScores,
                    borderColor: '#198754', // Màu xanh lá Bootstrap
                    borderWidth: 2,
                    borderDash: [5, 5], // Nét đứt
                    tension: 0.3,
                    hidden: true // Mặc định ẩn, người dùng có thể bấm vào label để hiện
                },
                {
                    label: 'Reading',
                    data: readingScores,
                    borderColor: '#0dcaf0', // Màu cyan Bootstrap
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.3,
                    hidden: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 990 // TOEIC max là 990
                }
            },
            plugins: {
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });
}

// Hàm đổ dữ liệu vào bảng
function renderHistoryTable(historyData) {
    let html = '';
    
    historyData.forEach(item => {
        // Format lại ngày tháng cho đẹp (VD: 15/04/2026)
        const dateObj = new Date(item.created_at);
        const formattedDate = dateObj.toLocaleDateString('vi-VN');
        
        // Tính tổng điểm nếu backend chưa tính
        const totalScore = (item.listening_score || 0) + (item.reading_score || 0);

        html += `
            <tr>
                <td class="align-middle">${formattedDate}</td>
                <td class="align-middle fw-bold">${item.test_name || 'Đề ngẫu nhiên'}</td>
                <td class="align-middle text-success">${item.listening_score || 0}</td>
                <td class="align-middle text-info">${item.reading_score || 0}</td>
                <td class="align-middle fw-bold text-primary">${totalScore}</td>
                <td class="align-middle">${item.time_taken || '0'} phút</td>
                <td class="align-middle">
                    <a href="results.php?attempt_id=${item.attempt_id}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Xem chi tiết
                    </a>
                </td>
            </tr>
        `;
    });
    
    $('#history-body').html(html);
}