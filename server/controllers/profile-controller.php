<?php
/**
 * @var PDO $conn
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../utils/response.php';

//Hiển thị điểm cao nhất
function getMaxScore(){
    global $conn;
    try{
        $sql = "SELECT MAX(total_score) 
                FROM attempts
                WHERE user_id = :id";

        $stmt = $conn->prepare($sql);
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':id'      => $id
        ]);
        return $stmt->fetchColumn();
    }catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
        return false;
    }
}
//Hiển thị số bài đã làm
function getNumTestDone(){
    global $conn;
    try{
        $sql = "SELECT COUNT(*) 
                FROM attempts
                WHERE user_id = :id";

        $stmt = $conn->prepare($sql);
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':id'      => $id
        ]);
        return $stmt->fetchColumn();
    }catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
        return false;
    }
}
//Hiển thị điểm số trung bình
function getAvgScore(){
    global $conn;
    try{
        $sql = "SELECT ROUND(AVG(total_score)) 
                FROM attempts
                WHERE user_id = :id";
        
        $stmt = $conn->prepare($sql);
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':id'      => $id
        ]);
        return $stmt->fetchColumn();
    }catch (PDOException $e) {
        sendError("Lỗi database: " . $e->getMessage(), 500);
        return false;
    }
}


//Thay đổi tên của người dùng
function changeUsername(){
    global $conn;
    try{
        //Dùng bind params để chống sqli
        $sql = "UPDATE users SET 
                first_name = :firstName, last_name= :lastName
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $firstname = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':firstName' => $firstname,
            ':lastName'  => $lastName,
            ':id'      => $id
        ]);
        $_SESSION['first_name'] = $firstname;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['changeNameResult'] = "Cập nhật tên thành công";
        header("Location: ../../client/pages/profile.php");
        exit();
    }catch (PDOException $e) {
        $_SESSION['changeNameResult'] = "Có lỗi! Hãy thử lại sau.";
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}
//Nếu bấm nút "Cập nhật tên" ở profile.php thì thay đổi tên
if (isset($_POST['changeName'])){
    changeUsername();
}


//Thay đổi mật khẩu của người dùng
function changePassword(){
    global $conn;
    try{
        //Mật khẩu mới
        $passHash = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
        $sql = "UPDATE users 
                SET password = :password
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':password' => $passHash,
            ':id' => $_SESSION['user_id']
        ]);        
        $_SESSION['changePassResult'] = "Cập nhật mật khẩu thành công";
        header("Location: ../../client/pages/profile.php");
        exit();
    }catch (PDOException $e) {
        $_SESSION['changePassResult'] = "Có lỗi! Hãy thử lại sau.";
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}
//Kiểm tra xem mật khẩu hiện tại có giống với mật khẩu trong db hay không
function checkPassword(string $password_need_to_be_checked){ //T hết biết đặt tên gì r
    global $conn;
    try{
        $sql = "SELECT password FROM users
                WHERE id = :id"; //chống sqli
        $stmt = $conn->prepare($sql);
        $id = $_SESSION['user_id'];
        $stmt->execute([
            ':id'      => $id
        ]);
        $dbPassword = $stmt->fetchColumn();
        if (!$dbPassword){
            return false; //Không tìm thấy password trong db
        }
        //Password người dùng nhập
        $userPassword = trim($password_need_to_be_checked);
        return password_verify($userPassword, $dbPassword);

    }catch(PDOException $e){
        sendError("Lỗi database: " . $e->getMessage(), 500);
        return false;
    }
}
//Kiểm tra xem new pass và confirm pass có giống nhau không
function samePassword(){
    if (trim($_POST['new_password']) === trim($_POST['confirm_password'])){
        return true;
    }
    return false;
}
//Xem người dùng bấm nút đổi mật khẩu hay chưa
if (isset($_POST['changePassword'])){
    if (checkPassword($_POST['current_password']) && samePassword()){
        changePassword();
    }else{
        $_SESSION['changePassResult'] = "Hãy kiểm tra xem bạn đã nhập đúng mật khẩu hay chưa.";
        $_SESSION['changePassType'] = "error";
        header("Location: ../../client/pages/profile.php");
        exit();
    }
} 


function deleteAccount(){
    global $conn;
    try {
        $sql = "DELETE FROM users
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $_SESSION['user_id']
        ]);
        //Xóa session sau khi xóa mật khẩu
        session_unset();
        session_destroy();
        header("Location: ../../client/pages/home.php");
        exit();
    }catch(PDOException $e){
        sendError("Lỗi database: " . $e->getMessage(), 500);
    }
}
if (isset($_POST['deleteAccount'])){
    if (checkPassword($_POST['password_confirmation_delete'])){
        deleteAccount();
    }else{
        //Nếu nhập sai mật khẩu thì không cho xóa tài khoản
        $_SESSION['password_confirmation_result'] = "Nhập sai mật khẩu";
        header("Location: ../../client/pages/profile.php");
        exit();
    }
}

require_once __DIR__ . '/../utils/fileHandler.php';

function changeAvatar() {
    global $conn;
    try {
        if (!isset($_FILES['avatar_file']) || $_FILES['avatar_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['changeAvatarResult'] = "Có lỗi xảy ra khi tải ảnh lên";
            header("Location: ../../client/pages/profile.php");
            exit();
        }

        // upload ảnh mới
        $avatarUrl = fh_upload_file($_FILES['avatar_file'], 'image');

        // xoá ảnh cũ
        $id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $oldAvatar = $stmt->fetchColumn();

        if ($oldAvatar && strpos($oldAvatar, '/server/uploads/') === 0) {
            fh_delete_file($oldAvatar);
        }

        // thêm vào database
        $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':avatar' => $avatarUrl,
            ':id' => $id
        ]);

        $_SESSION['avatar'] = $avatarUrl;
        $_SESSION['changeAvatarResult'] = "Cập nhật ảnh đại diện thành công";
        header("Location: ../../client/pages/profile.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['changeAvatarResult'] = $e->getMessage();
        header("Location: ../../client/pages/profile.php");
        exit();
    }
}

// xử lý yêu cầu đổi ảnh đại diện
if (isset($_POST['changeAvatar'])) {
    changeAvatar();
}

?>
