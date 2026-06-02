<?php
/**
 * Admin dashboard page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// kiểm tra quyền admin phía PHP
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: home.php");
    exit();
}

$section = $_GET['section'] ?? 'overview';
$action = $_GET['action'] ?? '';
$test_id = $_GET['test_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrepHub Admin Dashboard</title>
    <?php include './components/metadata.php'; ?>
    <link href="../styles/adminStyle.css?v=<?= time() ?>" rel="stylesheet">
    <?php if ($section === 'tests' && ($action === 'create' || $action === 'edit')): ?>
        <link href="../styles/questionsStyle.css" rel="stylesheet">
    <?php endif; ?>
    <!-- tải thư viện chart.js cdn vẽ biểu đồ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
</head>
<body>

    <!-- thanh sidebar điều hướng -->
    <?php include './components/admin/sidebar.php'; ?>

    <!-- vùng hiển thị nội dung chính -->
    <main class="main-content">
        <!-- nạp các phân hệ thành phần -->
        <?php include './components/admin/overview.php'; ?>
        <?php include './components/admin/tests.php'; ?>
        <?php include './components/admin/users.php'; ?>
        <?php include './components/admin/attempts.php'; ?>
        <?php include './components/admin/revenue.php'; ?>
    </main>

    <!-- điều khiển javascript -->
    <?php if ($section === 'tests' && ($action === 'create' || $action === 'edit')): ?>
        <!-- tải các file script phục vụ form câu hỏi -->
        <script src="../js/questions/state.js"></script>
        <script src="../js/questions/ui.js"></script>
        <script src="../js/questions/api.js"></script>
        <script src="../js/questions/form-fill.js"></script>
        <script src="../js/questions/dom-builder.js"></script>
        <script src="../js/questions/validation.js"></script>
        <script src="../js/questions/utils.js"></script>
        <script src="../js/questions/main.js"></script>
    <?php endif; ?>

    <script>
        window.adminUserId = <?= (int)$_SESSION['user_id'] ?>
    </script>

    <!-- tải các file điều khiển mô-đun chính dashboard -->
    <script src="../js/admin/main.js?v=<?= time() ?>"></script>
    <script src="../js/admin/overview.js?v=<?= time() ?>"></script>
    <script src="../js/admin/tests.js?v=<?= time() ?>"></script>
    <script src="../js/admin/users.js?v=<?= time() ?>"></script>
    <script src="../js/admin/attempts.js?v=<?= time() ?>"></script>
    <script src="../js/admin/revenue.js?v=<?= time() ?>"></script>

</body>
</html>
