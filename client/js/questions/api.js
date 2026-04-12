/**
 * api.js - Các hàm giao tiếp với Backend thông qua fetch API
 */

async function loadTestsData() {
	try {
		const response = await fetch('/api/tests');
		if (!response.ok) throw new Error(`HTTP ${response.status}: ${await response.text()}`);
		
		const result = await response.json();
		if (!result.success || !Array.isArray(result.data)) throw new Error('Định dạng dữ liệu không hợp lệ');

		const testSelect = document.getElementById('testSelect');
		if (!testSelect) return;

		if (result.data.length === 0) {
			showMessage('Không có đề thi nào', 'warning');
			return;
		}

		result.data.forEach(test => {
			const option = document.createElement('option');
			option.value = test.id;
			option.textContent = test.title || `Đề thi ${test.id}`;
			testSelect.appendChild(option);
		});
	} catch (error) {
		console.error('Error loading tests:', error);
		showMessage('Lỗi tải danh sách đề thi', 'error');
	}
}

async function handleCreateTestSubmit(e) {
	e.preventDefault();
	const form = e.target;
	const formData = new FormData(form);
	
	const data = {
		title: formData.get('title'),
		description: formData.get('description'),
		is_premium: formData.get('is_premium') ? 1 : 0,
		is_active: formData.get('is_active') ? 1 : 0
	};

	try {
		const response = await fetch('/api/tests', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(data)
		});
		const result = await response.json();

		if (result.success) {
			showMessage('Tạo bài thi thành công!', 'success');
			form.reset();
			toggleCreateTestForm(false);
			toggleOtherForms(true);
			
			const testSelect = document.getElementById('testSelect');
			if (testSelect) {
				testSelect.innerHTML = '<option value="">-- Chọn đề thi --</option>';
				await loadTestsData();

				setTimeout(() => {
					if (result.data && result.data.id) {
						testSelect.value = result.data.id;
						onTestChange();
						setTimeout(() => {
							const partSelect = document.getElementById('partSelect');
							if (partSelect) {
								partSelect.value = '1';
								onPartChange();
							}
						}, 300);
					}
				}, 500);
			}
		} else {
			showMessage(`Lỗi: ${result.message || 'Không thể tạo bài thi'}`, 'error');
		}
	} catch (error) {
		console.error('Error creating test:', error);
		showMessage('Lỗi tạo bài thi', 'error');
	}
}

async function loadSavedQuestionsToForm() {
	const testSelect = document.getElementById('testSelect');
	const partSelect = document.getElementById('partSelect');
	if (!testSelect || !partSelect) return;

	const testId = testSelect.value;
	const part = partSelect.value;
	if (!testId || !part) return;

	AppState.loadedQuestionIds.clear();
	AppState.loadedPassageIds.clear();

	try {
		const response = await fetch(`/api/questions/${testId}`);
		if (!response.ok) throw new Error(`HTTP ${response.status}`);
		const result = await response.json();

		AppState.allTestQuestionNumbers.clear();
		if (result.success && Array.isArray(result.data)) {
			result.data.forEach(q => {
				if (q.question_number) AppState.allTestQuestionNumbers.add(parseInt(q.question_number));
			});
		}

		if (!result.success || !result.data || result.data.length === 0) {
			deleteAllBlocks();
			addBlock('single');
			return;
		}

		const partQuestions = result.data.filter(q => parseInt(q.part) === parseInt(part));
		if (partQuestions.length === 0) {
			deleteAllBlocks();
			addBlock('single');
			return;
		}

		partQuestions.sort((a, b) => parseInt(a.question_number) - parseInt(b.question_number));
		deleteAllBlocks();

		const groupQuestions = partQuestions.filter(q => q.passage_id);
		let passagesMap = {};

		if (groupQuestions.length > 0) {
			const passagesRes = await fetch(`/api/passages/${testId}`);
			if (passagesRes.ok) {
				const pResult = await passagesRes.json();
				if (pResult.success && pResult.data) {
					pResult.data.forEach(p => passagesMap[p.id] = p);
				}
			}
		}

		const passageToQuestions = {};
		groupQuestions.forEach(q => {
			if (!passageToQuestions[q.passage_id]) passageToQuestions[q.passage_id] = [];
			passageToQuestions[q.passage_id].push(q);
		});

		Object.values(passageToQuestions).forEach(arr => arr.sort((a, b) => parseInt(a.question_number) - parseInt(b.question_number)));

		const processedPassages = new Set();

		partQuestions.forEach(q => {
			if (q.passage_id) {
				if (!processedPassages.has(q.passage_id)) {
					processedPassages.add(q.passage_id);
					addBlock('group');
					const blockDiv = document.querySelector('.question-block.group-type:last-child');
					if (blockDiv) fillGroupQuestionData(passagesMap[q.passage_id], passageToQuestions[q.passage_id], blockDiv);
				}
			} else {
				addBlock('single');
				const blockDiv = document.querySelector('.question-block.single-type:last-child');
				if (blockDiv) fillSingleQuestionData(q, blockDiv);
			}
		});

	} catch (error) {
		console.error('Error loading saved questions:', error);
		showMessage('Lỗi tải câu hỏi đã lưu', 'warning');
		deleteAllBlocks();
		addBlock('single');
	}
}

async function submitSingleQuestionAPI(block, testId, part) {
	try {
		const opts = block.querySelectorAll('.options-container .option-item .option-content');
		const options = { A: opts[0]?.value.trim(), B: opts[1]?.value.trim(), C: opts[2]?.value.trim(), D: opts[3]?.value.trim() };

		const questionId = block.dataset.questionId;
		if (questionId) await fetch('/api/questions/' + questionId, { method: 'DELETE' });

		const formData = new FormData();
		formData.append('test_id', testId);
		formData.append('part', part);
		formData.append('question_number', block.querySelector('.question-number').value);
		formData.append('content', block.querySelector('.question-content').value.trim() || null);
		formData.append('correct_answer', block.querySelector('.correct-radio:checked')?.value);
		formData.append('explanation', block.querySelector('.explanation').value.trim() || null);
		formData.append('options', JSON.stringify(options));

		const audioIn = block.querySelector('.audio-file');
		if (audioIn?.files[0]) formData.append('audio_file', audioIn.files[0]);
		else if (audioIn?.dataset.existingUrl) formData.append('audio_url', audioIn.dataset.existingUrl);

		const imageIn = block.querySelector('.image-file');
		if (imageIn?.files[0]) formData.append('image_file', imageIn.files[0]);
		else if (imageIn?.dataset.existingUrl) formData.append('image_url', imageIn.dataset.existingUrl);

		const response = await fetch('/api/questions', { method: 'POST', body: formData });
		const result = await response.json();

		if (!result.success) {
			showMessage(`Lỗi: ${result.message}`, 'error');
			return false;
		}
		return true;
	} catch (error) {
		return false;
	}
}

async function submitGroupQuestionsAPI(block, testId, part) {
	let created = 0, errors = 0;
	try {
		let passageId = null;
		const passageContent = block.querySelector('.passage-content').value.trim();
		const subQuestions = block.querySelectorAll('.sub-question-item');

		const existingPassageId = block.dataset.passageId;
		if (existingPassageId) await fetch('/api/passages/' + existingPassageId, { method: 'DELETE' });

		const pFormData = new FormData();
		pFormData.append('test_id', testId);
		pFormData.append('part', part);
		if (passageContent) pFormData.append('content', passageContent);

		const aIn = block.querySelector('.group-audio-file');
		if (aIn?.files[0]) pFormData.append('audio_file', aIn.files[0]);
		else if (aIn?.dataset.existingUrl) pFormData.append('audio_url', aIn.dataset.existingUrl);

		const iIn = block.querySelector('.group-image-file');
		if (iIn?.files[0]) pFormData.append('image_file', iIn.files[0]);
		else if (iIn?.dataset.existingUrl) pFormData.append('image_url', iIn.dataset.existingUrl);

		const pRes = await fetch('/api/passages', { method: 'POST', body: pFormData });
		const pResult = await pRes.json();

		if (pResult.success) {
			passageId = pResult.data.passage_id;
			subQuestions.forEach(sq => delete sq.dataset.questionId);
		} else {
			errors++; return { created, errors };
		}

		for (let subQ of subQuestions) {
			try {
				const opts = subQ.querySelectorAll('.sub-options-grid .option-content');
				const options = { A: opts[0]?.value.trim(), B: opts[1]?.value.trim(), C: opts[2]?.value.trim(), D: opts[3]?.value.trim() };
				
				const qFormData = new FormData();
				qFormData.append('test_id', testId);
				qFormData.append('part', part);
				qFormData.append('passage_id', passageId);
				qFormData.append('question_number', subQ.querySelector('.sub-question-number').value);
				qFormData.append('content', subQ.querySelector('.question-content').value.trim());
				qFormData.append('correct_answer', subQ.querySelector('input[type="radio"]:checked')?.value);
				qFormData.append('explanation', subQ.querySelector('.explanation').value.trim() || null);
				qFormData.append('options', JSON.stringify(options));

				const qRes = await fetch('/api/questions', { method: 'POST', body: qFormData });
				const qResult = await qRes.json();
				qResult.success ? created++ : errors++;
			} catch (e) { errors++; }
		}
	} catch (e) { errors++; }
	return { created, errors };
}
