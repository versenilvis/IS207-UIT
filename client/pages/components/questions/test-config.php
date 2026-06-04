<div class="test-config <?php echo $action === 'edit' ? 'editor-context-config' : ''; ?>">
	<?php if ($action === 'edit'): ?>
		<div class="editor-context">
			<div>
				<div class="editor-eyebrow">Chỉnh sửa đề thi & đáp án</div>
				<h3 id="currentTestTitle">Đang tải đề thi...</h3>
			</div>
			<div class="editor-meta">
				<span id="editorQuestionCount">0 câu hỏi</span>
				<span id="editorModeLabel">Đề thi + đáp án</span>
			</div>
		</div>
		<div class="test-audio-config-card" style="margin-top: 20px; padding: 20px; background: #ffffff; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
			<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
				<label style="font-weight: 700; font-size: 15px; color: #1e293b; display: flex; align-items: center; gap: 8px; margin-bottom: 0;">
					<i class="bx bx-music" style="color: var(--primary-color, #4f46e5); font-size: 20px;"></i>
					Âm thanh tổng của đề thi <span class="required-mark" style="color: red;">*</span>
				</label>
				<span id="testAudioStatus" style="font-size: 13px; font-weight: 600; color: #64748b;">Đang kiểm tra...</span>
			</div>
			
			<div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
				<input type="file" id="testAudioFileInput" accept="audio/*" style="display: none;" onchange="handleTestAudioChange(this)">
				<button type="button" class="btn" onclick="document.getElementById('testAudioFileInput').click()" style="background-color: #2563eb; color: white; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; min-height: unset; transition: all 0.2s; margin-bottom: 0;">
					<i class="bx bx-upload" style="font-size: 18px;"></i> Tải lên audio tổng
				</button>
				<span id="testAudioFileName" style="color: #475569; font-size: 14px; flex-grow: 1; min-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Chưa có file nào được tải lên</span>
				
				<div id="testAudioPlayerContainer" style="display: none; align-items: center; gap: 10px;">
					<audio id="testAudioPlayer" controls preload="auto" style="height: 36px; max-width: 300px;"></audio>
				</div>
			</div>
		</div>
	<?php else: ?>
		<h3>Cấu Hình Đề Thi & Câu Hỏi</h3>
	<?php endif; ?>

	<div class="config-row <?php echo $action === 'edit' ? 'editor-hidden-controls' : ''; ?>">
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
				<option value="all">Tất cả câu hỏi</option>
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

<?php if ($action === 'edit'): ?>
	<nav id="partQuickNav" class="part-quick-nav" aria-label="Điều hướng part"></nav>
<?php endif; ?>

<div id="messageBox" class="message-box"></div>
<div id="partInfo" class="part-info"></div>
