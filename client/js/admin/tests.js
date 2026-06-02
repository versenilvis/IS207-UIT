// tải danh sách đề thi
async function loadTestsList() {
    try {
        const response = await fetch('/api/tests');
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        
        if (result.success && result.data && Array.isArray(result.data)) {
            allTests = result.data;
            filterTests();
        }
    } catch (error) {
        console.error('error loading tests:', error);
    }
}

// lọc danh sách đề thi tại client
function filterTests() {
    const searchInput = document.getElementById('search-tests');
    const premiumFilter = document.getElementById('filter-tests-premium');
    const statusFilter = document.getElementById('filter-tests-status');
    if (!searchInput) return;

    const searchText = searchInput.value.toLowerCase().trim();
    const premiumValue = premiumFilter.value;
    const statusValue = statusFilter.value;

    const filtered = allTests.filter(test => {
        const titleMatch = test.title.toLowerCase().includes(searchText);
        
        let premiumMatch = true;
        if (premiumValue === 'Premium') {
            premiumMatch = test.is_premium === true || parseInt(test.is_premium) === 1;
        } else if (premiumValue === 'Thường') {
            premiumMatch = test.is_premium === false || parseInt(test.is_premium) === 0;
        }

        let statusMatch = true;
        if (statusValue === 'Hoạt động') {
            statusMatch = parseInt(test.is_active) === 1;
        } else if (statusValue === 'Tạm ẩn') {
            statusMatch = parseInt(test.is_active) === 0;
        }

        return titleMatch && premiumMatch && statusMatch;
    });

    renderTestsTable(filtered);
}

// hiển thị danh sách đề thi
function renderTestsTable(tests) {
    const tbody = document.getElementById('testTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (tests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không tìm thấy bài thi nào</td></tr>';
        return;
    }

    tests.forEach(test => {
        const isPremium = test.is_premium === true || parseInt(test.is_premium) === 1;
        const isActive = parseInt(test.is_active) === 1;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${test.title}</strong></td>
            <td><span class="badge ${isPremium ? 'premium' : 'standard'}">${isPremium ? 'Premium' : 'Thường'}</span></td>
            <td><span class="badge ${isActive ? 'active' : 'warning'}">${isActive ? 'Hoạt động' : 'Chờ duyệt'}</span></td>
            <td>${formatDate(test.created_at)}</td>
            <td style="text-align: right;">
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    ${!isActive ? `
                        <button class="btn-primary approve-test-btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--accent-green);" data-uuid="${test.uuid}">
                            Duyệt
                        </button>
                    ` : ''}
                    <a href="admin.php?section=tests&action=edit&test_id=${test.uuid}" class="btn-primary" style="padding: 6px 12px; font-size: 12px;">
                        <i class="bx bx-edit-alt"></i> Câu hỏi
                    </a>
                    <button class="btn-primary edit-info-btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--accent-orange);" 
                            data-uuid="${test.uuid}" data-title="${test.title}" data-premium="${isPremium ? '1' : '0'}" data-active="${isActive ? '1' : '0'}">
                        Thông tin
                    </button>
                    <button class="btn-danger delete-test-btn" style="padding: 6px 12px; font-size: 12px;" data-uuid="${test.uuid}">
                        Xóa
                    </button>
                </div>
            </td>
        `;

        // kích đúp để biên soạn câu hỏi
        row.addEventListener('dblclick', () => {
            window.location.href = `admin.php?section=tests&action=edit&test_id=${test.uuid}`;
        });

        tbody.appendChild(row);
    });

    // gắn sự kiện cho các nút hành động
    document.querySelectorAll('.edit-info-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const data = e.target.dataset;
            openEditModal(data.uuid, data.title, data.premium === '1', data.active === '1');
        });
    });

    document.querySelectorAll('.delete-test-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const uuid = e.target.dataset.uuid;
            deleteTest(uuid);
        });
    });

    document.querySelectorAll('.approve-test-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const uuid = e.target.dataset.uuid;
            approveTest(uuid);
        });
    });
}

// phê duyệt và kích hoạt đề thi
async function approveTest(uuid) {
    if (!confirm('Bạn có chắc chắn muốn duyệt và kích hoạt đề thi này?')) return;
    try {
        const response = await fetch(`/api/admin/tests/${uuid}/activate`, {
            method: 'PUT'
        });
        const result = await response.json();
        if (result.success) {
            alert('Duyệt đề thi thành công');
            loadTestsList();
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('error approving test:', error);
        alert('Có lỗi xảy ra khi phê duyệt đề');
    }
}

// mở modal chỉnh sửa nhanh
function openEditModal(uuid, title, isPremium, isActive) {
    const modal = document.getElementById('editModal');
    document.getElementById('edit_id').value = uuid;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_premium').checked = isPremium;
    document.getElementById('edit_active').checked = isActive;
    modal.classList.add('show');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.classList.remove('show');
}

// xóa đề thi bằng uuid
async function deleteTest(uuid) {
    if (confirm("Bạn có chắc chắn muốn xóa bài thi này không? Toàn bộ câu hỏi liên quan sẽ bị xóa vĩnh viễn.")) {
        try {
            const response = await fetch(`/api/tests/${uuid}`, { method: 'DELETE' });
            const result = await response.json();
            if (result.success) {
                alert("Đã xóa bài thi thành công");
                await loadTestsList();
            } else {
                alert("Lỗi: " + result.message);
            }
        } catch (error) {
            console.error('error deleting test:', error);
            alert("Lỗi khi gửi yêu cầu xóa bài thi");
        }
    }
}

// thiết lập sự kiện khi DOM sẵn sàng
document.addEventListener("DOMContentLoaded", () => {
    // thiết lập đóng modal nếu đang ở danh sách đề thi
    const editForm = document.getElementById('editForm');
    if (editForm) {
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const modal = document.getElementById('editModal');

        const closeModal = () => closeEditModal();
        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        // gửi form chỉnh sửa đề thi
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const uuid = document.getElementById('edit_id').value;
            const title = document.getElementById('edit_title').value;
            const isPremium = document.getElementById('edit_premium').checked ? 1 : 0;
            const isActive = document.getElementById('edit_active').checked ? 1 : 0;

            try {
                const response = await fetch(`/api/tests/${uuid}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: title,
                        is_premium: isPremium,
                        is_active: isActive
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert("Đã lưu thông tin đề thi thành công");
                    closeEditModal();
                    await loadTestsList();
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                console.error('error updating test metadata:', error);
            }
        });
    }

    // tìm kiếm và lọc đề thi
    const searchTests = document.getElementById('search-tests');
    if (searchTests) {
        searchTests.addEventListener('input', filterTests);
        document.getElementById('filter-tests-premium').addEventListener('change', filterTests);
        document.getElementById('filter-tests-status').addEventListener('change', filterTests);
    }

    // thiết lập modal import đề thi
    const openImportModalBtn = document.getElementById('openImportModalBtn');
    const importModal = document.getElementById('importModal');
    const closeImportModalBtn = document.getElementById('closeImportModalBtn');
    const cancelImportModalBtn = document.getElementById('cancelImportModalBtn');
    const importForm = document.getElementById('importForm');
    const importLoading = document.getElementById('importLoading');
    const importMessage = document.getElementById('importMessage');

    const importRadios = document.querySelectorAll('input[name="import_type"]');
    const singleFilesGroup = document.getElementById('single-files-group');
    const splitFilesGroup = document.getElementById('split-files-group');
    const importExamFile = document.getElementById('import_exam_file');
    const importAnswerFile = document.getElementById('import_answer_file');
    const importListeningFile = document.getElementById('import_listening_file');
    const importListeningAnswerFile = document.getElementById('import_listening_answer_file');
    const importReadingFile = document.getElementById('import_reading_file');
    const importReadingAnswerFile = document.getElementById('import_reading_answer_file');

    const handleImportTypeChange = () => {
        const selectedType = document.querySelector('input[name="import_type"]:checked')?.value || 'single';
        if (selectedType === 'single') {
            if (singleFilesGroup) singleFilesGroup.style.display = 'block';
            if (splitFilesGroup) splitFilesGroup.style.display = 'none';
            if (importExamFile) importExamFile.required = true;
            if (importAnswerFile) importAnswerFile.required = true;
            if (importListeningFile) importListeningFile.required = false;
            if (importListeningAnswerFile) importListeningAnswerFile.required = false;
            if (importReadingFile) importReadingFile.required = false;
            if (importReadingAnswerFile) importReadingAnswerFile.required = false;
        } else {
            if (singleFilesGroup) singleFilesGroup.style.display = 'none';
            if (splitFilesGroup) splitFilesGroup.style.display = 'block';
            if (importExamFile) importExamFile.required = false;
            if (importAnswerFile) importAnswerFile.required = false;
            if (importListeningFile) importListeningFile.required = true;
            if (importListeningAnswerFile) importListeningAnswerFile.required = true;
            if (importReadingFile) importReadingFile.required = true;
            if (importReadingAnswerFile) importReadingAnswerFile.required = true;
        }
    };

    if (importRadios.length > 0) {
        importRadios.forEach(radio => radio.addEventListener('change', handleImportTypeChange));
    }

    const openImportModal = () => {
        if (importModal) {
            importModal.classList.add('show');
            if (importForm) importForm.reset();
            handleImportTypeChange();
            if (importMessage) {
                importMessage.style.display = 'none';
                importMessage.className = 'import-message-box';
                importMessage.textContent = '';
            }
            if (importLoading) importLoading.style.display = 'none';
        }
    };

    const closeImportModal = () => {
        if (importModal) importModal.classList.remove('show');
    };

    if (openImportModalBtn) openImportModalBtn.addEventListener('click', openImportModal);
    if (closeImportModalBtn) closeImportModalBtn.addEventListener('click', closeImportModal);
    if (cancelImportModalBtn) cancelImportModalBtn.addEventListener('click', closeImportModal);
    if (importModal) {
        importModal.addEventListener('click', (e) => {
            if (e.target === importModal) closeImportModal();
        });
    }

    if (importForm) {
        importForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (importLoading) importLoading.style.display = 'block';
            if (importMessage) importMessage.style.display = 'none';

            const formData = new FormData(importForm);

            try {
                const response = await fetch('/api/admin/import', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (importLoading) importLoading.style.display = 'none';
                
                if (importMessage) {
                    importMessage.style.display = 'block';
                    if (result.success) {
                        importMessage.className = 'import-message-box success';
                        importMessage.textContent = result.message || 'Import đề thi thành công';
                        importForm.reset();
                        await loadTestsList();
                        // đóng modal sau 2 giây thành công
                        setTimeout(closeImportModal, 2000);
                    } else {
                        importMessage.className = 'import-message-box error';
                        importMessage.textContent = result.message || 'Có lỗi xảy ra khi import đề thi';
                    }
                }
            } catch (error) {
                console.error('error importing exam:', error);
                if (importLoading) importLoading.style.display = 'none';
                if (importMessage) {
                    importMessage.style.display = 'block';
                    importMessage.className = 'import-message-box error';
                    importMessage.textContent = 'Lỗi kết nối máy chủ hoặc quá tải dung lượng file';
                }
            }
        });
    }
});
