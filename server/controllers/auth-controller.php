<?php
// xử lí logic api cho user: login, logout, register, ...
session_start();
require_once '../config/database.php';

//Xử lý register
if (isset($_POST['register'])){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $uuid = uniqid();
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    //Kiểm tra xem email có tồn tại hay chưa
    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0){
        $_SESSION['register_error'] = 'Email is already registered! ';
        $_SESSION['active_form'] = 'register';
    }else{
        $insert = $conn->query("INSERT INTO users (uuid, first_name, last_name, email, password) VALUES ('$uuid', '$first_name', '$last_name', '$email', '$password')");
        if (!$insert){
            die($conn->error);
        }
    }
    header("Location: ../../client/page/login.php");
    exit();
}

//Xử lý login
if (isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0){
        $user = $result->fetch_assoc();
        //Nếu nhập đúng mật khẩu thì chuyển tới trang user.php
        if (password_verify($password, $user['password'])){
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            header("Location: ../../client/page/user.php");
            exit();
        }
    }
    //Nếu nhập sai mật khẩu thì chuyển tới home.php
    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: ../../client/page/login.php");
    exit();
}

?>