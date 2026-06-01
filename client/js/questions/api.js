/**
 * api.js - Các hàm giao tiếp của question với Backend thông qua fetch API
 */


//  load tất cả các đề thi có sẵn từ server (hàm này khác với getTestList trong server là nó chỉ fetch qua
// endpoint api/tests thôi, còn getTestList fetch full list đề từ database với query)
//  dữ liệu tải về sẽ được dùng để đổ vào các thẻ <option> trong dropdown chọn đề thi
// 	hàm này trả về data dạng như này:
/* {
  "success": true,
  "data": [
    {
      "id": "ac15f725-3647-11f1-8a60-5e73694bac0c",
      "title": "asdas",
      "is_premium": false,
      "is_active": 1,
      "created_at": "2026-04-12 08:14:48",
      "is_unlocked": true
    },
    {
      "id": "9364db42-3646-11f1-8a60-5e73694bac0c",
      "title": "asdasd",
      "is_premium": false,
      "is_active": 1,
      "created_at": "2026-04-12 08:06:57",
      "is_unlocked": true
    },
    {
      "id": "583029e9-3642-11f1-8a60-5e73694bac0c",
      "title": "asdasd",
      "is_premium": false,
      "is_active": 1,
      "created_at": "2026-04-12 07:36:40",
      "is_unlocked": true
    }
  ]
}
*/
async function loadTestsData() {
	try {
		// gửi 1 GET request lên api/tests lấy đề
		// truy cập localhost:3000/api/tests để hiểu (phải có data trong database rồi mới hiện ra)
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
			option.value = test.uuid;
			option.textContent = test.title || `Đề thi ${test.uuid}`;
			testSelect.appendChild(option);
		});
	} catch (error) {
		console.error('Error loading tests:', error);
		showMessage('Lỗi tải danh sách đề thi', 'error');
	}
}


//  xử lý sự kiện gửi form để tạo đề
//  sau khi tạo thành công, giao diện sẽ được reset và tự động chọn đề thi mới tạo để mình tiếp tục 
//  thêm các câu hỏi
//  tham số "e" là event là sự kiện submit của form
// 	có thể test qua curl với lệnh sau:
/*
curl -X POST http://localhost:3000/api/tests \
     -H "Content-Type: application/json" \
     -d '{
           "title": "Tên bài thi mới",
           "description": "Mô tả bài thi",
           "is_premium": 0,
           "is_active": 1
         }'

 */
// tức là hàm này gửi POST request qua API/tests. xong api gọi createTest() trong test-controller.php
// và trả về cho data cho hàm này

// ✅ Hàm lưu audio của bài thi
// Lưu audio bài thi độc lập (gọi từ nút Lưu Audio)
async function saveTestAudio() {
	const testSelect = document.getElementById('testSelect');
	if (!testSelect || !testSelect.value) {
		showMessage('Vui lòng chọn đề thi trước', 'error');
		return;
	}

	const testId = testSelect.value;
	const testAudioFile = document.getElementById('testAudioFile');
	if (!testAudioFile) {
		showMessage('Không tìm thấy input audio', 'error');
		return;
	}

	const formData = new FormData();

	// Nếu có file mới, upload file
	if (testAudioFile.files && testAudioFile.files[0]) {
		formData.append('audio_file', testAudioFile.files[0]);
	} else if (testAudioFile.dataset.existingUrl && testAudioFile.dataset.existingUrl !== 'null') {
		// Nếu không có file mới nhưng có URL cũ, gửi URL cũ
		formData.append('audio_url', testAudioFile.dataset.existingUrl);
	} else {
		// Không có file mới cũng không có URL cũ
		showMessage('Vui lòng chọn file audio', 'warning');
		return;
	}

	try {
		showMessage('Đang lưu audio bài thi...', 'info');
		const response = await fetch(`/api/tests/${testId}/audio`, {
			method: 'POST',
			body: formData
		});
		const result = await response.json();
		if (result.success) {
			showMessage('Audio bài thi đã được lưu thành công', 'success');
			// Lưu URL mới vào dataset để giữ lại nếu reload
			if (result.data && result.data.audio_url) {
				testAudioFile.dataset.existingUrl = result.data.audio_url;
			}
		} else {
			showMessage('Lỗi: ' + result.message, 'error');
			console.error('Lỗi lưu audio:', result);
		}
	} catch (error) {
		showMessage('Lỗi khi lưu audio: ' + error.message, 'error');
		console.error('Exception lưu audio:', error);
	}
}

async function handleCreateTestSubmit(e) {
	e.preventDefault();
	const form = e.target;
	const formData = new FormData(form);

	// chọn các option khi tạo đề thi + nhâp title
	const data = {
		title: formData.get('title'),
		description: formData.get('description'),
		is_premium: formData.get('is_premium') ? 1 : 0,
		is_active: formData.get('is_active') ? 1 : 0
	};

	// sau khi nhập xong thì gửi 1 POST request lên server để tạo đề thi qua api/tests
	// nhìn lại file ./task/core/api.md để hiểu
	try {
		const response = await fetch('/api/tests', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(data)
		});
		const result = await response.json();
	
		// nêu thành công thì reset form, ẩn form tạo đề, hiện form khác
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


// hàm này khác api/test/uuid ở chỗ nó là cho Admin, nó dùng để lấy danh sách câu hỏi để chỉnh sửa hoặc check
// nó có mấy phần như correct_answer, explanation (phần này /api/test/uuid không có)
// lấy data từ endpoint /api/questions?test_id=uuid
// data trả về, ví dụ:
/*
{
  "success": true,
  "data": [
    {
      "id": 1,
      "test_id": 1,
      "passage_id": null,
      "part": 1,
      "question_number": 1,
      "content": "ádasdadd",
      "audio_url": null,
      "image_url": "/server/uploads/image/16c147a1-e7a2-4632-8c25-83056dbf283f.jpg",
      "correct_answer": "A",
      "explanation": "null",
      "options": [
        {
          "id": 1,
          "label": "A",
          "content": "aaa"
        },
        {
          "id": 2,
          "label": "B",
          "content": "asa"
        },
        {
          "id": 3,
          "label": "C",
          "content": "ádasdadad"
        },
        {
          "id": 4,
          "label": "D",
          "content": "sadasdas"
        }
      ]
    }
  ],
  "count": 1
}
 */
// Load audio đề thi từ server khi đổi đề
async function loadTestAudio(testId) {
	const testAudioFile = document.getElementById('testAudioFile');
	const testAudioPreview = document.getElementById('testAudioPreview');
	if (!testAudioFile || !testAudioPreview) return;

	try {
		const response = await fetch(`/api/tests/${testId}`);
		if (!response.ok) return;
		
		const result = await response.json();
		if (result.success && result.data && result.data.audio_url) {
			const audioUrl = result.data.audio_url;
			// Lưu URL cũ vào dataset
			testAudioFile.dataset.existingUrl = audioUrl;
			testAudioFile.value = ''; // Clear file input
			// Hiển thị preview audio cũ
			testAudioPreview.innerHTML = `<audio controls src="${audioUrl}" style="width: 100%;"></audio>`;
		} else {
			// Không có audio, clear preview
			testAudioFile.dataset.existingUrl = '';
			testAudioFile.value = '';
			testAudioPreview.innerHTML = '';
		}
	} catch (error) {
		console.warn('Lỗi load audio đề thi:', error);
		testAudioFile.dataset.existingUrl = '';
		testAudioFile.value = '';
		testAudioPreview.innerHTML = '';
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

	// Load audio đề thi
	await loadTestAudio(testId);

	try {
		const response = await fetch(`/api/questions?test_id=${testId}`);
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
			const passagesRes = await fetch(`/api/passages?test_id=${testId}`);
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

/**
 * Gửi yêu cầu API để lưu hoặc cập nhật một câu hỏi đơn lẻ.
 * Nếu câu hỏi đã tồn tại, nó sẽ xóa bản ghi cũ trước khi tạo bản ghi mới để cập nhật.
 * @param {HTMLElement} block - Phần tử DOM chứa dữ liệu câu hỏi đơn lẻ.
 * @param {string} testId - ID của đề thi.
 * @param {number|string} part - Phần thi TOEIC (1-7).
 * @returns {Promise<Object>} Đối tượng kết quả {"success": boolean, "message": string}.
 */
async function submitSingleQuestionAPIWithResult(block, testId, part) {
	try {
		const opts = block.querySelectorAll('.options-container .option-item .option-content');
		const options = { A: opts[0]?.value.trim(), B: opts[1]?.value.trim(), C: opts[2]?.value.trim(), D: opts[3]?.value.trim() };

		const questionId = block.dataset.questionId;

		const formData = new FormData();
		formData.append('test_id', testId);
		formData.append('part', part);
		formData.append('question_number', block.querySelector('.question-number').value);
		formData.append('content', block.querySelector('.question-content').value.trim() || null);
		formData.append('correct_answer', block.querySelector('.correct-radio:checked')?.value || '');
		formData.append('explanation', block.querySelector('.explanation').value.trim() || null);
		formData.append('options', JSON.stringify(options));

		const audioIn = block.querySelector('.audio-file');
		if (audioIn?.files[0]) {
			formData.append('audio_file', audioIn.files[0]);
		} else if (audioIn?.dataset.existingUrl) {
			// Giữ lại URL cũ nếu user không upload file mới
			formData.append('audio_url', audioIn.dataset.existingUrl);
		}

		const imageIn = block.querySelector('.image-file');
		if (imageIn?.files[0]) {
			formData.append('image_file', imageIn.files[0]);
		} else if (imageIn?.dataset.existingUrl) {
			// Giữ lại URL cũ nếu user không upload file mới
			formData.append('image_url', imageIn.dataset.existingUrl);
		}

		// Nếu là câu hỏi cũ, gửi ID để backend xử lý cập nhật thay vì xóa-tạo mới
		if (questionId) {
			formData.append('existing_question_id', questionId);
		}

		const response = await fetch('/api/questions', { method: 'POST', body: formData });
		return await response.json();
	} catch (error) {
		return { success: false, message: error.message };
	}
}

/**
 * Gửi yêu cầu API để lưu một cụm câu hỏi kèm theo đoạn văn (Passage).
 * Tự động xóa Passage cũ và các câu hỏi cũ thuộc Passage đó trước khi lưu mới.
 * @param {HTMLElement} block - Phần tử DOM chứa dữ liệu cụm câu hỏi (group).
 * @param {string} testId - ID của đề thi.
 * @param {number|string} part - Phần thi TOEIC (1-7).
 * @returns {Promise<Object>} Đối tượng kết quả bao gồm trạng thái thành công, số lượng câu được tạo và lỗi.
 */
async function submitGroupQuestionsAPI(block, testId, part) {
	let created = 0;
	let errorMessages = [];
	try {
		let passageId = null;
		const passageContent = block.querySelector('.passage-content').value.trim();
		const subQuestions = block.querySelectorAll('.sub-question-item');

		const existingPassageId = block.dataset.passageId;

		const pFormData = new FormData();
		pFormData.append('test_id', testId);
		pFormData.append('part', part);
		if (passageContent) pFormData.append('content', passageContent);

		// Nếu là passage cũ, gửi ID để backend xử lý cập nhật
		if (existingPassageId) {
			pFormData.append('existing_passage_id', existingPassageId);
		}

		const aIn = block.querySelector('.group-audio-file');
		if (aIn?.files[0]) {
			pFormData.append('audio_file', aIn.files[0]);
		} else if (aIn?.dataset.existingUrl) {
			// Giữ lại URL cũ nếu user không upload file mới
			pFormData.append('audio_url', aIn.dataset.existingUrl);
		}

		const iIn = block.querySelector('.group-image-file');
		if (iIn?.files[0]) {
			pFormData.append('image_file', iIn.files[0]);
		} else if (iIn?.dataset.existingUrl) {
			// Giữ lại URL cũ nếu user không upload file mới
			pFormData.append('image_url', iIn.dataset.existingUrl);
		}

		const pRes = await fetch('/api/passages', { method: 'POST', body: pFormData });
		const pResult = await pRes.json();

		if (pResult.success) {
			passageId = pResult.data.passage_id;
			subQuestions.forEach(sq => delete sq.dataset.questionId);
		} else {
			return { success: false, created: 0, message: `Lỗi tạo Passage: ${pResult.message}` };
		}

		for (let subQ of subQuestions) {
			try {
				const opts = subQ.querySelectorAll('.sub-options-grid .option-content');
				const options = { A: opts[0]?.value.trim(), B: opts[1]?.value.trim(), C: opts[2]?.value.trim(), D: opts[3]?.value.trim() };

				const qFormData = new FormData();
				qFormData.append('test_id', testId);
				qFormData.append('part', part);
				qFormData.append('passage_id', passageId);
				const qNum = subQ.querySelector('.sub-question-number').value;
				qFormData.append('question_number', qNum);
				qFormData.append('content', subQ.querySelector('.question-content').value.trim());
				qFormData.append('correct_answer', subQ.querySelector('input[type="radio"]:checked')?.value || '');
				qFormData.append('explanation', subQ.querySelector('.explanation').value.trim() || null);
				qFormData.append('options', JSON.stringify(options));

				const qRes = await fetch('/api/questions', { method: 'POST', body: qFormData });
				const qResult = await qRes.json();
				if (qResult.success) {
					created++;
				} else {
					errorMessages.push(`Câu ${qNum}: ${qResult.message}`);
				}
			} catch (e) {
				errorMessages.push("Lỗi kết nối");
			}
		}
		return {
			success: errorMessages.length === 0,
			created,
			message: errorMessages.join(' | ')
		};
	} catch (e) {
		return { success: false, created, message: e.message };
	}
}

/**
 * Hàm tổng hợp để thu thập và lưu tất cả các khối câu hỏi đang hiển thị trên giao diện.
 * Duyệt qua từng khối (Single/Group) và gọi API lưu trữ tương ứng.
 * @param {Event} [event] - Sự kiện click hoặc submit (có thể bỏ qua nếu gọi thủ công).
 * @returns {Promise<void>} Hiển thị kết quả lưu trữ thông qua hàm showMessage().
 */
async function submitData(event) {
	if (event) event.preventDefault();

	const testSelect = document.getElementById('testSelect');
	const partSelect = document.getElementById('partSelect');
	if (!testSelect || !partSelect) return;

	const testId = testSelect.value;
	const part = partSelect.value;

	if (!testId || !part) {
		return showMessage('Vui lòng chọn đề thi và phần thi trước khi lưu', 'error');
	}

	const blocks = document.querySelectorAll('.question-block');

	showMessage('Đang lưu dữ liệu...', 'info');

	let totalCreated = 0;
	let errorMessages = [];

	// Tìm những câu hỏi đã bị xóa (có trong loadedQuestionIds nhưng không còn trong DOM)
	const remainingQuestionIds = new Set();
	blocks.forEach(block => {
		const qId = block.dataset.questionId;
		if (qId) remainingQuestionIds.add(parseInt(qId));
	});

	const deletedQuestionIds = Array.from(AppState.loadedQuestionIds).filter(id => !remainingQuestionIds.has(id));

	// Xóa những câu hỏi đã bị xóa
	for (let qId of deletedQuestionIds) {
		try {
			const response = await fetch(`/api/questions/${qId}`, { method: 'DELETE' });
			const result = await response.json();
			if (!result.success) {
				errorMessages.push(`Xóa câu ${qId}: ${result.message}`);
			}
		} catch (e) {
			errorMessages.push(`Lỗi xóa câu ${qId}`);
		}
	}

	// Lưu các câu hỏi còn lại
	if (blocks.length === 0) {
		if (deletedQuestionIds.length > 0) {
			showMessage(`Đã xóa ${deletedQuestionIds.length} câu hỏi!`, 'success');
			await loadSavedQuestionsToForm();
		} else {
			showMessage('Không có dữ liệu để lưu', 'warning');
		}
		return;
	}

	for (let block of blocks) {
		const isGroup = block.classList.contains('group-type');
		try {
			if (isGroup) {
				const res = await submitGroupQuestionsAPI(block, testId, part);
				totalCreated += res.created;
				if (!res.success) {
					errorMessages.push(`Cụm câu: ${res.message}`);
				}
			} else {
				const qNum = block.querySelector('.question-number')?.value || 'Không số';
				const res = await submitSingleQuestionAPIWithResult(block, testId, part);
				if (res.success) {
					totalCreated++;
				} else {
					errorMessages.push(`Câu ${qNum}: ${res.message}`);
				}
			}
		} catch (e) {
			errorMessages.push("Lỗi hệ thống khi lưu");
		}
	}

	if (errorMessages.length === 0) {
		showMessage(`Đã lưu thành công ${totalCreated} mục!`, 'success');
		await loadSavedQuestionsToForm();
	} else {
		const errorText = errorMessages.length <= 2 ? errorMessages.join(' | ') : `Có ${errorMessages.length} lỗi xảy ra`;
		showMessage(`Lưu ${totalCreated} mục. Lỗi: ${errorText}`, 'warning');
	}
}