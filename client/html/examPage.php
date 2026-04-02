<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Làm bài: ETS 2024 - PREHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="css/examStyle.css" />
</head>
<body>

    <header class="top-header text-center shadow-sm">
        <span class="fw-bold fs-5 me-3" id="exam-title">ETS 2019 Test 1</span>
        <a href="index.html" class="btn btn-outline-secondary btn-sm">Thoát</a>
    </header>

    <div class="container-fluid py-4 px-lg-5">
        <div class="row g-4">
            
            <div class="col-lg-9">
                <div class="main-content shadow-sm">
                    
                    <div class="sticky-audio">
                        <div class="d-flex align-items-center">
                            <span class="badge rounded-pill bg-primary me-3 px-3 py-2"><i class="fas fa-play me-1"></i> Audio</span>
                            <audio id="toeic-audio" controls>
                                <source src="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3" type="audio/mpeg">
                            </audio>
                        </div>
                    </div>

                    <div class="p-4">
                        <ul class="nav part-tabs mb-4 border-bottom pb-3">
                            <li class="nav-item"><a class="nav-link" href="#">Part 1</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Part 2</a></li>
                            <li class="nav-item"><a class="nav-link active" href="#">Part 3</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Part 4</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Part 5</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Part 6</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Part 7</a></li>
                        </ul>

                        <div id="question-list-container"></div>
                    </div>
                    
                </div>
            </div>

            <div class="col-lg-3">
                <div class="sidebar shadow-sm">
                    
                    <div class="text-center mb-4">
                        <p class="mb-1 text-muted">Thời gian còn lại:</p>
                        <h3 class="fw-bold mb-3" id="timer-display">120:00</h3>
                        <button class="btn btn-outline-primary fw-bold w-100 py-2">NỘP BÀI</button>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 1</p>
                        <div class="question-grid" id="sidebar-part-1"></div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 2</p>
                        <div class="question-grid" id="sidebar-part-2"></div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 3</p>
                        <div class="question-grid" id="sidebar-part-3"></div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 4</p>
                        <div class="question-grid" id="sidebar-part-4"></div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 5</p>
                        <div class="question-grid" id="sidebar-part-5"></div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 6</p>
                        <div class="question-grid" id="sidebar-part-6"></div>
                    </div>
                    <div class="mb-3">
                        <p class="fw-bold mb-2">Part 7</p>
                        <div class="question-grid" id="sidebar-part-7"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="/client/js/data.js"></script>
    <script src="/client/js/exam.js"></script>
</body>
</html>