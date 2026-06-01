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
	<div class="config-row" style="margin-top: 12px;">
		<div class="config-group" style="flex: 0 0 auto; max-width: 350px;">
			<label><i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em; margin-right: 4px;"></i>Âm thanh bài thi</label>
			<input type="file" accept="audio/*" id="testAudioFile" onchange="previewTestAudio(this)">
			<small style="color: #666; display: block; margin-top: 4px;">MP3, WAV, OGG (tuỳ chọn)</small>
			<div id="testAudioPreview" style="margin-top: 8px;"></div>
		</div>
		<div class="config-group" style="display: flex; align-items: center; padding-top: 24px;">
			<button type="button" id="saveTestAudioBtn" onclick="saveTestAudio()" style="margin-top: 5px; padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; white-space: nowrap;">
				<i class="bx bx-save" style="vertical-align: -0.125em; margin-right: 4px;"></i>Lưu Audio
			</button>
		</div>
	</div>
</div>

<div id="messageBox" class="message-box"></div>
<div id="partInfo" class="part-info"></div>
