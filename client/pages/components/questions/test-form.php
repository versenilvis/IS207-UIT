<div class="form-section">
	<h3 style="margin-top: 0; font-size: 20px;">Tạo Bài Thi Mới</h3>
	<form id="createTestForm">
		<div class="form-grid">
			<div class="form-group">
				<label>Tiêu đề <span class="required-mark">*</span></label>
				<input type="text" name="title" required placeholder="Nhập tiêu đề...">
			</div>
			<div class="form-group full-width">
				<label>Mô tả</label>
				<textarea name="description" placeholder="Nhập mô tả chi tiết..."></textarea>
			</div>
		</div>
		<div class="form-actions">
			<div class="checkbox-group-wrapper">
				<div class="checkbox-group">
					<input type="checkbox" name="is_premium" value="1">
					<label>Premium</label>
				</div>
				<div class="checkbox-group">
					<input type="checkbox" name="is_active" value="1" checked>
					<label>Kích hoạt</label>
				</div>
			</div>
			<button type="submit" class="btn-submit">Thêm Bài Thi</button>
		</div>
	</form>
</div>
