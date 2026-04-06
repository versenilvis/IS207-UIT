// logic login, register, lưu auth token

//Chọn ra Login hoặc Register form dựa vào formID
function showForm(formID) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formID).classList.add("active");
}

//Kiểm tra xem ô nhập lại password có giống với ô password chưa
let registerForm = document.querySelector('#register-form form');
let passwordInput = document.getElementById("password");
let reEnterPassword = document.getElementById("rePassword");
let checkPasswordMatch = document.getElementById("checkPasswordError");

registerForm.addEventListener("submit", function(event){
    if (passwordInput.value !== reEnterPassword.value){
        event.preventDefault();
        checkPasswordMatch.textContent = "Password does not match!";
        checkPasswordMatch.style.display = "block";
    }else{
        checkPasswordMatch.textContent = "";
    }
});

//Nút hiện và ẩn mật khẩu
function togglePassword(eye){
   let input = eye.previousElementSibling;

    if (input.type === "password"){
        eye.src = "../img/eye_open.png";
        input.type = "text"
    }else{
        eye.src = "../img/eye_close.png";
        input.type = "password"
    }
}
