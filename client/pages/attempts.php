<!DOCTYPE html>
<html lang="vi">
<head>
   <?php include './components/metadata.php'; ?>
    <title>Lịch sử làm bài - TOEIC Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Lịch sử làm bài chi tiết</h2>
            <p class="text-muted">Danh sách tất cả các bộ đề bạn đã hoàn thành</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-primary">
            <i class="fa-solid fa-house me-1"></i> Về Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Tìm kiếm bộ đề...">
                </div>
                <div class="col-md-4">
                    <select class="form-select">
                        <option>Tất cả thời gian</option>
                        <option>7 ngày qua</option>
                        <option>Tháng này</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-dark w-100 text-white">Lọc kết quả</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Ngày thi</th>
                            <th>Tên đề thi</th>
                            <th class="text-center">Listening</th>
                            <th class="text-center">Reading</th>
                            <th class="text-center">Tổng điểm</th>
                            <th class="text-center">Thời gian</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                        </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item disabled"><span class="page-link">Trước</span></li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item"><span class="page-link">Sau</span></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="..\js\data_sample.js"></script>
<script src="..\js\scoring.js"></script>
</body>
</html> 