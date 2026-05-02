<!DOCTYPE html>
<html lang="vi">

<head>
    <?php include './components/metadata.php'; ?>
    <title></title>
    <link rel="stylesheet" href="../styles/examStyle.css" />
</head>

<body>

    <header class="top-header text-center shadow-sm">
        <span class="fw-bold fs-5 me-3" id="exam-title"></span>
        <a href="user.php" class="btn btn-outline-secondary btn-sm">Thoát</a>
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
                        <h3 class="fw-bold mb-3" id="timer-display"></h3>
                        <button class="btn btn-primary fw-bold w-100 py-2 submit-btn">SUBMIT</button>
                    </div>

                    <hr>
                    <div id="sidebar-container"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/exam.js"></script>
    <script src="../js/data.js"></script>
</body>

</html>
