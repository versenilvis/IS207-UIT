// tải danh sách lượt làm bài phân trang
async function loadAttemptsList(page) {
    attemptsState.page = page;
    try {
        const response = await fetch(`/api/admin/attempts?page=${page}&limit=${attemptsState.limit}`);
        const result = await response.json();
        if (result.success) {
            renderAttemptsTable(result.data);
            attemptsState.total = result.pagination.total;
            renderPagination('attempts-pagination', result.pagination, loadAttemptsList);
        }
    } catch (error) {
        console.error('error loading attempts:', error);
    }
}

// hiển thị danh sách lượt làm bài
function renderAttemptsTable(attempts) {
    const tbody = document.getElementById('attemptTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (attempts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không tìm thấy bài thi thử nào</td></tr>';
        return;
    }

    attempts.forEach(attempt => {
        const isPremium = parseInt(attempt.is_premium) === 1 || attempt.premium_plan !== null;
        const correctText = `L: ${attempt.listening_correct} | R: ${attempt.reading_correct}`;
        const scoreText = `L: ${attempt.listening_score} | R: ${attempt.reading_score} | T: ${attempt.total_score}`;
        
        // tính toán tỷ lệ tiến trình làm bài
        const attempted = parseInt(attempt.user_tests_attempted) || 0;
        const total = parseInt(attempt.total_active_tests) || 1;
        const percentage = Math.min(100, Math.round((attempted / total) * 100));

        // màu tiến trình nhỏ trong cột bảng
        let progressClass = 'red';
        if (percentage >= 80) progressClass = 'green';
        else if (percentage >= 40) progressClass = 'orange';

        let avatarHtml = '';
        if (attempt.avatar) {
            avatarHtml = `<img src="${attempt.avatar}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border-color);">`;
        } else {
            avatarHtml = `
                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 13px; display: flex; align-items: center; justify-content: center; background-color: var(--accent-blue); color: #ffffff; border-radius: 50%; font-weight: 600; flex-shrink: 0; border: 1px solid var(--border-color);">
                    ${attempt.first_name[0] || 'U'}
                </div>`;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    ${avatarHtml}
                    <div style="max-width: 180px; overflow: hidden;">
                        <div style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${attempt.first_name} ${attempt.last_name}</div>
                        <div style="font-size: 11px; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${attempt.email}</div>
                        ${isPremium ? '<span class="badge success" style="font-size: 9px; padding: 2px 4px; margin-top: 4px;">Pro</span>' : ''}
                    </div>
                </div>
            </td>
            <td><strong style="display: inline-block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle;">${attempt.title}</strong></td>
            <td>${correctText}</td>
            <td><strong style="color: var(--accent-blue);">${attempt.total_score}</strong> <span style="font-size: 11px; color: var(--text-secondary);">(${scoreText})</span></td>
            <td>${formatTimeSpent(attempt.time_spent)}</td>
            <td style="min-width: 140px;">
                <div>
                    <div class="progress-bar-container" style="height: 4px;">
                        <div class="progress-bar ${progressClass}" style="width: ${percentage}%;"></div>
                    </div>
                    <span style="font-size: 10.5px; color: var(--text-secondary);">${attempted}/${total} đề (${percentage}%)</span>
                </div>
            </td>
            <td>${formatDateTime(attempt.created_at)}</td>
        `;
        tbody.appendChild(row);
    });
}
