// trạng thái toàn cục
let allTests = [];
let usersState = { page: 1, limit: 10, total: 0, search: '', role: '', status: '' };
let attemptsState = { page: 1, limit: 10, total: 0 };
let transactionsState = { page: 1, limit: 10, total: 0 };
let loadedSections = new Set();
let revenueChartInstance = null;
const revenueTarget = 2000000; // doanh thu mục tiêu 2 triệu VND cho thanh tiến trình sidebar

// định dạng tiền tệ sang VND
function formatVND(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

// định dạng ngày tháng sang DD/MM/YYYY
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    // sửa lại đúng định dạng giờ từ database
    let normalized = dateString;
    if (typeof normalized === 'string' && !normalized.endsWith('Z') && !normalized.includes('+') && !normalized.includes('GMT')) {
        normalized = normalized.replace(' ', 'T');
    }
    const date = new Date(normalized);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// định dạng ngày giờ sang DD/MM/YYYY HH:MM:SS
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    // sửa lại đúng định dạng giờ từ database
    let normalized = dateString;
    if (typeof normalized === 'string' && !normalized.endsWith('Z') && !normalized.includes('+') && !normalized.includes('GMT')) {
        normalized = normalized.replace(' ', 'T');
    }
    const date = new Date(normalized);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
}

// định dạng giây sang MM:SS
function formatTimeSpent(seconds) {
    if (!seconds) return '00:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

// tải dữ liệu khi tab được kích hoạt
async function loadSectionData(section) {
    if (loadedSections.has(section)) return;

    if (section === 'overview') {
        await loadOverviewStats();
    } else if (section === 'tests') {
        const hasForm = document.getElementById('questions-container') !== null;
        if (!hasForm) {
            await loadTestsList();
        }
    } else if (section === 'users') {
        await loadUsersList(1);
    } else if (section === 'attempts') {
        await loadAttemptsList(1);
    } else if (section === 'revenue') {
        await loadRevenueData();
        await loadTransactionsList(1);
    }

    loadedSections.add(section);
}

// cập nhật tiến trình mục tiêu ở sidebar
function updateSidebarWidget(currentMonthRevenue) {
    const revenueValEl = document.getElementById('widget-revenue-val');
    const revenueProgressEl = document.getElementById('widget-revenue-progress');
    
    if (revenueValEl && revenueProgressEl) {
        const percent = Math.min(100, Math.round((currentMonthRevenue / revenueTarget) * 100));
        revenueValEl.textContent = `${formatVND(currentMonthRevenue)} / ${formatVND(revenueTarget)}`;
        revenueProgressEl.style.width = `${percent}%`;
        
        // thay đổi màu sắc tiến trình dựa trên tỉ lệ đạt được
        revenueProgressEl.className = 'progress-bar';
        if (percent >= 100) {
            revenueProgressEl.classList.add('green');
        } else if (percent >= 50) {
            revenueProgressEl.classList.add('orange');
        } else {
            revenueProgressEl.classList.add('red');
        }
    }
}

// hiển thị cấu phần phân trang
function renderPagination(containerId, pagination, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const { page, limit, total } = pagination;
    const totalPages = Math.ceil(total / limit);

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = `
        <div class="pagination-info">
            Hiển thị từ ${(page - 1) * limit + 1} đến ${Math.min(page * limit, total)} trong tổng số ${total} bản ghi
        </div>
        <div class="pagination-buttons">
            <button class="page-btn" ${page === 1 ? 'disabled' : ''} data-page="${page - 1}">
                <i class="bx bx-chevron-left"></i>
            </button>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
            html += `<button class="page-btn ${i === page ? 'active' : ''}" data-page="${i}">${i}</button>`;
        } else if (i === page - 3 || i === page + 3) {
            html += `<span style="padding: 0 4px; display: inline-flex; align-items: center; color: var(--text-secondary);">...</span>`;
        }
    }

    html += `
            <button class="page-btn" ${page === totalPages ? 'disabled' : ''} data-page="${page + 1}">
                <i class="bx bx-chevron-right"></i>
            </button>
        </div>
    `;

    container.innerHTML = html;

    // gắn sự kiện chuyển trang
    container.querySelectorAll('.page-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const btnTarget = e.currentTarget;
            if (btnTarget.hasAttribute('disabled')) return;
            const targetPage = parseInt(btnTarget.dataset.page);
            onPageChange(targetPage);
        });
    });
}

// logic chuyển tab
function setupTabSwitching() {
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    const sections = document.querySelectorAll('.section-content');

    sidebarItems.forEach(item => {
        item.addEventListener('click', async () => {
            const sectionName = item.dataset.section;
            if (!sectionName) return;

            // không chuyển trang nếu đang soạn đề để tránh mất dữ liệu
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            if (action && sectionName !== 'tests') {
                const confirmed = await showConfirmModal('Chuyển trang', 'Các thay đổi chưa lưu trên đề thi sẽ bị mất, bạn có chắc chắn muốn chuyển trang?', 'warning', 'Chuyển trang');
                if (!confirmed) {
                    return;
                }
            }

            // cập nhật đường dẫn URL
            history.pushState(null, '', `admin.php?section=${sectionName}`);

            // chuyển đổi trạng thái active
            sidebarItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            sections.forEach(s => s.classList.remove('active'));
            const targetSection = document.getElementById(`section-${sectionName}`);
            if (targetSection) {
                targetSection.classList.add('active');
            }

            // tải dữ liệu cho tab
            loadSectionData(sectionName);
        });
    });
}

// khởi tạo trạng thái tài liệu
document.addEventListener("DOMContentLoaded", () => {
    setupTabSwitching();

    // phân tích tab mặc định từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialSection = urlParams.get('section') || 'overview';
    
    // tải dữ liệu cho tab mặc định
    loadSectionData(initialSection);

    // cập nhật doanh số mục tiêu ở sidebar bằng dữ liệu thực tế ngay khi tải trang
    fetch('/api/admin/revenue')
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                updateSidebarWidget(result.data.current_month);
            }
        })
        .catch(err => console.error('error loading target widget:', err));
});

// hiển thị cửa sổ xác nhận tùy chỉnh
function showConfirmModal(title, message, type = 'warning', confirmText = 'Xác nhận', cancelText = 'Hủy') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'custom-modal-overlay';
        
        let iconClass = 'bx-help-circle';
        let headerClass = 'warning-header';
        let confirmBtnClass = 'modal-btn-confirm';
        
        if (type === 'danger') {
            iconClass = 'bx-x-circle';
            headerClass = 'danger-header';
            confirmBtnClass = 'modal-btn-confirm-danger';
        }
        
        modal.innerHTML = `
            <div class="custom-modal-box">
                <div class="custom-modal-header ${headerClass}">
                    <h2>${title}</h2>
                </div>
                <div class="custom-modal-body">
                    <p>${message}</p>
                </div>
                <div class="custom-modal-footer">
                    <button class="modal-btn-cancel">${cancelText}</button>
                    <button class="${confirmBtnClass}">${confirmText}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        const close = (result) => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
                resolve(result);
            }, 250);
        };
        
        modal.querySelector(`.${confirmBtnClass}`).addEventListener('click', () => close(true));
        modal.querySelector('.modal-btn-cancel').addEventListener('click', () => close(false));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) close(false);
        });
    });
}

// hiển thị thông báo tùy chỉnh
function showAlertModal(title, message, type = 'info', buttonText = 'Đóng') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'custom-modal-overlay';
        
        let iconClass = 'bx-info-circle';
        let headerClass = 'info-header';
        
        if (type === 'error') {
            iconClass = 'bx-x-circle';
            headerClass = 'danger-header';
        } else if (type === 'success') {
            iconClass = 'bx-check-circle';
            headerClass = 'success-header';
        }
        
        modal.innerHTML = `
            <div class="custom-modal-box">
                <div class="custom-modal-header ${headerClass}">
                    <i class="bx ${iconClass} header-icon"></i>
                    <h2>${title}</h2>
                </div>
                <div class="custom-modal-body">
                    <p>${message}</p>
                </div>
                <div class="custom-modal-footer">
                    <button class="modal-btn-confirm">${buttonText}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        const close = () => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
                resolve();
            }, 250);
        };
        
        modal.querySelector('.modal-btn-confirm').addEventListener('click', close);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) close();
        });
    });
}
