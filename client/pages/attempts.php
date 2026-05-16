<!DOCTYPE html>
<html lang="vi">

<head>
    <?php include './components/metadata.php'; ?>
    <title>Lịch sử làm bài - TOEIC Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/attempts.css">
</head>

<body>

    <?php include './components/navBar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 page-header">
            <div>
                <h2 class="fw-bold fs-3 mb-1">Lịch sử làm bài chi tiết</h2>
                <p class="text-muted mb-0">Danh sách tất cả các bộ đề bạn đã hoàn thành</p>
            </div>
        </div>

        <div class="filter-card bg-white p-3 mb-4">
            <div class="row g-3">
                <div class="col-md-7 search-input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm bộ đề...">
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="far fa-calendar-alt"></i></span>
                        <select id="timeFilter" class="form-select border-start-0 ps-0">
                            <option value="all">Tất cả thời gian</option>
                            <option value="this_month">Tháng này</option>
                            <option value="7_days">7 ngày qua</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button id="filterBtn" class="btn btn-filter w-100"><i class="fas fa-sort-amount-down me-2"></i>Sắp xếp điểm</button>
                </div>
            </div>
        </div>
        <div class="table-card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="text-center py-3 fw-medium text-muted" style="width: 15%;">Ngày thi</th>
                            <th class="text-center py-3 fw-medium text-muted" style="width: 25%;">Tên đề thi</th>
                            <th class="text-center py-3 fw-medium text-muted" style="width: 12%;">Listening</th>
                            <th class="text-center py-3 fw-medium text-muted" style="width: 12%;">Reading</th>
                            <th class="text-center py-3 fw-medium text-muted" style="width: 12%;">Tổng điểm</th>
                            <th class="text-center py-3 fw-medium text-muted" style="width: 10%;">Thời gian</th>
                            <th class="text-center pe-4 py-3 fw-medium text-muted text-nowrap" style="width: 14%;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                    </tbody>
                </table>
            </div>

            <div class="bg-white py-3 border-top d-flex justify-content-center">
                <nav>
                    <ul class="pagination mb-0 gap-1" id="paginationContainer">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <?php include './components/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/attempts.js"></script>
</body>

</html>