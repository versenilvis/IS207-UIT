<<<<<<< HEAD
=======
<?php
if (!function_exists('showError')) {
    function showError($error) {
        return !empty($error) ? "<p class='error-message' style='color: #ef4444; font-size: 0.85rem; margin-bottom: 12px; font-weight: 500;'>$error</p>" : '';
    }
}
if (!function_exists('showSuccess')) {
    function showSuccess($msg) {
        return !empty($msg) ? "<p class='success-message' style='color: #10b981; font-size: 0.85rem; margin-bottom: 12px; font-weight: 500;'>$msg</p>" : '';
    }
}
$errors = $errors ?? [];
?>
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
<!-- metadata.php có công dụng là thay vì import các thẻ  meta, link rel, ... lặp lại ở nhiều phần html của các file
	thì giờ mình chỉ cần import đúng cái file này thôi, code sẽ gọn hơn rất nhiều
	nói chung là cái này có tác dụng như import đầu file html vậy
 -->

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<<<<<<< HEAD
<!-- bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
=======
<!-- favicon -->
<link rel="icon" type="image/svg+xml" href="../img/logo.svg">
<link rel="shortcut icon" href="../img/logo.svg">

<!-- bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8

<!-- font awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- boxicons -->
<<<<<<< HEAD
=======
<link rel="preload" href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.woff2" as="font" type="font/woff2" crossorigin>

>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
<link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
<link href="https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css" rel="stylesheet">
<link href="https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css" rel="stylesheet">

<!-- font family -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../styles/homepage/main.css">
<<<<<<< HEAD

<!-- chart js cho dashboard -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
=======
<link rel="stylesheet" href="../styles/profile-dropdown.css">
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
