<?php
session_start();
// XỬ LÝ NHẬP SAI TÀI KHOẢN HOẶC MẬT KHẨU
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? '',
];
$activeForm = $_SESSION['active_form'] ?? 'login';
session_unset(); //Session vẫn còn hoạt động nhưng bỏ hết các biến
function showError($error)
{
    return !empty($error) ? "<p class = 'error-message'>$error</p>" : '';
}
function isActiveForm($formName, $activeForm)
{
    return $formName === $activeForm ? 'active' : '';
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/loginPageStyle.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<!--http://localhost/Web/PrepHub/IS207-UIT/client/html/login.php-->

<body>
    <div class="container">
        <div class="form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
            <form action="../../server/controllers/auth-controller.php" method="post">
                <h2>Login</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account ? <a href="#" onclick="showForm('register-form')">Sign Up</a></p>
            </form>
        </div>
        <div class="form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">
            <form action="../../server/controllers/auth-controller.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <div class="name-row">
                    <input type="name" name="first_name" placeholder="First name" required>
                    <input type="name" name="last_name" placeholder="Last name" required>
                </div>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>
    <script src="../js/auth.js"></script>
</body>

</html>