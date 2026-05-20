<section class="test-section container" id="tests">
	<div class="section-header">
		<div>
			<span class="section-badge">Đề thi mới</span>
			<h2 class="section-title">Cập nhật đề thi mới<br>nhất 2026.</h2>
		</div>
		<a href="tests.php" class="view-more-link">Xem thêm<i class='bx bx-chevron-right'></i></a>
	</div>

	<!-- skeleton hiện khi đang load -->
	<div class="row row-cols-1 row-cols-md-4 g-3" id="testSkeleton">
		<?php for ($i = 0; $i < 8; $i++): ?>
			<div class="col">
				<div class="test-card test-skeleton">
					<div class="sk-base sk-line sk-title"></div>
					<div class="sk-base sk-line sk-meta"></div>
					<div class="sk-tags">
						<div class="sk-base sk-tag"></div>
						<div class="sk-base sk-tag"></div>
					</div>
					<div class="sk-base sk-btn"></div>
				</div>
			</div>
		<?php endfor; ?>
	</div>

	<div class="row row-cols-1 row-cols-md-4 g-3 d-none" id="testContainer"></div>
	<div class="test-error d-none" id="testError">Không thể tải đề thi. <a href="#">Thử lại</a></div>
</section>

<script>
	(function () {
		const container = document.getElementById('testContainer');
		const skeleton = document.getElementById('testSkeleton');
		const errorBox = document.getElementById('testError');
		const LIMIT = 8;
		const REFRESH = 30000; // tự refresh sau 30s để bắt đề mới

		function formatDuration(seconds) {
			const m = Math.round(seconds / 60);
			return m >= 60 ? `${Math.floor(m / 60)} giờ ${m % 60 ? m % 60 + ' phút' : ''}`.trim() : `${m} phút`;
		}

		// sinh tags từ title
		function inferTags(title) {
			const t = title.toLowerCase();
			const tags = [];
			if (t.includes('full') || t.includes('practice')) tags.push('Full test');
			if (t.includes('listen')) tags.push('Listening');
			if (t.includes('read')) tags.push('Reading');
			if (t.includes('vocab')) tags.push('Vocab');
			if (tags.length === 0) tags.push('TOEIC');
			return tags;
		}

		// kiểm tra đề có mới trong 7 ngày không
		function isNew(createdAt) {
			return (Date.now() - new Date(createdAt).getTime()) < 7 * 86400000;
		}

		function buildCard(test) {
			const tags = inferTags(test.title);
			const dur = formatDuration(test.duration || 2700);
			const qCount = test.total_questions || '-';
			const newBadge = isNew(test.created_at) ? '<span class="test-new-badge">Mới</span>' : '';
			const premTag = test.is_premium ? '<span class="test-tag test-tag-premium"><i class="bx bx-lock"></i> Premium</span>' : '';
			const btnClass = test.is_unlocked ? 'test-btn featured' : 'test-btn';
			const btnLabel = test.is_unlocked ? 'Làm ngay' : (test.is_premium ? 'Mở khóa' : 'Chi tiết');
			// premium chưa unlock → về pricing, còn lại → vào exam
			const href = (!test.is_unlocked && test.is_premium)
				? 'pricing.php'
				: `exam.php?id=${encodeURIComponent(test.uuid)}`;

			return `<div class="col">
			<div class="test-card">
				<div class="test-card-top">
					<h5>${escHtml(test.title)}</h5>
					<p class="test-meta">${qCount} câu hỏi · ${dur}</p>
				</div>
				<div class="test-tags">${tags.map(t => `<span class="test-tag">#${t}</span>`).join('')}${premTag}${newBadge}</div>
				<a href="${href}" class="${btnClass}">${btnLabel}</a>
			</div>
		</div>`;
		}

		function escHtml(s) {
			return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}

		async function loadTests() {
			try {
				const res = await fetch('/api/tests');
				const json = await res.json();

				if (!json.success || !Array.isArray(json.data)) throw new Error();

				// chỉ lấy 8 đề mới nhất (API đã ORDER BY id DESC)
				const tests = json.data.filter(t => t.is_active).slice(0, LIMIT);

				skeleton.classList.add('d-none');
				errorBox.classList.add('d-none');

				container.innerHTML = tests.map(buildCard).join('');
				container.classList.remove('d-none');
			} catch {
				skeleton.classList.add('d-none');
				errorBox.classList.remove('d-none');
			}
		}

		loadTests();
		setInterval(loadTests, REFRESH); // tự reload để bắt đề mới

		// retry khi click thử lại
		document.getElementById('testError').addEventListener('click', e => {
			if (e.target.tagName === 'A') { e.preventDefault(); loadTests(); }
		});
	})();
<<<<<<< HEAD
</script>
=======
</script>
>>>>>>> 404a8ab9cf94f9753e090ad4fe9eee6b1323c4fe
