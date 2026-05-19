async function drawCharts() {
    const response = await fetch('../../api/dashboard');
    if (!response.ok) {
        throw new Error('Lỗi kết nối database. Hãy tải lại trang để thử lại.');
    }

    const data = await response.json();
    const ctx = document.getElementById('scoreChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Tổng điểm',
                    data: data.total || [],
                    borderColor: '#1d9e75',
                    backgroundColor: 'rgba(29, 158, 117, 0.05)',
                    pointBackgroundColor: '#1d9e75',
                    pointBorderColor: '#1d9e75',
                    pointRadius: 3,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false,
                },
                {
                    label: 'Listening',
                    data: data.listening || [],
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.05)',
                    pointBackgroundColor: '#0284c7',
                    pointBorderColor: '#0284c7',
                    pointRadius: 2.5,
                    borderWidth: 1.8,
                    tension: 0.35,
                    fill: false,
                },
                {
                    label: 'Reading',
                    data: data.reading || [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.05)',
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#f59e0b',
                    pointRadius: 2.5,
                    borderWidth: 1.8,
                    tension: 0.35,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 990,
                    afterBuildTicks: (scale) => {
                        scale.ticks = [0, 100, 200, 300, 400, 500, 600, 700, 800, 900, 990]
                            .map((value) => ({ value }));
                    },
                    ticks: {
                        stepSize: 100,
                        color: '#6b7280',
                        font: {
                            size: 10,
                        },
                    },
                    grid: {
                        color: '#e5e7eb',
                    },
                    border: {
                        display: false,
                    },
                },
                x: {
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 10,
                        },
                    },
                    grid: {
                        color: '#e5e7eb',
                    },
                    border: {
                        display: false,
                    },
                },
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
                legend: {
                    position: 'top',
                    align: 'center',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 6,
                        boxHeight: 6,
                        padding: 14,
                        color: '#334155',
                        font: {
                            size: 10,
                            weight: '600',
                        },
                    },
                },
            },
        },
    });
}

drawCharts().catch((error) => console.error(error));
