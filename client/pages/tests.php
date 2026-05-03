<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Danh sách đề thi | PREPHUB</title>
  <link rel="stylesheet" href="../styles/testPage.css">
  <?php include './components/metadata.php'; ?>
</head>

<body>

  <!-- INCLUDE NAVBAR FILE -->
  <?php include './components/navBar.php'; ?>

  <div class="hero">
    <h1>Danh sách đề thi</h1>
    <p>Luyện thi TOEIC với bộ đề đa dạng — từ mini test đến full test chuẩn quốc tế</p>
  </div>

  <div class="content">
    <div class="toolbar">
      <div class="filter-tabs">
        <button class="filter-tab active">Tất cả</button>
        <button class="filter-tab">Listening</button>
        <button class="filter-tab">Reading</button>
        <button class="filter-tab">Grammar</button>
        <button class="filter-tab">Vocabulary</button>
        <button class="filter-tab">Full Test</button>
        <button class="filter-tab">Mini Test</button>
      </div>
      <div class="right-tools">
        <input class="search-input" placeholder="🔍  Tìm kiếm đề thi..." />
        <select class="sort-select">
          <option>Mới nhất</option>
          <option>Phổ biến nhất</option>
          <option>Điểm TB cao nhất</option>
        </select>
      </div>
    </div>

    <div class="section-label">Miễn phí</div>
    <div class="grid">

      <div class="card">
        <div class="card-top">
          <div class="card-title">TOEIC Grammar Mini Test</div><span class="badge badge-free">Miễn phí</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>30 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>50 câu</span>
        </div>
        <div class="card-desc">Bài kiểm tra ngữ pháp tập trung vào các điểm thường gặp trong đề thi TOEIC.</div>
        <div class="card-tags"><span class="tag">Grammar</span><span class="tag">Mini Test</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">4,821 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 72đ</span></div>
          </div>
          <button class="btn-start">Làm bài</button>
        </div>
      </div>

      <div class="card">
        <div class="card-top">
          <div class="card-title">TOEIC Listening Practice Test 1</div><span class="badge badge-free">Miễn phí</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>120 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>200 câu</span>
        </div>
        <div class="card-desc">Bài thi nghe với đa dạng dạng câu hỏi giúp cải thiện kỹ năng nghe hiểu hiệu quả.</div>
        <div class="card-tags"><span class="tag">Listening</span><span class="tag">Practice Test</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">11,340 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 410đ</span></div>
          </div>
          <div class="done-label"><svg width="14" height="14" viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" fill="#3a7c28" opacity="0.15" />
              <path d="M5 8l2.5 2.5L11 5.5" stroke="#3a7c28" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>Đã hoàn thành</div>
        </div>
      </div>

      <div class="card">
        <div class="card-top">
          <div class="card-title">TOEIC Reading Practice Test 1</div><span class="badge badge-free">Miễn phí</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>120 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>200 câu</span>
        </div>
        <div class="card-desc">Bài thi đọc mức độ trung bình, phù hợp để ôn luyện và tự đánh giá kỹ năng đọc hiểu.</div>
        <div class="card-tags"><span class="tag">Reading</span><span class="tag">Practice Test</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">8,670 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 385đ</span></div>
          </div>
          <button class="btn-start">Làm bài</button>
        </div>
      </div>

      <div class="card">
        <div class="card-top">
          <div class="card-title">TOEIC Vocabulary Mini Test</div><span class="badge badge-new">Mới</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>20 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>30 câu</span>
        </div>
        <div class="card-desc">Kiểm tra từ vựng theo chủ đề công sở, du lịch và giao tiếp thường ngày.</div>
        <div class="card-tags"><span class="tag">Vocabulary</span><span class="tag">Mini Test</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">1,203 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 68đ</span></div>
          </div>
          <button class="btn-start">Làm bài</button>
        </div>
      </div>
    </div>

    <div class="section-label" style="margin-top:2.5rem;">Premium</div>
    <div class="grid">

      <div class="card premium">
        <div class="card-top">
          <div class="card-title">TOEIC Full Test 2024 — Bộ 1</div><span class="badge badge-premium">✦ Premium</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>120 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>200 câu</span>
        </div>
        <div class="card-desc">Đề thi mô phỏng chuẩn TOEIC 2024 kèm giải thích chi tiết.</div>
        <div class="card-tags"><span class="tag">Full Test</span><span class="tag">2024</span><span class="tag">Giải thích</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">6,540 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 665đ</span></div>
          </div>
          <button class="btn-start gold">Làm bài ✦</button>
        </div>
      </div>

      <div class="card premium">
        <div class="card-top">
          <div class="card-title">TOEIC Full Test 2024 — Bộ 2</div><span class="badge badge-premium">✦ Premium</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>120 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>200 câu</span>
        </div>
        <div class="card-desc">Bộ đề số 2 chuẩn format mới, thiết kế bởi chuyên gia hàng đầu.</div>
        <div class="card-tags"><span class="tag">Full Test</span><span class="tag">2024</span><span class="tag">Chuyên gia</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">4,112 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 640đ</span></div>
          </div>
          <button class="btn-start gold">Làm bài ✦</button>
        </div>
      </div>

      <div class="card premium">
        <div class="card-top">
          <div class="card-title">Listening Advanced — Part 3 & 4</div><span class="badge badge-premium">✦ Premium</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>60 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>90 câu</span>
        </div>
        <div class="card-desc">Tập trung vào phần khó nhất của Listening — hội thoại và bài nói dài.</div>
        <div class="card-tags"><span class="tag">Listening</span><span class="tag">Part 3 & 4</span><span class="tag">Nâng cao</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">2,980 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 58đ</span></div>
          </div>
          <button class="btn-start gold">Làm bài ✦</button>
        </div>
      </div>

      <div class="card premium">
        <div class="card-top">
          <div class="card-title">Reading — Double & Triple Passages</div><span class="badge badge-premium">✦ Premium</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>75 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>100 câu</span>
        </div>
        <div class="card-desc">Luyện tập Part 7 chuyên sâu với bài đọc đôi và bộ ba.</div>
        <div class="card-tags"><span class="tag">Reading</span><span class="tag">Part 7</span><span class="tag">Phân tích</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">3,455 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 310đ</span></div>
          </div>
          <button class="btn-start gold">Làm bài ✦</button>
        </div>
      </div>

      <div class="card premium">
        <div class="card-top">
          <div class="card-title">TOEIC Simulation — ETS Style</div><span class="badge badge-premium">✦ Premium</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>120 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>200 câu</span>
        </div>
        <div class="card-desc">Mô phỏng thi thật chuẩn ETS, phân tích điểm yếu bằng AI.</div>
        <div class="card-tags"><span class="tag">Full Test</span><span class="tag">ETS Style</span><span class="tag">AI phân tích</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">7,890 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 720đ</span></div>
          </div>
          <button class="btn-start gold">Làm bài ✦</button>
        </div>
      </div>

      <div class="card premium">
        <div class="card-top">
          <div class="card-title">Grammar Intensive — Part 5 & 6</div><span class="badge badge-premium">✦ Premium</span>
        </div>
        <div class="card-meta">
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M8 5v3.5l2 1.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>45 phút</span>
          <span class="meta-item"><svg viewBox="0 0 16 16" fill="none">
              <rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>70 câu</span>
        </div>
        <div class="card-desc">Bài tập ngữ pháp chuyên sâu Part 5 và 6 với giải thích chi tiết.</div>
        <div class="card-tags"><span class="tag">Grammar</span><span class="tag">Part 5 & 6</span><span class="tag">Chi tiết</span></div>
        <div class="card-footer">
          <div class="card-stats">
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2a4 4 0 100 8A4 4 0 008 2zM2 14c0-2.21 2.686-4 6-4s6 1.79 6 4" stroke="#888" stroke-width="1.2" stroke-linecap="round" />
              </svg><span class="stat-count">5,217 lượt</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><svg viewBox="0 0 16 16" fill="none">
                <path d="M8 2l1.5 3.5H13l-2.9 2.1 1.1 3.4L8 9l-3.2 2 1.1-3.4L3 5.5h3.5L8 2z" stroke="#1a6e3c" stroke-width="1.1" stroke-linejoin="round" />
              </svg><span class="stat-score">TB 63đ</span></div>
          </div>
          <button class="btn-start gold">Làm bài ✦</button>
        </div>
      </div>

    </div>
    <div style="height:2.5rem;"></div>
  </div>
  </div>

  <script>
    document.querySelectorAll('.filter-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
      });
    });
  </script>

</body>

</html>