<!doctype html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PREHUB - Luyện Thi TOEIC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../styles/style.css">
</head>

<body>

  <!-- INCLUDE NAVBAR FILE -->
  <?php include './components/navBar.php'; ?>
  <!-- INCLUDE HEADER FILE -->
  <?php include './components/header.php'; ?>
  <main class="container mb-5">

    <section id="book-list-section">
      <h2 class="fw-bold mb-4">Chọn bộ sách ôn tập</h2>
      <div class="row row-cols-1 row-cols-lg-3 g-4" id="book-container"></div>
    </section>

    <section id="test-list-section" style="display: none">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#" onclick="showBooks()">Sách</a></li>
          <li class="breadcrumb-item active" id="current-book-name">Tên sách</li>
        </ol>
      </nav>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Danh sách đề thi</h2>
        <button class="btn btn-sm btn-secondary" onclick="showBooks()">
          <i class="fas fa-arrow-left"></i> Quay lại
        </button>
      </div>

      <div class="row row-cols-1 row-cols-md-2 g-3" id="test-container"></div>
    </section>

  </main>

  <!-- INCLUDE FOOTER FILE -->
  <?php include './components/footer.php'; ?>

  <script src="../js/data.js"></script>
  <script src="../js/main.js"></script>
</body>

</html>