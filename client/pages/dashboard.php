<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOEIC Dashboard </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">Kết quả luyện tập cá nhân</h2>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Điểm cao nhất</h5>
                    <h2 id="max-score">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Số bài đã làm</h5>
                    <h2 id="total-tests">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Thời gian trung bình</h5>
                    <h2 id="avg-time">0m</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>Biểu đồ tiến độ điểm số</strong></div>
                <div class="card-body">
                    <canvas id="scoreChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Lịch sử làm bài gần đây</strong></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ngày thi</th>
                        <th>Đề thi</th>
                        <th>Điểm Listening</th>
                        <th>Điểm Reading</th>
                        <th>Tổng điểm</th>
                        <th>Thời gian</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="history-body">
                    </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card-header bg-white d-flex justify-content-between align-items-center">
    <strong>Lịch sử làm bài gần đây</strong>
    <a href="attempts.php" class="btn btn-sm btn-link text-decoration-none">Xem tất cả >></a>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="..\js\data_sample.js"></script>
</body>
</html>