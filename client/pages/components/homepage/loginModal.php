<!-- Login/Signup Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content auth-modal-content">
            <div class="modal-body p-0">
                <div class="auth-wrapper" id="authWrapper">
                    <!-- Left: Sign In Form -->
                    <div class="auth-side auth-form-side sign-in-side">
                        <div class="auth-container">
                            <div class="auth-header">
                                <h1>Chào mừng</h1>
                                <p>Đăng nhập để tiếp tục lộ trình học TOEIC của bạn</p>
                                <?php 
                                if (function_exists('showSuccess')) {
                                    echo showSuccess($errors['success'] ?? ''); 
                                }
                                if (function_exists('showError')) {
                                    echo showError($errors['login'] ?? ''); 
                                }
                                ?>
                            </div>
                            
                            <form id="loginForm" class="auth-form" action="/api/auth/login" method="POST">
                                <div class="input-group-glass">
                                    <label>Email</label>
                                    <div class="input-wrapper">
                                        <input type="email" name="email" placeholder="name@example.com" required>
                                    </div>
                                </div>
                                
                                <div class="input-group-glass">
                                    <label>Mật khẩu</label>
                                    <div class="input-wrapper relative">
                                        <input type="password" name="password" placeholder="••••••••" required>
                                        <button type="button" class="eye-toggle">
                                            <img src="../img/eye_close.png" alt="view" class="eye-icon">
                                        </button>
                                    </div>
                                </div>

                                <div class="auth-options justify-content-end">
                                    <a href="#" class="forgot-link" id="toForgot">Quên mật khẩu?</a>
                                </div>

                                <button type="submit" name="login" class="btn-auth-submit">Đăng nhập</button>
                            </form>

                            <div class="auth-divider">
                                <span>Hoặc tiếp tục với</span>
                            </div>

                            <a class="btn-google-auth" href="/api/auth/google">
                                <svg viewBox="0 0 48 48" width="20" height="20">
                                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s12-5.373 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-2.641-.21-5.236-.611-7.743z" />
                                    <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
                                    <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
                                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.022 35.026 44 30.038 44 24c0-2.641-.21-5.236-.611-7.743z" />
                                </svg>
                                <span>Tiếp tục với Google</span>
                            </a>

                            <p class="auth-switch-text">
                            <!--NÚT ĐĂNG KÝ-->
                                Chưa có tài khoản? <a href="#" id="toSignup">Đăng ký ngay</a>
                            </p>
                        </div>
                    </div>

                    <!-- Right: Sign Up Form -->
                    <div class="auth-side auth-form-side sign-up-side">
                        <div class="auth-container">
                            <div class="auth-header">
                                <h1>Tạo tài khoản mới</h1>
                                <p>Bắt đầu hành trình chinh phục TOEIC cùng PrepHub</p>
                                <?php 
                                if (function_exists('showError')) {
                                    echo showError($errors['register'] ?? ''); 
                                }
                                ?>
                                <div class="register-success-notice" id="registerSuccessNotice" role="status" aria-live="polite">
                                    <strong>Đăng ký thành công!</strong>
                                    <span>Hãy xác thực tại email của bạn.</span>
                                </div>
                            </div>
                            
                            <form id="signupForm" class="auth-form" action="/api/auth/register" method="POST">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="input-group-glass">
                                            <label>Tên</label>
                                            <div class="input-wrapper">
                                                <input type="text" name="first_name" placeholder="Tên" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group-glass">
                                            <label>Họ</label>
                                            <div class="input-wrapper">
                                                <input type="text" name="last_name" placeholder="Họ" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="input-group-glass">
                                    <label>Email Address</label>
                                    <div class="input-wrapper">
                                        <input type="email" name="email" placeholder="name@example.com" required>
                                    </div>
                                </div>

                                <div class="input-group-glass">
                                    <label>Mật khẩu</label>
                                    <div class="input-wrapper relative">
                                        <input type="password" name="password" id="password" placeholder="••••••••" minlength="8" required>
                                        <button type="button" class="eye-toggle">
                                            <img src="../img/eye_close.png" alt="view" class="eye-icon">
                                        </button>
                                    </div>
                                </div>

                                <div class="input-group-glass">
                                    <label>Nhập lại mật khẩu</label>
                                    <div class="input-wrapper relative">
                                        <input type="password" name="reenter_password" id="signupRePass" placeholder="••••••••" minlength="8" required>
                                        <button type="button" class="eye-toggle">
                                            <img src="../img/eye_close.png" alt="view" class="eye-icon">
                                        </button>
                                    </div>
                                </div>

                                <p class="auth-inline-message" id="signupStatus" role="alert" aria-live="polite"></p>

                                <button type="submit" name="register" class="btn-auth-submit">Đăng ký tài khoản</button>
                            </form>

                            <p class="auth-switch-text">
                                Đã có tài khoản? <a href="#" id="toSignin">Đăng nhập</a>
                            </p>
                        </div>
                    </div>

                    <!-- Forgot Password Form -->
                    <div class="auth-side auth-form-side forgot-side">
                        <div class="auth-container">
                            <div class="auth-header">
                                <h1>Quên mật khẩu</h1>
                                <p>Nhập email đăng ký để nhận hướng dẫn đặt lại mật khẩu</p>
                            </div>

                            <form id="forgotForm" class="auth-form" action="/api/auth/reset" method="POST">
                                <div class="input-group-glass">
                                    <label>Email</label>
                                    <div class="input-wrapper">
                                        <input type="email" name="email" placeholder="name@example.com" required>
                                    </div>
                                </div>

                                <p class="forgot-status" id="forgotStatus" role="status" aria-live="polite"></p>

                                <button type="submit" name="forgot_password" class="btn-auth-submit">Gửi</button>
                            </form>

                            <p class="auth-switch-text">
                                Nhớ mật khẩu? <a href="#" id="forgotToSignin">Đăng nhập</a>
                            </p>
                        </div>
                    </div>

                    <!-- Reset Password Form -->
                    <div class="auth-side auth-form-side reset-side">
                        <div class="auth-container">
                            <div class="auth-header">
                                <h1>Đặt lại mật khẩu</h1>
                                <p>Tạo mật khẩu mới cho tài khoản của bạn để tiếp tục học trên PrepHub</p>
                            </div>

                            <form id="resetPasswordForm" class="auth-form" action="#" method="POST">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($resetToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <div class="input-group-glass">
                                    <label>Mật khẩu mới</label>
                                    <div class="input-wrapper relative">
                                        <input type="password" name="password" id="resetPassword" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="8" required>
                                        <button type="button" class="eye-toggle">
                                            <img src="../img/eye_close.png" alt="view" class="eye-icon">
                                        </button>
                                    </div>
                                </div>

                                <div class="input-group-glass">
                                    <label>Nhập lại mật khẩu</label>
                                    <div class="input-wrapper relative">
                                        <input type="password" name="confirm_password" id="resetConfirmPassword" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" minlength="8" required>
                                        <button type="button" class="eye-toggle">
                                            <img src="../img/eye_close.png" alt="view" class="eye-icon">
                                        </button>
                                    </div>
                                </div>

                                <p class="password-hint">Mật khẩu nên có ít nhất 8 ký tự.</p>
                                <p class="error-message reset-match-error" id="resetMatchError"></p>

                                <button type="submit" name="reset_password" class="btn-auth-submit">Cập nhật mật khẩu</button>
                            </form>

                            <p class="auth-switch-text">
                                Quay lại <a href="#" id="resetToSignin">đăng nhập</a>
                            </p>
                        </div>
                    </div>
                    <!-- Visual Side -->
                    <div class="auth-side auth-visual-side">
                        <div class="visual-overlay"></div>
                        <div class="visual-content">
                            <div class="testimonial-stack">
                                <div class="testimonial-card animate-1">
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Tran+Hai+Yen" alt="user">
                                    <div class="testi-info">
                                        <p class="testi-name">Trần Hải Yến <i class='bx bxs-badge-check'></i></p>
                                        <p class="testi-handle">@haiyen_digital</p>
                                        <p class="testi-text">Nền tảng tuyệt vời! Trải nghiệm người dùng mượt mà và các tính năng rất hữu ích cho việc ôn tập.</p>
                                    </div>
                                </div>
                                <div class="testimonial-card animate-2">
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Nguyen+Hoang+Nam" alt="user">
                                    <div class="testi-info">
                                        <p class="testi-name">Nguyễn Hoàng Nam <i class='bx bxs-badge-check'></i></p>
                                        <p class="testi-handle">@nam_tech</p>
                                        <p class="testi-text">PrepHub đã thay đổi hoàn toàn cách tôi học TOEIC. Giao diện sạch sẽ và lộ trình cực kỳ rõ ràng.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const authWrapper = document.getElementById('authWrapper');
    const toSignup = document.getElementById('toSignup');
    const toSignin = document.getElementById('toSignin');
    const toForgot = document.getElementById('toForgot');
    const forgotToSignin = document.getElementById('forgotToSignin');
    const resetToSignin = document.getElementById('resetToSignin');
    const loginModalEl = document.getElementById('loginModal');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const resetPassword = document.getElementById('resetPassword');
    const resetConfirmPassword = document.getElementById('resetConfirmPassword');
    const resetMatchError = document.getElementById('resetMatchError');
    const forgotForm = document.getElementById('forgotForm');
    const forgotStatus = document.getElementById('forgotStatus');
    const signupForm = document.getElementById('signupForm');
    const registerSuccessNotice = document.getElementById('registerSuccessNotice');
    const signupPassword = document.getElementById('password');
    const signupRePass = document.getElementById('signupRePass');
    const signupStatus = document.getElementById('signupStatus');

    if (loginModalEl) {
        const initModal = () => {
            if (typeof bootstrap === 'undefined') {
                setTimeout(initModal, 100);
                return;
            }
            const loginModal = new bootstrap.Modal(loginModalEl);

            loginModalEl.addEventListener('show.bs.modal', (e) => {
                const triggerBtn = e.relatedTarget;
                if (triggerBtn && triggerBtn.getAttribute('data-auth-tab') === 'signup') {
                    authWrapper.classList.remove('forgot-active');
                    authWrapper.classList.remove('reset-active');
                    authWrapper.classList.add('signup-active');
                } else if (triggerBtn) {
                    authWrapper.classList.remove('signup-active');
                    authWrapper.classList.remove('forgot-active');
                    authWrapper.classList.remove('reset-active');
                }
            });
            
            if (toSignup && toSignin && authWrapper) {
                toSignup.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.remove('forgot-active');
                    authWrapper.classList.remove('reset-active');
                    authWrapper.classList.add('signup-active');
                });
                toSignin.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.remove('signup-active');
                    authWrapper.classList.remove('forgot-active');
                    authWrapper.classList.remove('reset-active');
                });
            }

            if (toForgot && authWrapper) {
                toForgot.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.remove('signup-active');
                    authWrapper.classList.remove('reset-active');
                    authWrapper.classList.add('forgot-active');
                });
            }

            if (forgotToSignin && authWrapper) {
                forgotToSignin.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.remove('forgot-active');
                    authWrapper.classList.remove('signup-active');
                    authWrapper.classList.remove('reset-active');
                });
            }

            if (resetToSignin && authWrapper) {
                resetToSignin.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.remove('reset-active');
                    authWrapper.classList.remove('forgot-active');
                    authWrapper.classList.remove('signup-active');
                });
            }

            // Check if we need to show the modal (on error)
            <?php 
            $hasErrors = !empty($errors['login'] ?? '') || !empty($errors['register'] ?? '') || !empty($errors['success'] ?? '');
            $activeForm = $activeAuthForm ?? 'login';
            $hasResetToken = !empty($resetToken ?? '');
            if ($hasErrors || $hasResetToken): ?>
                <?php if ($activeForm === 'register'): ?>
                    if (authWrapper) authWrapper.classList.add('signup-active');
                <?php elseif ($activeForm === 'reset' || $hasResetToken): ?>
                    if (authWrapper) authWrapper.classList.add('reset-active');
                <?php endif; ?>
                loginModal.show();
            <?php endif; ?>
        };
        initModal();
    }

    // eye toggle logic
    document.querySelectorAll('.eye-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const iconImg = this.querySelector('img');
            if (!input || !iconImg) return;
            
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            iconImg.src = isPassword ? '../img/eye_open.png' : '../img/eye_close.png';
        });
    });

    if (resetPasswordForm && resetPassword && resetConfirmPassword && resetMatchError) {
        resetPasswordForm.addEventListener('submit', (event) => {
            event.preventDefault();

            if (resetPassword.value !== resetConfirmPassword.value) {
                resetMatchError.textContent = 'Mat khau nhap lai khong khop.';
                resetMatchError.style.display = 'block';
                return;
            }

            resetMatchError.textContent = '';
            resetMatchError.style.display = 'none';
        });
    }

    if (forgotForm && forgotStatus) {
        forgotForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            forgotStatus.textContent = 'Hãy kiểm tra email của bạn.';
            forgotStatus.style.display = 'block';

            try {
                await fetch(forgotForm.action, {
                    method: 'POST',
                    body: new FormData(forgotForm),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } catch (error) {
                console.error('Forgot password request failed.', error);
            }
        });
    }

    if (signupForm && registerSuccessNotice) {
        const setSignupStatus = (message, type = 'error') => {
            if (!signupStatus) return;
            signupStatus.textContent = message;
            signupStatus.classList.toggle('is-error', type === 'error');
            signupStatus.classList.toggle('is-success', type === 'success');
            signupStatus.style.display = message ? 'block' : 'none';
        };

        const validateSignupPassword = () => {
            if (!signupPassword) return '';

            const password = signupPassword.value;
            if (password.length < 8) {
                return 'Mật khẩu phải có ít nhất 8 ký tự.';
            }

            if (!/[a-z]/i.test(password)) {
                return 'Mật khẩu phải có ít nhất 1 chữ cái.';
            }

            if (!/[0-9]/.test(password)) {
                return 'Mật khẩu phải có ít nhất 1 số.';
            }

            return '';
        };

        const clearSignupValidation = () => {
            if (signupPassword) signupPassword.removeAttribute('aria-invalid');
            if (signupRePass) signupRePass.removeAttribute('aria-invalid');
            setSignupStatus('');
        };

        const updateSignupPasswordStatus = () => {
            if (!signupPassword || !signupRePass) return true;

            signupPassword.removeAttribute('aria-invalid');
            signupRePass.removeAttribute('aria-invalid');

            if (!signupPassword.value && !signupRePass.value) {
                setSignupStatus('');
                return true;
            }

            const passwordError = validateSignupPassword();
            if (passwordError) {
                signupPassword.setAttribute('aria-invalid', 'true');
                setSignupStatus(passwordError);
                return false;
            }

            if (signupRePass.value && signupPassword.value !== signupRePass.value) {
                signupPassword.setAttribute('aria-invalid', 'true');
                signupRePass.setAttribute('aria-invalid', 'true');
                setSignupStatus('Mật khẩu nhập lại không khớp!');
                return false;
            }

            setSignupStatus('Mật khẩu hợp lệ.', 'success');
            return true;
        };

        [signupPassword, signupRePass].forEach((input) => {
            if (!input) return;
            input.addEventListener('input', updateSignupPasswordStatus);
        });

        signupForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!updateSignupPasswordStatus()) {
                if (signupPassword && signupPassword.hasAttribute('aria-invalid')) {
                    signupPassword.focus();
                } else if (signupRePass) {
                    signupRePass.focus();
                }
                return;
            }

            clearSignupValidation();

            const submitButton = signupForm.querySelector('button[type="submit"]');
            const formData = new FormData(signupForm);
            if (submitButton) submitButton.disabled = true;
            registerSuccessNotice.style.display = 'none';

            try {
                const response = await fetch(signupForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok || result.success === false) {
                    setSignupStatus(result.message || 'Đăng ký không thành công. Vui lòng thử lại.');
                    return;
                }

                registerSuccessNotice.style.display = 'block';
                signupForm.reset();
                signupForm.style.display = 'none';
            } catch (error) {
                console.error('Register request failed.', error);
                setSignupStatus('Không gửi được email xác thực. Vui lòng thử lại sau.');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }
});
</script>
