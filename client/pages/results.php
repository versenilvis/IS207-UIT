<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include './components/metadata.php'; ?>
    <title>Kết quả bài thi: ETS 2024 - PREHUB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/results.css">
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm p-3 h-100">
                <small class="text-muted fw-bold">TỔNG ĐIỂM</small>
                <h2 class="text-primary fw-bold mb-0" id="total-points">0</h2>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm p-3 h-100 border-start border-success border-4">
                <small class="text-muted fw-bold">LISTENING</small>
                <h3 class="text-success fw-bold mb-0" id="listening-points">0/495</h3>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm p-3 h-100 border-start border-info border-4">
                <small class="text-muted fw-bold">READING</small>
                <h3 class="text-info fw-bold mb-0" id="reading-points">0/495</h3>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card text-center border-0 shadow-sm p-3 h-100">
                <small class="text-muted fw-bold">ĐỘ CHÍNH XÁC</small>
                <h3 class="fw-bold mb-0" id="accuracy-rate">0%</h3>
            </div>
        </div>
    </div>

    <div class="row d-flex align-items-stretch">
        
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm border-0 left-review-card h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">Review chi tiết từng câu</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" id="filter-all" data-filter="all">Tất cả</button>
                        <button type="button" class="btn btn-outline-danger" id="filter-wrong" data-filter="wrong">Câu sai</button>
                    </div>
                </div>
                
                <div id="wrong-questions-list">
                    <div class="p-5 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Đang tải dữ liệu bài làm...
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="sticky-sidebar">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold mb-3 d-flex justify-content-between">
                            BẢNG ĐÁP ÁN
                            <small class="text-muted">1 - 200</small>
                        </h6>
                        
                        <div id="answer-grid" class="flex-grow-1"></div>
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-around small mb-3">
                            <span><i class="fas fa-square text-success me-1"></i> Đúng</span>
                            <span><i class="fas fa-square text-danger me-1"></i> Sai</span>
                            <span><i class="fas fa-square text-light border me-1"></i> Trống</span>
                        </div>
                        
                        <div class="row g-2 mt-auto">
                            <div class="col-6">
                                <a href="test.php" class="btn btn-outline-primary btn-sm w-100">Làm lại bài</a>
                            </div>
                            <div class="col-6">
                                <a href="dashboard.php" class="btn btn-primary btn-sm w-100">Về Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/results.js"></script>

</body>
</html>