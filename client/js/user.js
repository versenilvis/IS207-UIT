async function drawCharts() {
    const canvas = document.getElementById('scoreChart');
    if (!canvas) return;

    if (typeof Chart === 'undefined') {
        showChartMessage(canvas, 'Khong the tai thu vien bieu do. Hay tai lai trang.');
        return;
    }

    try {
        const response = await fetch('/api/dashboard');
        if (!response.ok) {
            throw new Error('Khong the tai du lieu bieu do.');
        }

        const data = await response.json();
        const labels = Array.isArray(data.labels) ? data.labels : [];

        if (labels.length === 0) {
            showChartMessage(canvas, 'Chua co du lieu diem so de ve bieu do.');
            return;
        }

        new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Tong diem',
                        data: data.total || [],
                        borderColor: '#1d9e75',
                        backgroundColor: 'rgba(29, 158, 117, 0.04)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true,
                        pointBackgroundColor: '#1d9e75',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Listening',
                        data: data.listening || [],
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.04)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Reading',
                        data: data.reading || [],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.04)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#05102b',
                        padding: 12,
                        titleFont: { size: 12, weight: '700' },
                        bodyFont: { size: 12, weight: '600' },
                    },
                    legend: {
                        position: 'top',
                        align: 'center',
                        labels: {
                            boxWidth: 5,
                            boxHeight: 5,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            color: '#475569',
                            font: {
                                size: 12,
                                weight: '600',
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '600' },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        max: 990,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: '#94a3b8',
                            stepSize: 165,
                            font: { size: 11, weight: '600' },
                        },
                    },
                },
            },
        });
    } catch (error) {
        showChartMessage(canvas, error.message || 'Khong the ve bieu do.');
    }
}

function showChartMessage(canvas, message) {
    const wrapper = canvas.parentElement;
    canvas.remove();

    const empty = document.createElement('div');
    empty.className = 'chart-empty';
    empty.textContent = message;
    wrapper.appendChild(empty);
}

document.addEventListener('DOMContentLoaded', drawCharts);
