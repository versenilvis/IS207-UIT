//File này dùng để vẽ các chart cho các số điểm tương ứng
//Data trong dashboard-chart.php

async function drawCharts() {
    const test_data  = await fetch('../../api/dashboard');
    if (!test_data.ok) {
          throw new Error("Lỗi kết nối database. Hãy tải lại trang để thử lại.");
    }
    const data = await test_data.json();

    const chartDatasets = {
        'Tổng điểm': {
            label: 'Tổng điểm',
            data: data.total,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
        },
        'Listening': {
            label: 'Listening',
            data: data.listening,
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34,197,94,0.1)',
        },
        'Reading': {
            label: 'Reading',
            data: data.reading,
            borderColor: '#f97316',
            backgroundColor: 'rgba(249,115,22,0.1)',
        },
        'Tất cả': {
            datasets: [
                { label: 'Tổng điểm', data: data.total,     borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)' },
                { label: 'Listening',  data: data.listening, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)' },
                { label: 'Reading',    data: data.reading,   borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)' },
            ]
        }
    };

    const ctx = document.getElementById('scoreChart');
    const scoreChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [chartDatasets['Tổng điểm']]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: false } }
        }
    });

    //Cho ng dùng chọn line chart để hiển thị
    $('.chart-tab').on('click', function () {
        $('.chart-tab').removeClass('active');
        $(this).addClass('active');

        const selected = chartDatasets[$(this).text().trim()];
        scoreChart.data.datasets = selected.datasets ?? [selected];
        scoreChart.update();
    });
}

drawCharts();

