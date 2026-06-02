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
                <h1 class="page-title"><?php echo $action === 'create' ? 'Tạo Đề Thi Mới' : 'Biên Soạn Câu Hỏi'; ?></h1>
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
                        <th style="text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="testTableBody">
                    <!-- hiển thị bằng JS -->
                </tbody>
            </table>
        </div>

        <!-- modal sửa thông tin đề thi -->
        <div class="modal-overlay" id="editModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Chỉnh Sửa Đề Thi</h3>
                    <button class="close-btn" id="closeModalBtn">&times;</button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_id">
                        <div class="form-group">
                            <label>Tiêu đề</label>
                            <input type="text" id="edit_title" required>
                        </div>
                        <div class="checkbox-group-wrapper">
                            <div class="checkbox-group">
                                <input type="checkbox" id="edit_premium">
                                <label for="edit_premium">Premium</label>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="edit_active">
                                <label for="edit_active">Hoạt động</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-danger" id="btnDelete" style="margin-right: auto;">Xóa</button>
                        <button type="button" class="btn-primary" style="background-color: var(--text-secondary);" id="cancelModalBtn">Hủy</button>
                        <button type="submit" class="btn-primary">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- modal import đề thi từ html -->
        <div class="modal-overlay" id="importModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Import Đề Thi từ HTML</h3>
                    <button class="close-btn" id="closeImportModalBtn">&times;</button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 8px;">Loại hình đề thi</label>
                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                <label style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; margin-bottom: 0;">
                                    <input type="radio" name="import_type" value="single" checked style="width: auto; height: auto; margin: 0;">
                                    <span>Đề Đơn (Nghe, Đọc hoặc đề gộp sẵn)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; margin-bottom: 0;">
                                    <input type="radio" name="import_type" value="split" style="width: auto; height: auto; margin: 0;">
                                    <span>Đề Ghép (Listening + Reading tách rời)</span>
                                </label>
                            </div>
                        </div>

                        <div id="single-files-group">
                            <div class="form-group">
                                <label>File HTML đề thi <span style="color: var(--accent-red);">*</span></label>
                                <input type="file" id="import_exam_file" name="exam_file" accept=".html" required>
                                <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đề thi lưu dạng "Web Page, Complete" (.html)</small>
                            </div>
                            <div class="form-group" style="margin-top: 12px;">
                                <label>File HTML đáp án <span style="color: var(--accent-red);">*</span></label>
                                <input type="file" id="import_answer_file" name="answer_file" accept=".html" required>
                                <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đáp án tương ứng lưu dạng .html</small>
                            </div>
                        </div>

                        <div id="split-files-group" style="display: none;">
                            <div class="form-group">
                                <label>File HTML Listening đề thi <span style="color: var(--accent-red);">*</span></label>
                                <input type="file" id="import_listening_file" name="listening_file" accept=".html">
                                <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đề thi Listening lưu dạng .html</small>
                            </div>
                            <div class="form-group" style="margin-top: 12px;">
                                <label>File HTML Listening đáp án <span style="color: var(--accent-red);">*</span></label>
                                <input type="file" id="import_listening_answer_file" name="listening_answer_file" accept=".html">
                                <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đáp án Listening lưu dạng .html</small>
                            </div>
                            <div class="form-group" style="margin-top: 12px;">
                                <label>File HTML Reading đề thi <span style="color: var(--accent-red);">*</span></label>
                                <input type="file" id="import_reading_file" name="reading_file" accept=".html">
                                <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đề thi Reading lưu dạng .html</small>
                            </div>
                            <div class="form-group" style="margin-top: 12px;">
                                <label>File HTML Reading đáp án <span style="color: var(--accent-red);">*</span></label>
                                <input type="file" id="import_reading_answer_file" name="reading_answer_file" accept=".html">
                                <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp đáp án Reading lưu dạng .html</small>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 12px;">
                            <label>File ZIP hình ảnh / âm thanh đề thi (không bắt buộc)</label>
                            <input type="file" id="import_media_file" name="media_file" accept=".zip">
                            <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp nén .zip chứa thư mục media của trang đề thi</small>
                        </div>
                        <div class="form-group" style="margin-top: 12px;">
                            <label>File ZIP hình ảnh / âm thanh đáp án (không bắt buộc)</label>
                            <input type="file" id="import_media_answer_file" name="media_answer_file" accept=".zip">
                            <small style="color: var(--text-secondary); margin-top: 4px; font-size: 11px;">Tệp nén .zip chứa thư mục media của trang đáp án</small>
                        </div>
                        <div class="checkbox-group-wrapper" style="margin-top: 16px;">
                            <div class="checkbox-group">
                                <input type="checkbox" id="import_premium" name="is_premium" value="1">
                                <label for="import_premium">Đặt là đề thi Premium</label>
                            </div>
                        </div>
                        <div id="importLoading" style="display: none; text-align: center; margin-top: 15px; color: var(--accent-blue); font-weight: 600;">
                            <i class="bx bx-loader-alt bx-spin" style="margin-right: 5px;"></i> Đang giải nén & import đề thi, vui lòng đợi...
                        </div>
                        <div id="importMessage" style="display: none; margin-top: 15px; padding: 10px; border-radius: 6px; font-size: 13px;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-primary" style="background-color: var(--text-secondary);" id="cancelImportModalBtn">Hủy</button>
                        <button type="submit" class="btn-primary" id="btnSubmitImport">Xử lý & Import</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
