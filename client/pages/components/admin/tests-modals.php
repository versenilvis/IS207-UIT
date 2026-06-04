<!-- các modal quản lý đề thi -->
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
                <div class="form-group" style="margin-top: 12px;">
                    <label>Mô tả đề thi</label>
                    <textarea id="edit_description" rows="3" style="width: 100%; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 12px; font-family: inherit; font-size: 13px; resize: vertical; box-sizing: border-box;"></textarea>
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

                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Tên đề thi (tùy chọn, để trống sẽ tự động lấy từ file đề)</label>
                    <input type="text" name="title" placeholder="Ví dụ: ETS 2024 Test 1">
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Mô tả đề thi (tùy chọn)</label>
                    <textarea name="desc" rows="3" placeholder="Nhập mô tả đề thi..." style="width: 100%; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 12px; font-family: inherit; font-size: 13px; resize: vertical; box-sizing: border-box;"></textarea>
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
                <div class="checkbox-group-wrapper" style="margin-top: 16px;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="import_premium" name="is_premium" value="1">
                        <label for="import_premium">Đặt là đề thi Premium</label>
                    </div>
                </div>
                <div id="importLoading" style="display: none; text-align: center; margin-top: 15px; color: var(--accent-blue); font-weight: 600;">
                    <i class="bx bx-loader-alt bx-spin" style="margin-right: 5px;"></i> Đang import đề thi, vui lòng đợi...
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
