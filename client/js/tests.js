/* Khi load trang sẽ tự động lấy danh sách đề thi từ db */
document.addEventListener("DOMContentLoaded", function(){
    load_tests();
});

// Ô filter đề thi ở đầu trang
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});

/* Lấy danh sách đề thi và hiển thị chi tiết (Trang tests.php) */
async function load_tests() {
    try {
        const response = await fetch('/api/tests/');
        if (!response.ok) {
            throw new Error("Lỗi không hiển thị được đề thi. Tải lại trang để thử lại.");
        }

        const test_list = await response.json();
        const tests = test_list.data;

        // Lọc đề thi Active
        const activeTests = tests.filter(test => Number(test.is_active) === 1);
        const freeTests = activeTests.filter(test => test.is_premium == false);
        const premiumTests = activeTests.filter(test => test.is_premium == true);

        // Hiển thị đề thi FREE
        let grid_free = document.querySelector('.grid-free');
        if (grid_free) {
            grid_free.innerHTML = "";
            freeTests.forEach(test => {
                grid_free.innerHTML += `
                    <div class="test-card">
                        <div class="card-top">
                            <h3 class="card-title">${test.title}</h3>
                            <span class="badge-free">Miễn phí</span>
                        </div>
                        <div class="card-meta">
                            <span class="meta-item"><i class="fa-regular fa-clock"></i> ${test.duration / 60} phút</span>
                            <span class="meta-item"><i class="fa-regular fa-rectangle-list"></i> ${test.total_questions} câu</span>
                        </div>
                        <p class="card-desc">${test.description || 'Bài kiểm tra mẫu miễn phí.'}</p>
                        <div class="card-tags"><span class="tag">Full Test</span></div>
                        <div class="card-footer">
                            <div class="card-stats">
                                <span class="stat-item"><i class="fa-regular fa-user"></i> 4,8k lượt</span>
                                <span class="stat-divider"></span>
                                <span class="stat-item score"><i class="fa-regular fa-star"></i> TB 72đ</span>
                            </div>
                            <a href="./exam.php?uuid=${test.uuid}" class="btn-start">Làm bài</a>
                        </div>
                    </div>
                `;
            });
        }

        // Hiển thị đề thi PREMIUM
        let grid_premium = document.querySelector('.grid-premium');
        if (grid_premium) {
            grid_premium.innerHTML = "";
            premiumTests.forEach(test => {
                // LOGIC QUAN TRỌNG: Kiểm tra nếu đã mở khóa thì hiện "Làm bài", nếu chưa thì hiện "Đăng ký"
                const actionButton = test.is_unlocked 
                    ? `<a href="./exam.php?uuid=${test.uuid}" class="btn-start">Làm bài ✦</a>`
                    : `<a href="./pricing.php" class="btn-start btn-lock"><i class="fas fa-lock"></i> Mua ngay</a>`;

                grid_premium.innerHTML += `
                    <div class="test-card premium">
                        <div class="card-top">
                            <div class="card-title">${test.title}</div>
                            <span class="badge badge-premium">✦ Premium</span>
                        </div>
                        <div class="card-meta">
                            <span class="meta-item"><i class="fa-regular fa-clock"></i> ${test.duration / 60} phút</span>
                            <span class="meta-item"><i class="fa-regular fa-rectangle-list"></i> ${test.total_questions} câu</span>
                        </div>
                        <div class="card-desc">${test.description || 'Đề thi bản quyền sát thực tế.'}</div>
                        <div class="card-tags"><span class="tag">Full Test</span></div>
                        <div class="card-footer">
                            <div class="card-stats">
                                <div class="stat-item"><i class="fa-regular fa-user"></i> 6,5k lượt</div>
                                <div class="stat-divider"></div>
                                <div class="stat-item"><i class="fa-regular fa-star"></i> TB 665đ</div>
                            </div>
                            ${actionButton}
                        </div>
                    </div>
                `;
            });
        }

    } catch (error) {
        console.error(error);
        const container = document.querySelector(".test-grid") || document.querySelector(".grid-free");
        if (container) container.innerHTML = `<p class='text-danger'>${error.message}</p>`;
    }
}
