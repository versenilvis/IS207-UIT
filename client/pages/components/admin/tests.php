<!-- phân hệ: đề thi -->
<section id="section-tests" class="section-content <?php echo $section === 'tests' ? 'active' : ''; ?>">
    <?php if ($action === 'create' || $action === 'edit'): ?>
        <div class="page-header">
            <div class="page-title-container">
                <div class="breadcrumbs">
                    <span>Quản lý đề thi</span>
                    <i class="bx bx-chevron-right"></i>
                    <span><?php echo $action === 'create' ? 'Tạo mới' : 'Biên soạn'; ?></span>
                </div>
                <h1 class="page-title"><?php echo $action === 'create' ? 'Tạo Đề Thi Mới' : 'Chỉnh Sửa Đề Thi & Đáp Án'; ?></h1>
            </div>
            <a href="admin.php?section=tests" class="btn-primary" style="background-color: var(--text-secondary);">
                <i class="bx bx-chevron-left"></i> Quay lại danh sách
            </a>
        </div>

        <div class="container-wrapper">
            <!-- form tạo đề thi -->
            <?php include('./components/questions/test-form.php'); ?>

            <!-- cấu hình đề và phần thi -->
            <?php include('./components/questions/test-config.php'); ?>

            <!-- các nút hành động -->
            <?php include('./components/questions/action-buttons.php'); ?>

            <!-- vùng chứa danh sách câu hỏi -->
            <div id="questions-container"></div>
        </div>

        <!-- các mẫu câu hỏi ẩn -->
        <?php include('./components/questions/question-templates.php'); ?>
    <?php else: ?>
        <div class="page-header">
            <div class="page-title-container">
                <div class="breadcrumbs">
                    <span>Quản lý</span>
                    <i class="bx bx-chevron-right"></i>
                    <span>Đề thi</span>
                </div>
                <h1 class="page-title">Danh Sách Đề Thi</h1>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn-primary" style="background-color: var(--accent-orange); cursor: pointer;" id="openImportModalBtn">
                    <i class="bx bx-upload"></i> Import Đề từ HTML
                </button>
            </div>
        </div>

        <div class="filter-toolbar">
            <input type="text" id="search-tests" class="search-input" placeholder="Tìm kiếm theo tiêu đề...">
            <select id="filter-tests-premium" class="select-filter">
                <option value="">Tất cả phân loại</option>
                <option value="Premium">Premium</option>
                <option value="Thường">Thường</option>
            </select>
            <select id="filter-tests-status" class="select-filter">
                <option value="">Tất cả trạng thái</option>
                <option value="Hoạt động">Hoạt động</option>
                <option value="Tạm ẩn">Tạm ẩn</option>
            </select>
        </div>

        <div class="table-container" style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Phân loại</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th style="text-align: right; min-width: 200px;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="testTableBody">
                    <!-- hiển thị bằng JS -->
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper" id="tests-pagination"></div>

    <?php endif; ?>
</section>
