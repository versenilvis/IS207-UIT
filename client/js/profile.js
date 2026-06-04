// Nút toggle con mắt trong password (nó giống như ở login ấy)
function togglePassword(eye) {
    let input = eye.previousElementSibling;
    let icon = eye.querySelector("img");

    if (input.type === "password") {
        icon.src = "../img/eye_open.png";
        input.type = "text";
    } else {
        icon.src = "../img/eye_close.png";
        input.type = "password";
    }
}


//Hiển thị cửa sổ xác nhận khi bấm nút xóa tài khoản (UI)
document.addEventListener("DOMContentLoaded", function () {
    const popup = document.getElementById("delete-popup");
    const openBtn = document.getElementById("open-delete-popup");
    const closeBtn = document.getElementById("close-delete-popup");
    const cancelBtn = document.getElementById("cancel-delete-popup");

    if (!popup || !openBtn) {
        return;
    }

    function openPopup() {
        popup.classList.add("show");
        document.body.style.overflow = "hidden";
    }

    function closePopup() {
        popup.classList.remove("show");
        document.body.style.overflow = "";
    }

    openBtn.addEventListener("click", openPopup);

    if (closeBtn) {
        closeBtn.addEventListener("click", closePopup);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", closePopup);
    }

    popup.addEventListener("click", function (event) {
        if (event.target === popup) {
            closePopup();
        }
    });

	// xác thực form đổi mật khẩu
    const passwordForm = document.getElementById("passwordForm");
    const currentPassword = document.getElementById("current-password");
	// hiển thị lỗi khi gõ sai định dạng mật khẩu
    const newPassword = document.getElementById("new-password");
    const confirmPassword = document.getElementById("confirm-password");

    function isFieldInvalid(inputEl) {
        const group = inputEl.closest(".settings-form-group");
        return !!group.querySelector(".form-error-inline")
    }

    function validateCurrentPassword(showError = false) {
        if (!currentPassword) return true
        if (!currentPassword.value) {
            if (showError) {
                showInlineError(currentPassword, "Mật khẩu hiện tại không được để trống");
            }
            return false
        }
        clearInlineError(currentPassword);
        return true
    }

    function validateNewPassword(showError = false) {
        if (!newPassword) return true
        const val = newPassword.value;
        if (!val) {
            if (showError) {
                showInlineError(newPassword, "Mật khẩu mới không được để trống");
            }
            return false
        }
        if (val.length < 8) {
            if (showError) {
                showInlineError(newPassword, "Mật khẩu mới phải có ít nhất 8 ký tự");
            }
            return false
        }
        if (!/[a-zA-Z]/.test(val)) {
            if (showError) {
                showInlineError(newPassword, "Mật khẩu mới phải có ít nhất 1 chữ cái");
            }
            return false
        }
        if (!/[0-9]/.test(val)) {
            if (showError) {
                showInlineError(newPassword, "Mật khẩu mới phải có ít nhất 1 số");
            }
            return false
        }
        clearInlineError(newPassword);
        return true
    }

    function validateConfirmPassword(showError = false) {
        if (!confirmPassword || !newPassword) return true
        const val = confirmPassword.value;
        if (!val) {
            if (showError) {
                showInlineError(confirmPassword, "Xác nhận mật khẩu không được để trống");
            }
            return false
        }
        if (val !== newPassword.value) {
            if (showError) {
                showInlineError(confirmPassword, "Mật khẩu xác nhận không trùng khớp");
            }
            return false
        }
        clearInlineError(confirmPassword);
        return true
    }

    if (currentPassword) {
        currentPassword.addEventListener("blur", function () {
            validateCurrentPassword(true);
        });
        currentPassword.addEventListener("input", function () {
            validateCurrentPassword(isFieldInvalid(currentPassword));
        });
    }

    if (newPassword) {
        newPassword.addEventListener("blur", function () {
            validateNewPassword(true);
            if (confirmPassword && confirmPassword.value) {
                validateConfirmPassword(true);
            }
        });
        newPassword.addEventListener("input", function () {
            validateNewPassword(isFieldInvalid(newPassword));
            if (confirmPassword && confirmPassword.value) {
                validateConfirmPassword(isFieldInvalid(confirmPassword));
            }
        });
    }

    if (confirmPassword) {
        confirmPassword.addEventListener("blur", function () {
            validateConfirmPassword(true);
        });
        confirmPassword.addEventListener("input", function () {
            validateConfirmPassword(isFieldInvalid(confirmPassword));
        });
    }

    if (passwordForm) {
        passwordForm.addEventListener("submit", function (e) {
            const isCurrentValid = validateCurrentPassword(true);
            const isNewValid = validateNewPassword(true);
            const isConfirmValid = validateConfirmPassword(true);

            if (!isCurrentValid || !isNewValid || !isConfirmValid) {
                e.preventDefault();
                if (!isCurrentValid) {
                    currentPassword.focus();
                } else if (!isNewValid) {
                    newPassword.focus();
                } else if (!isConfirmValid) {
                    confirmPassword.focus();
                }
            }
        });
    }

    function showInlineError(inputEl, message) {
        const group = inputEl.closest(".settings-form-group");
        let errorSpan = group.querySelector(".form-error-inline");
        if (!errorSpan) {
            errorSpan = document.createElement("div");
            errorSpan.className = "form-error-inline";
            group.appendChild(errorSpan);
        }
        errorSpan.innerText = message;
    }

    function clearInlineError(inputEl) {
        const group = inputEl.closest(".settings-form-group");
        const errorSpan = group.querySelector(".form-error-inline");
        if (errorSpan) {
            errorSpan.remove();
        }
    }

    // xử lí upload avatar
    const avatarContainer = document.querySelector(".profile-avatar-container");
    const avatarInput = document.getElementById("avatar-file-input");
    const avatarForm = document.getElementById("avatar-upload-form");

    if (avatarContainer && avatarInput && avatarForm) {
        avatarContainer.addEventListener("click", function () {
            avatarInput.click();
        });

        avatarInput.addEventListener("change", function () {
            if (avatarInput.files && avatarInput.files.length > 0) {
                avatarForm.submit();
            }
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && popup.classList.contains("show")) {
            closePopup();
        }
    });
});
