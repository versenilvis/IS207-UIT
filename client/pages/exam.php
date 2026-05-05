<!DOCTYPE html>
<html lang="vi">

<head>
    <?php include './components/metadata.php'; ?>
    <title>Làm bài thi TOEIC | PREPHUB</title>
    <link rel="stylesheet" href="../styles/examStyle.css" />
</head>

<body>

    <header class="top-header text-center shadow-sm">
        <span class="fw-bold fs-5 me-3" id="exam-title"></span>
        <a href="user.php" class="btn btn-outline-secondary btn-sm" onclick="clearExamData()">Thoát</a>    
    </header>

    <div class="container-fluid py-4 px-lg-5">
        <div class="row g-4">

            <div class="col-lg-9">
                <div class="main-content shadow-sm">

                    <div class="sticky-audio shadow-sm mb-4">
                        <div class="d-flex align-items-center w-100 flex-wrap">
                            <button id="custom-play-btn" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                <i class="fas fa-play me-2"></i> Start audio
                            </button>

                            <span id="audio-status" class="ms-3 text-muted fw-bold"></span>

                            <audio id="exam-audio" controlsList="nodownload" style="display: none;"></audio>

                            <div class="w-100 mt-3 px-2" id="progress-container" style="display: none;">
                                <div class="d-flex justify-content-between text-muted small mb-1 fw-bold">
                                    <span id="current-time">00:00</span>
                                    <span id="total-time">00:00</span>
                                </div>
                                <div class="progress" style="height: 12px; pointer-events: none; background-color: #e9ecef;">
                                    <div id="audio-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <!--Nav link để hiển thị các part trong đề thi-->
                        <ul class="nav part-tabs mb-4 border-bottom pb-3" id="part-tabs-container"></ul>
                        <!--Đây là chỗ gọi api từ db lên để hiện các câu hỏi trong đề thi.-->
                        <div id="question-list-container"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="sidebar shadow-sm">
                    <div class="text-center mb-4">
                        <p class="mb-1 text-muted">Thời gian còn lại:</p>
                        <h3 class="fw-bold mb-3" id="timer-display">--:--</h3>
                        
                        <!--  Thêm data-bs-toggle để gọi Modal xác nhận -->
                        <button class="btn btn-primary fw-bold w-100 py-2 submit-btn" data-bs-toggle="modal" data-bs-target="#confirmSubmitModal">
                            SUBMIT
                        </button>
                    </div>

                    <hr>
                    <div id="sidebar-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TÍNH NĂNG MỚI: Thêm khối Modal của Bootstrap để xác nhận nộp bài -->
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary" id="confirmSubmitModalLabel">Xác nhận nộp bài</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn nộp bài ngay bây giờ? <br>
                    <small class="text-danger">Lưu ý: Bạn không thể thay đổi đáp án sau khi đã nộp.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"> Quay lại</button>
                    <!-- Gắn sự kiện onclick gọi hàm submitExam() trong exam.js -->
                    <button type="button" class="btn btn-primary fw-bold" onclick="submitExam()">Đồng ý </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/exam.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>