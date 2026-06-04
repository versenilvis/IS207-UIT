/**
 * main.js - Điểm đầu vào của ứng dụng (Khởi tạo App)
 */

document.addEventListener('DOMContentLoaded', initApp);

async function initApp() {
	setupEventListeners();
	await loadTestsData();
	setupUIFromParams();
}

function setupUIFromParams() {
	const partSelect = document.getElementById('partSelect');
	if (partSelect) partSelect.disabled = true;

	if (ACTION_PARAM === 'edit' && TEST_ID_PARAM) {
		toggleCreateTestForm(false);
		toggleOtherForms(true);
		
		const testSelect = document.getElementById('testSelect');
		if (testSelect && testSelect.querySelector(`option[value="${TEST_ID_PARAM}"]`)) {
			testSelect.value = TEST_ID_PARAM;
			syncCurrentTestSummary();
			if (partSelect) {
				partSelect.disabled = false;
				partSelect.value = IS_FULL_TEST_EDIT ? 'all' : '1';
			}
			if (IS_FULL_TEST_EDIT) {
				loadSavedQuestionsToForm({ allParts: true });
			} else {
				onTestChange();
			}
		}
	} else {
		// Mặc định hiện tất cả nhưng form tạo có thể thu gọn (nếu muốn)
		toggleCreateTestForm(true);
		toggleOtherForms(true);
		addBlock('single');
	}
}

function setupEventListeners() {
	const createForm = document.getElementById('createTestForm');
	if (createForm) {
		createForm.addEventListener('submit', handleCreateTestSubmit);
	}
}

/**
 * Các hàm callback cần thiết cho sự kiện trong component
 */
function onTestChange() {
	const testSelect = document.getElementById('testSelect');
	const partSelect = document.getElementById('partSelect');
	if (!testSelect || !partSelect) return;

	const testId = testSelect.value;

	if (!testId) {
		showMessage('Vui lòng chọn đề thi', 'error');
		partSelect.value = '';
		const partInfo = document.getElementById('partInfo');
		if (partInfo) partInfo.classList.remove('show');
		return;
	}
	partSelect.disabled = false;
	partSelect.value = IS_FULL_TEST_EDIT ? 'all' : '1';
	syncCurrentTestSummary();
	if (IS_FULL_TEST_EDIT) {
		loadSavedQuestionsToForm({ allParts: true });
	} else {
		onPartChange();
	}
}

function onPartChange() {
	const partSelect = document.getElementById('partSelect');
	if (!partSelect) return;

	const part = partSelect.value;
	if (!part) {
		const partInfo = document.getElementById('partInfo');
		if (partInfo) partInfo.classList.remove('show');
		return;
	}

	if (part === 'all') {
		const partInfo = document.getElementById('partInfo');
		if (partInfo) {
			partInfo.innerHTML = '<strong>Tất cả câu hỏi</strong> Dữ liệu đang được query trực tiếp từ database theo đề thi đã chọn.';
			partInfo.classList.add('show');
		}
		loadSavedQuestionsToForm({ allParts: true });
		return;
	}

	const messageBox = document.getElementById('messageBox');
	if (messageBox) {
		messageBox.className = 'message-box';
		messageBox.textContent = '';
	}

	const config = PART_CONFIG[parseInt(part)];
	const partInfo = document.getElementById('partInfo');
	if (partInfo) {
		partInfo.innerHTML = `
			<strong>${config.name}</strong>
			Yêu cầu: ${config.requiresImage ? '✓ Hình ảnh' : ''} 
			${config.requiresAudio ? '✓ Âm thanh' : ''} 
			${config.requiresContent ? '✓ Nội dung' : ''}
		`;
		partInfo.classList.add('show');
	}

	document.querySelectorAll('.question-block').forEach(block => updateMediaBadges(block, part));
	loadSavedQuestionsToForm();
}

function syncCurrentTestSummary() {
	const testSelect = document.getElementById('testSelect');
	const selectedOption = testSelect?.selectedOptions?.[0];
	const titleElement = document.getElementById('currentTestTitle');
	const countElement = document.getElementById('editorQuestionCount');

	if (titleElement && selectedOption) {
		titleElement.textContent = selectedOption.textContent || 'Đề thi hiện tại';
	}

	if (countElement && selectedOption) {
		const total = selectedOption.dataset.questionCount;
		countElement.textContent = total ? `${total} câu hỏi` : 'Đang tải câu hỏi';
	}

	// sync main test audio player and status UI
	const audioUrl = selectedOption?.dataset.audioUrl || '';
	const audioStatus = document.getElementById('testAudioStatus');
	const audioFileName = document.getElementById('testAudioFileName');
	const audioPlayer = document.getElementById('testAudioPlayer');
	const audioContainer = document.getElementById('testAudioPlayerContainer');

	if (audioStatus && audioFileName) {
		if (audioUrl) {
			audioStatus.textContent = 'Đã có audio';
			audioStatus.style.color = '#16a34a';
			audioFileName.textContent = audioUrl.split('/').pop();
			if (audioPlayer && audioContainer) {
				audioPlayer.src = audioUrl;
				audioPlayer.load(); // reload audio source and fetch metadata
				audioContainer.style.display = 'flex';
			}
		} else {
			audioStatus.textContent = 'Chưa có audio (Bắt buộc)';
			audioStatus.style.color = '#dc2626';
			audioFileName.textContent = 'Chưa có file nào được tải lên';
			if (audioPlayer && audioContainer) {
				audioPlayer.src = '';
				audioPlayer.load(); // clear the audio resource
				audioContainer.style.display = 'none';
			}
		}
	}
}

// handle uploading of main test audio file
async function handleTestAudioChange(input) {
	if (!input.files || !input.files[0]) return;
	const file = input.files[0];
	const testSelect = document.getElementById('testSelect');
	const testId = testSelect?.value;
	if (!testId) {
		showMessage('Vui lòng chọn đề thi trước', 'error');
		input.value = '';
		return;
	}

	const audioStatus = document.getElementById('testAudioStatus');
	if (audioStatus) {
		audioStatus.textContent = 'Đang tải lên...';
		audioStatus.style.color = '#2563eb';
	}

	const formData = new FormData();
	formData.append('audio_file', file);
	const selectedOption = testSelect.selectedOptions[0];
	formData.append('title', selectedOption ? selectedOption.textContent : 'Đề thi');

	try {
		const response = await fetch(`/api/tests/${testId}`, {
			method: 'POST',
			body: formData
		});
		const result = await response.json();
		if (result.success && result.data && result.data.audio_url) {
			showMessage('Tải lên âm thanh tổng thành công', 'success');
			if (selectedOption) {
				selectedOption.dataset.audioUrl = result.data.audio_url;
			}
			syncCurrentTestSummary();
		} else {
			showMessage('Lỗi: ' + (result.message || 'Không thể tải lên audio'), 'error');
			syncCurrentTestSummary();
		}
	} catch (error) {
		console.error('error uploading test audio:', error);
		showMessage('Lỗi tải lên tệp âm thanh', 'error');
		syncCurrentTestSummary();
	}
	input.value = '';
}

function renderPartQuickNav(parts = []) {
	const nav = document.getElementById('partQuickNav');
	if (!nav) return;

	const availableParts = new Set(parts.map(part => String(part)));
	nav.innerHTML = Array.from({ length: 7 }, (_, index) => {
		const part = String(index + 1);
		const isAvailable = availableParts.has(part);
		return `
			<button type="button" class="part-nav-btn ${isAvailable ? '' : 'is-empty'}" data-part="${part}">
				<span>Part</span>
				<strong>${part}</strong>
			</button>
		`;
	}).join('');

	nav.querySelectorAll('.part-nav-btn').forEach(button => {
		button.addEventListener('click', () => setActiveEditorPart(button.dataset.part, true));
	});
}

function setActiveEditorPart(part, shouldScroll = true) {
	if (!part) return;
	AppState.activePart = String(part);

	document.querySelectorAll('.part-nav-btn').forEach(button => {
		if (button.dataset.part === AppState.activePart && document.getElementById(`part-${AppState.activePart}`)) {
			button.classList.remove('is-empty');
		}
		button.classList.toggle('active', button.dataset.part === AppState.activePart);
	});

	if (shouldScroll) {
		const heading = document.getElementById(`part-${AppState.activePart}`);
		if (heading) heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}
}
