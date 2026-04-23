<div class="test-config">
	<h3 style="margin-top: 0; color: #333;">Cấu Hình Đề Thi & Câu Hỏi</h3>
	<div class="config-row">
		<div class="config-group">
			<label>Đề Thi <span class="required">*</span></label>
			<select id="testSelect" onchange="onTestChange()" required>
				<option value="">-- Chọn đề thi --</option>
			</select>
		</div>
		<div class="config-group">
			<label>Phần (Part) <span class="required">*</span></label>
			<select id="partSelect" onchange="onPartChange()" required>
				<option value="">-- Chọn part --</option>
				<option value="1">Part 1: Ảnh</option>
				<option value="2">Part 2: Câu hỏi ngắn</option>
				<option value="3">Part 3: Hội thoại</option>
				<option value="4">Part 4: Độc thoại</option>
				<option value="5">Part 5: Đọc câu hoàn chỉnh</option>
				<option value="6">Part 6: Điền từ</option>
				<option value="7">Part 7: Đọc hiểu</option>
			</select>
		</div>
	</div>
</div>

<div id="messageBox" class="message-box"></div>
<div id="partInfo" class="part-info"></div>
