<!doctype html>
<html lang="vi">

<head>
  <?php include './components/metadata.php'; ?>
  <title>PREHUB - Luyện Thi TOEIC</title>
  <link rel="stylesheet" href="../styles/style.css">
</head>
<style>
  .nav-link.btn.btn-outline-light.ms-lg-3.px-4:hover:hover {
    background-color: #14b8a6;
    color: white;
  }
</style>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand footer-logo fw-bold" href="./home.php">
        <i class="bx bx-education" style="font-size: 31px;"></i>
        <span>PREPHUB</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a id="nav-home" class="nav-link" href="home.php">Trang chủ</a></li>
          <li class="nav-item"><a id="nav-list" class="nav-link" href="home.php#book-list-section" onclick="handleNavClick(event)">Danh sách đề thi</a></li>
          <li class="nav-item"><a id="nav-premium" class="nav-link" href="premium.php">Premium</a></li>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light ms-lg-3 px-4" href="login.php">
              Đăng nhập <i class="fas fa-sign-in-alt ms-2"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- INCLUDE HEADER FILE -->
  <?php include './components/header.php'; ?>
  <main class="container mb-5">

    <section id="book-list-section">
      <h2 class="fw-bold mb-4">Banner quảng cáo</h2>
      <div class="row row-cols-1 row-cols-lg-3 g-4" id="book-container"></div>
    </section>

    
  </main>

  <!-- INCLUDE FOOTER FILE -->
  <?php include './components/footer.php'; ?>

  <script src="../js/data.js"></script>
  <script src="../js/main.js"></script>
</body>

</html>