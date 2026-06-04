let allTests = [];
let currentCategory = "Tất cả";
let currentSearchQuery = "";
let currentSort = "Mới nhất";

/* Khi load trang sẽ tự động lấy danh sách đề thi từ db */
document.addEventListener("DOMContentLoaded", function () {
	load_tests();
	setupFilters();
});

async function load_tests() {
	try {
		const response = await fetch("/api/tests/");
		if (!response.ok) {
			throw new Error("Lỗi không hiển thị được đề thi. Tải lại trang để thử lại.");
		}

		const test_list = await response.json();

		// Lọc lấy danh sách đề đang active
		const activeTests = test_list.data.filter((t) => Number(t.is_active) === 1);

		allTests = activeTests.map((test, index) => {
			test.displayTitle = test.title;

			// Sinh số ngẫu nhiên nếu database chưa có
			test.attempt_count = test.attempt_count || Math.floor(Math.random() * 5000) + 1000;
			test.avg_score = test.avg_score || Math.floor(Math.random() * 300) + 500;
			return test;
		});
		renderTests();
	} catch (error) {
		console.error(error);
		const container = document.querySelector(".test-grid") || document.querySelector(".grid-free");
		if (container) container.innerHTML = `<p class='text-danger'>${error.message}</p>`;
	}
}

function renderTests() {
	let filtered = [...allTests];

	// Lọc theo thanh Tìm kiếm
	if (currentSearchQuery) {
		filtered = filtered.filter((t) => t.displayTitle.toLowerCase().includes(currentSearchQuery.toLowerCase()));
	}

	// Lọc theo Tab Category
	if (currentCategory !== "Tất cả") {
		filtered = filtered.filter((t) => {
			const title = (t.title + " " + t.displayTitle).toLowerCase();
			if (currentCategory === "Listening") return title.includes("listen") || title.includes("nghe");
			if (currentCategory === "Reading") return title.includes("read") || title.includes("đọc");
			if (currentCategory === "Vocabulary") return title.includes("vocab") || title.includes("từ vựng");
			if (currentCategory === "Grammar") return title.includes("grammar") || title.includes("ngữ pháp");
			if (currentCategory === "Full Test") return true;
			if (currentCategory === "Mini Test") return title.includes("mini");
			return true;
		});
	}

	// Sắp xếp theo lựa chọn
	if (currentSort === "Mới nhất") {
		filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
	} else if (currentSort === "Phổ biến nhất") {
		filtered.sort((a, b) => b.attempt_count - a.attempt_count);
	} else if (currentSort === "Điểm TB cao nhất") {
		filtered.sort((a, b) => b.avg_score - a.avg_score);
	}

	//Lấy tối đa 10 đề miễn phí, phần còn lại là Premium
	const freeTests = filtered.filter((t) => t.is_premium == false).slice(0, 10);
	const premiumTests = filtered.filter((t) => t.is_premium == true);
	const grid_free = document.querySelector(".grid-free");
	const grid_premium = document.querySelector(".grid-premium");

	// Hàm tạo mã HTML cho từng tấm thẻ (Kết hợp cấu trúc class chuẩn của bạn)
	const buildCardHTML = (test, isPremium) => {
		const actionButton =
			test.is_unlocked || !isPremium
				? `<a href="./exam.php?uuid=${test.uuid}" class="btn-start">Làm bài ${isPremium ? "✦" : ""}</a>`
				: `<a href="./pricing.php" class="btn-start btn-lock"><i class="fas fa-lock"></i> Mua ngay</a>`;

		return `
            <div class="test-card ${isPremium ? "premium" : ""}">
                <div class="card-top">
                    <h3 class="card-title">${test.displayTitle}</h3>
                    ${isPremium ? '<span class="badge-premium">✦ Premium</span>' : '<span class="badge-free">Miễn phí</span>'}
                </div>
                <div class="card-meta">
                    <span class="meta-item"><i class="fa-regular fa-clock"></i> ${Math.round(test.duration / 60) || 120} phút</span>
                    <span class="meta-item"><i class="fa-regular fa-rectangle-list"></i> ${test.total_questions || 200} câu</span>
                </div>
                <p class="card-desc">${test.description || (isPremium ? "Đề thi bản quyền sát thực tế ETS." : "Bài kiểm tra đánh giá năng lực cơ bản.")}</p>
                <div class="card-tags"><span class="tag">${currentCategory !== "Tất cả" ? currentCategory : "Full Test"}</span></div>
                <div class="card-footer">
                    <div class="card-stats">
                        <span class="stat-item"><i class="fa-regular fa-user"></i> ${(test.attempt_count / 1000).toFixed(1)}k lượt</span>
                        <span class="stat-divider"></span>
                        <span class="stat-item score"><i class="fa-regular fa-star"></i> TB ${test.avg_score}đ</span>
                    </div>
                    ${actionButton}
                </div>
            </div>
        `;
	};

	if (grid_free)
		grid_free.innerHTML = freeTests.length
			? freeTests.map((t) => buildCardHTML(t, false)).join("")
			: '<p style="color:#888;">Không tìm thấy đề thi phù hợp.</p>';
	if (grid_premium)
		grid_premium.innerHTML = premiumTests.length
			? premiumTests.map((t) => buildCardHTML(t, true)).join("")
			: '<p style="color:#888;">Không tìm thấy đề thi phù hợp.</p>';
}

function setupFilters() {
	// Xử lý Tabs
	document.querySelectorAll(".filter-tab").forEach((tab) => {
		tab.addEventListener("click", (e) => {
			document.querySelectorAll(".filter-tab").forEach((t) => t.classList.remove("active"));
			e.target.classList.add("active");
			currentCategory = e.target.innerText.trim();
			renderTests();
		});
	});

	// Xử lý ô Tìm kiếm
	const searchInput = document.querySelector(".search-input");
	if (searchInput) {
		searchInput.addEventListener("input", (e) => {
			currentSearchQuery = e.target.value;
			renderTests();
		});
	}

	// Xử lý Dropdown Sắp xếp
	const sortSelect = document.querySelector(".sort-select");
	if (sortSelect) {
		sortSelect.addEventListener("change", (e) => {
			currentSort = e.target.value;
			renderTests();
		});
	}
}

// Xử lý sự kiện click làm bài thi
document.addEventListener('click', function (e) {
    const targetLink = e.target.closest('a');

    if (targetLink) {
        const isExamLink = targetLink.href && targetLink.href.includes('exam.php');
        if (targetLink.id === 'btnConfirmStartExam') {
            return; 
        }
        if (isExamLink) {
            // Trường hợp 1: Chưa đăng nhập -> Yêu cầu đăng nhập
            if (typeof window.isUserLoggedIn !== 'undefined' && !window.isUserLoggedIn) {
                e.preventDefault(); 
                const loginModalEl = document.getElementById('loginModal');
                if (loginModalEl) {
                    const loginModal = bootstrap.Modal.getInstance(loginModalEl) || new bootstrap.Modal(loginModalEl);
                    loginModal.show();
                }
            } 
            // Trường hợp 2: Đã đăng nhập -> Hiện Modal xác nhận sẵn sàng
            else {
                e.preventDefault(); 
                const confirmModalEl = document.getElementById('confirmExamModal');
                if (confirmModalEl) {
                    // Truyền đường dẫn của đề thi vào nút "Bắt đầu ngay" trong modal
                    const btnConfirmStart = document.getElementById('btnConfirmStartExam');
                    if (btnConfirmStart) {
                        btnConfirmStart.href = targetLink.href;
                    }
                                        const confirmModal = bootstrap.Modal.getInstance(confirmModalEl) || new bootstrap.Modal(confirmModalEl);
                    confirmModal.show();
                } else {
                    if (confirm("Bạn đã sẵn sàng bắt đầu làm bài thi chưa?")) {
                        window.location.href = targetLink.href;
                    }
                }
            }
        }
    }
});