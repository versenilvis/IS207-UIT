// Khai báo biến toàn cục
let historyData = [];
let filteredData = [];
let currentAttemptPage = 1;
let rowsPerPage = 5;
let isSortedByScore = false;
function getScoreColor(score, isTotal = false) {
	let thresholdGreen = isTotal ? 800 : 400;
	let thresholdOrange = isTotal ? 600 : 300;
	if (score >= thresholdGreen) return "text-green";
	if (score >= thresholdOrange) return "text-orange";
	return "text-red";
}

document.addEventListener("DOMContentLoaded", function () {
	fetch("../../server/controllers/attempts-controller.php")
		.then((res) => res.json())
		.then((response) => {
			if (response.status === "success") {
				historyData = response.data.history;
				filteredData = [...historyData];
				displayTablePage(currentAttemptPage);

				// --- GẮN SỰ KIỆN LỌC TỰ ĐỘNG ---
				// 1. Khi chọn dropdown thời gian -> Tự động Lọc
				document.getElementById("timeFilter").addEventListener("change", applyFilters);

				// 2. Khi gõ vào ô tìm kiếm -> Tự động Lọc (dùng sự kiện 'input' để lọc ngay khi đang gõ)
				document.getElementById("searchInput").addEventListener("input", applyFilters);

				// --- GẮN SỰ KIỆN CHO NÚT BẤM ---
				// 3. Nút bấm bây giờ CHỈ GỌI HÀM SORT
				document.getElementById("filterBtn").addEventListener("click", sortData);
			}
		})
		.catch((error) => console.error("Lỗi khi fetch data:", error));
});

// --- HÀM XỬ LÝ LỌC DỮ LIỆU ĐÃ ĐƯỢC TỐI ƯU ---
function applyFilters() {
	const keyword = document.getElementById("searchInput").value.toLowerCase().trim();
	const timeVal = document.getElementById("timeFilter").value;

	const now = new Date();
	const sevenDaysAgo = new Date();
	sevenDaysAgo.setDate(now.getDate() - 7);

	filteredData = historyData.filter((row) => {
		// 1. Lọc từ khóa
		const examName = row.exam_name ? row.exam_name.toLowerCase() : "";
		const matchSearch = examName.includes(keyword);

		// 2. Lọc thời gian
		let matchTime = true;

		if (timeVal !== "all") {
			// Chuỗi từ PHP trả về chắc chắn là DD/MM/YYYY (VD: 20/04/2026)
			if (row.date && row.date.includes("/")) {
				const parts = row.date.split("/");
				// parts[0] là Ngày, parts[1] là Tháng, parts[2] là Năm
				const rowDate = new Date(parts[2], parts[1] - 1, parts[0]);

				if (!isNaN(rowDate.getTime())) {
					if (timeVal === "this_month") {
						matchTime =
							rowDate.getMonth() === now.getMonth() && rowDate.getFullYear() === now.getFullYear();
					} else if (timeVal === "7_days") {
						rowDate.setHours(0, 0, 0, 0);

						const pastStart = new Date(sevenDaysAgo);
						pastStart.setHours(0, 0, 0, 0);

						const todayEnd = new Date(now);
						todayEnd.setHours(23, 59, 59, 999);

						matchTime = rowDate >= pastStart && rowDate <= todayEnd;
					}
				} else {
					matchTime = false; // Bỏ qua nếu parse lỗi
				}
			} else {
				matchTime = false; // Bỏ qua nếu không có dấu '/'
			}
		}

		return matchSearch && matchTime;
	});
	if (isSortedByScore) {
		filteredData.sort((a, b) => b.total - a.total);
	}
	currentAttemptPage = 1;
	displayTablePage(currentAttemptPage);
}
function displayTablePage(page) {
	const startIndex = (page - 1) * rowsPerPage;
	const endIndex = startIndex + rowsPerPage;

	// LƯU Ý: Cắt từ mảng filteredData thay vì historyData
	const paginatedItems = filteredData.slice(startIndex, endIndex);

	const tbody = document.getElementById("historyTableBody");
	if (!tbody) return;
	tbody.innerHTML = "";

	// Xử lý UI khi không tìm thấy kết quả nào
	if (paginatedItems.length === 0) {
		tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
            <i class="fas fa-search fa-2x mb-3 text-light"></i><br>Không tìm thấy đề thi nào phù hợp.
        </td></tr>`;
		renderPagination();
		return;
	}

	paginatedItems.forEach((row) => {
		const lColor = getScoreColor(row.listening);
		const rColor = getScoreColor(row.reading);
		const tColor = getScoreColor(row.total, true);

		tbody.innerHTML += `
            <tr>
                <td class="ps-4">
    <div class="text-dark mb-1">${row.date}</div>
</td>
<td>
    <div class="exam-title">${row.exam_name || "Đề thi"}</div>
    <div class="exam-type">${row.exam_type || "TOEIC"}</div>
</td>
                <td class="text-center">
                    <span class="${lColor} fw-semibold">${row.listening}</span><span class="text-muted small">/495</span>
                </td>
                <td class="text-center">
                    <span class="${rColor} fw-semibold">${row.reading}</span><span class="text-muted small">/495</span>
                </td>
                <td class="text-center">
                    <span class="${tColor} fw-bold">${row.total}</span><span class="text-muted small">/990</span>
                </td>
                <td class="text-center text-dark fw-medium">${row.duration || "--"}</td>
<td class="text-center">
    <div class="d-flex justify-content-center align-items-center gap-1">
        <a href="results.php?uuid=${row.uuid}" class="btn btn-view rounded-2 text-nowrap">
            <i class="fas fa-external-link-alt me-1"></i>Xem chi tiết
        </a>
    </div>
</td>
            </tr>
        `;
	});

	renderPagination();
}

function renderPagination() {
	// Phân trang dựa trên số lượng của mảng filteredData
	const totalPages = Math.ceil(filteredData.length / rowsPerPage);
	const paginationContainer = document.getElementById("paginationContainer");
	if (!paginationContainer) return;

	paginationContainer.innerHTML = "";
	if (totalPages <= 1) return;

	const prevDisabled = currentAttemptPage === 1 ? "disabled" : "";
	const prevStyle = currentAttemptPage === 1 ? "bg-light text-secondary border-0" : "text-dark border";
	paginationContainer.innerHTML += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link rounded-2 me-1 ${prevStyle}" href="javascript:void(0)" onclick="changePage(${currentAttemptPage - 1})">Trước</a>
        </li>
    `;

	for (let i = 1; i <= totalPages; i++) {
		const isActive = currentAttemptPage === i;
		const activeClass = isActive ? "active bg-primary text-white border-primary" : "text-dark border";

		paginationContainer.innerHTML += `
            <li class="page-item ${isActive ? "active" : ""}">
                <a class="page-link rounded-2 ${activeClass}" href="javascript:void(0)" onclick="changePage(${i})">${i}</a>
            </li>
        `;
	}

	const nextDisabled = currentAttemptPage === totalPages ? "disabled" : "";
	const nextStyle = currentAttemptPage === totalPages ? "bg-light text-secondary border-0" : "text-primary border";
	paginationContainer.innerHTML += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link rounded-2 ms-1 ${nextStyle}" href="javascript:void(0)" onclick="changePage(${currentAttemptPage + 1})">Sau</a>
        </li>
    `;
}

function changePage(page) {
	const totalPages = Math.ceil(filteredData.length / rowsPerPage);
	if (page < 1 || page > totalPages) return;

	currentAttemptPage = page;
	displayTablePage(currentAttemptPage);
}
function sortData() {
	isSortedByScore = !isSortedByScore;
	const sortBtn = document.getElementById("filterBtn");

	if (isSortedByScore) {
		filteredData.sort((a, b) => b.total - a.total);
		sortBtn.innerHTML = '<i class="fas fa-times me-2"></i>Hủy sắp xếp';
		sortBtn.classList.add("text-danger");
	} else {
		applyFilters();
		sortBtn.innerHTML = '<i class="fas fa-sort-amount-down me-2"></i>Sắp xếp điểm';
		sortBtn.classList.remove("text-danger");
		return;
	}

	currentAttemptPage = 1;
	displayTablePage(currentAttemptPage);
}
