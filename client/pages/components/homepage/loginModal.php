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

                                <div class="auth-options">
                                    <label class="checkbox-custom">
                                        <input type="checkbox" name="remember">
                                        <span class="checkmark"></span>
                                        <span class="label-text">Ghi nhớ đăng nhập</span>
                                    </label>
                                    <a href="#" class="forgot-link">Quên mật khẩu?</a>
                                </div>

                                <button type="submit" name="login" class="btn-auth-submit">Đăng nhập</button>
                            </form>

                            <div class="auth-divider">
                                <span>Hoặc tiếp tục với</span>
                            </div>

                            <button class="btn-google-auth">
                                <svg viewBox="0 0 48 48" width="20" height="20">
                                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s12-5.373 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-2.641-.21-5.236-.611-7.743z" />
                                    <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
                                    <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
                                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.022 35.026 44 30.038 44 24c0-2.641-.21-5.236-.611-7.743z" />
                                </svg>
                                <span>Tiếp tục với Google</span>
                            </button>

                            <p class="auth-switch-text">
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
                                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                                        <button type="button" class="eye-toggle">
                                            <img src="../img/eye_close.png" alt="view" class="eye-icon">
                                        </button>
                                    </div>
                                </div>

                                <div class="input-group-glass">
                                    <label>Nhập lại mật khẩu</label>
                                    <div class="input-wrapper relative">
                                        <input type="password" name="reenter_password" id="signupRePass" placeholder="••••••••" required>
                                        <button type="button" class="eye-toggle"><i class='bx bx-hide'></i></button>
                                    </div>
                                </div>

                                <button type="submit" name="register" class="btn-auth-submit">Đăng ký tài khoản</button>
                            </form>

                            <p class="auth-switch-text">
                                Đã có tài khoản? <a href="#" id="toSignin">Đăng nhập</a>
                            </p>
                        </div>
                    </div>

                    <!-- Visual Side -->
                    <div class="auth-side auth-visual-side">
                        <div class="visual-overlay"></div>
                        <div class="visual-content">
                            <div class="testimonial-stack">
                                <div class="testimonial-card animate-1">
                                    <img src="https://randomuser.me/api/portraits/women/57.jpg" alt="user">
                                    <div class="testi-info">
                                        <p class="testi-name">Sarah Chen <i class='bx bxs-badge-check'></i></p>
                                        <p class="testi-handle">@sarahdigital</p>
                                        <p class="testi-text">Nền tảng tuyệt vời! Trải nghiệm người dùng mượt mà và các tính năng rất hữu ích cho việc ôn tập.</p>
                                    </div>
                                </div>
                                <div class="testimonial-card animate-2">
                                    <img src="https://randomuser.me/api/portraits/men/64.jpg" alt="user">
                                    <div class="testi-info">
                                        <p class="testi-name">Marcus Johnson <i class='bx bxs-badge-check'></i></p>
                                        <p class="testi-handle">@marcustech</p>
                                        <p class="testi-text">PrepHub đã thay đổi hoàn toàn cách tôi học TOEIC. Giao diện sạch sẽ và lộ trình cực kỳ rõ ràng.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close-auth" data-bs-dismiss="modal">
                        <i class='bx bx-x'></i>
                    </button>
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
    const loginModalEl = document.getElementById('loginModal');

    if (loginModalEl) {
        const initModal = () => {
            if (typeof bootstrap === 'undefined') {
                setTimeout(initModal, 100);
                return;
            }
            const loginModal = new bootstrap.Modal(loginModalEl);
            
            if (toSignup && toSignin && authWrapper) {
                toSignup.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.add('signup-active');
                });
                toSignin.addEventListener('click', (e) => {
                    e.preventDefault();
                    authWrapper.classList.remove('signup-active');
                });
            }

            // Check if we need to show the modal (on error)
            <?php 
            $hasErrors = !empty($errors['login'] ?? '') || !empty($errors['register'] ?? '') || !empty($errors['success'] ?? '');
            $activeForm = $activeAuthForm ?? 'login';
            if ($hasErrors): ?>
                <?php if ($activeForm === 'register'): ?>
                    if (authWrapper) authWrapper.classList.add('signup-active');
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
});
</script>
