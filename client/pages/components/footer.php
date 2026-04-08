<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Document</title>
    <link rel="stylesheet" href="./componants/componantsStyle.css">
</head><!-- CSS để ở đây để ghi đè bootstrap -->
<style>
    .info-link {
        color: black !important;
        text-decoration: none;
    }

    .info-link:hover {
        color: blue !important;
    }

    .footer-logo i {
        font-size: 20px !important;
    }

    .social-link {
        font-size: 25px;
        color: #4c7ce3;
        transition: 0.3s;
    }

    .social-link:hover {
        color: #1842b4;
    }
</style>

<body>
    <div class="container">
        <footer class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-3 my-3 border-top">
            <div class="col mb-3"> <a href="/" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none" aria-label="Bootstrap"> <svg class="bi me-2" width="40" height="32" aria-hidden="true">
                        <use xlink:href="#bootstrap"></use>
                    </svg> </a>
                <a class="navbar-brand fw-bold footer-logo" href="#">
                    <i class="fas fa-graduation-cap me-2"> PREPHUB</i>
                </a>
                <div class="site-socials">
                    <a target="_blank" href="#"><span class="social-link fab fa-facebook-square"></span></a>
                    <a target="_blank" href="#"><span class="social-link fab fa-instagram"></span></a>
                    <a target="_blank" href="#"><span class="social-link fab fa-twitter"></span></a>
                    <a target="_blank" href="#"><span class="social-link fab fa-youtube"></span></a>
                    <a target="_blank" href="#"><span class="social-link fab fa-tiktok"></span></a>
                </div>
            </div>
            <div class="col mb-3"></div>
            <div class="col mb-3">
                <h5>Về PREPHUB</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="#" class="info-link">Giới thiệu</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Liên hệ</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Điều khoảng bảo mật</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Điều khoảng sử dụng</a></li>
                </ul>
            </div>
            <div class="col mb-3">
                <h5>Tài nguyên</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="#" class="info-link">Thư viện đề thi</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Blog</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Tổng hợp tài liệu</a></li>
                </ul>
            </div>
            <div class="col mb-3">
                <h5>Chính sách chung</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="#" class="info-link">Hướng dẫn sử dụng</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Hướng dẫn thanh toán</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Điều khoảng và điều kiện giao dịch</a></li>
                    <li class="nav-item mb-2"><a href="#" class="info-link">Phản hồi, khiếu nại</a></li>
                </ul>
            </div>
        </footer>
        <div class="company-info">
            <h5><B>Thông tin doanh nghiệp</B></h5>
            <ul>
                <li>Điện thoại liên hệ/Hotline: 123 456 7890</li>
                <li>Email: prephub.team@gmail.com</li>
                <li>Địa chỉ trụ sở: Trường đại học Công Nghệ Thông Tin - UIT</li>
                <li>CS1: TRUNG TÂM NGOẠI NGỮ PREPHUB - TPHCM</li>
            </ul>
        </div>
    </div>
</body>

</html>