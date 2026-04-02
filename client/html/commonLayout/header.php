<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Document</title>
  <link rel="stylesheet" href="commonLayoutCSS.css">
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
      <a class="navbar-brand fw-bold" href="#">
        <i class="fas fa-graduation-cap me-2"></i>PREPHUB
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Trang chủ</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Danh sách đề thi</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Lịch sử</a></li>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light ms-lg-3 px-4" href="/client/html/registerLogin.php">
              Đăng nhập <i class="fas fa-sign-in-alt ms-2"></i>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero-section text-center">
    <div class="container">
      <h1 class="display-4 fw-bold">Luyện Thi TOEIC Online</h1>
      <p class="lead">Hệ thống đề thi sát với thực tế, cập nhật mới nhất năm 2026.</p>
    </div>
  </header>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>