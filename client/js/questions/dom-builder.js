/**
 * dom-builder.js - Quản lý việc thêm/xóa các khối câu hỏi trên giao diện
 */

function addBlock(type, partOverride = null) {
	const testSelect = document.getElementById('testSelect');
	const partSelect = document.getElementById('partSelect');
	if (!testSelect || !partSelect) return;

	const testId = testSelect.value;
	const part = partOverride || (partSelect.value === 'all' ? AppState.activePart : partSelect.value);

	if (!testId) return showMessage('Vui lòng chọn đề thi trước', 'error');
	if (!part || part === 'all') return showMessage('Vui lòng chọn Part trên thanh điều hướng trước khi thêm câu hỏi', 'error');

	AppState.globalBlockCounter++;
	const container = document.getElementById('questions-container');
	const templateId = type === 'single' ? 'single-question-template' : 'group-question-template';
	const template = document.getElementById(templateId);
	if (!template) return;

	const clone = template.content.cloneNode(true);
	const blockDiv = clone.querySelector('.question-block');
	
	blockDiv.dataset.blockId = AppState.globalBlockCounter;
	blockDiv.dataset.part = part;
	const nextNumber = getLastQuestionNumber() + 1;

	if (type === 'single') {
		const numberInput = blockDiv.querySelector('.question-number');
		if (numberInput) numberInput.value = nextNumber;
		blockDiv.querySelectorAll('.correct-radio').forEach(r => r.name = `correct_block_${AppState.globalBlockCounter}`);
		adjustOptionDVisibility(blockDiv, part);
	} else {
		const subContainer = blockDiv.querySelector('.sub-questions-container');
		for (let i = 0; i < 3; i++) {
			subContainer.appendChild(createSubQuestionDOM(AppState.globalBlockCounter, nextNumber + i, part));
		}
	}

	if (IS_FULL_TEST_EDIT) ensurePartHeading(container, part);

	const nextPartHeading = IS_FULL_TEST_EDIT ? findNextPartHeading(container, part) : null;
	if (nextPartHeading) {
		container.insertBefore(clone, nextPartHeading);
	} else {
		container.appendChild(clone);
	}
	updateMediaBadges(blockDiv, part);
	setActiveEditorPart(part, false);
	updateQuestionCount();
}

function ensurePartHeading(container, part) {
	if (container.querySelector(`.part-section-heading[data-part="${part}"]`)) return;

	const heading = document.createElement('div');
	heading.className = 'part-section-heading';
	heading.id = `part-${part}`;
	heading.dataset.part = String(part);
	heading.innerHTML = `
		<span>Part ${part}</span>
		<strong>${PART_CONFIG[parseInt(part)]?.name || 'Câu hỏi'}</strong>
	`;

	const headings = Array.from(container.querySelectorAll('.part-section-heading'));
	const nextHeading = headings.find(item => parseInt(item.dataset.part) > parseInt(part));
	if (nextHeading) {
		container.insertBefore(heading, nextHeading);
	} else {
		container.appendChild(heading);
	}
}

function findNextPartHeading(container, part) {
	const headings = Array.from(container.querySelectorAll('.part-section-heading'));
	const currentIndex = headings.findIndex(heading => heading.dataset.part === String(part));
	if (currentIndex === -1) return null;

	for (let i = currentIndex + 1; i < headings.length; i++) {
		return headings[i];
	}

	return null;
}

function createSubQuestionDOM(blockId, questionNumber = null, part = null) {
	const template = document.getElementById('sub-question-template');
	const clone = template.content.cloneNode(true);
	const div = clone.querySelector('.sub-question-item');
	
	AppState.globalSubQuestionCounter++;
	const radioName = `correct_group_${blockId}_sub_${AppState.globalSubQuestionCounter}`;

	if (questionNumber) {
		const numIn = div.querySelector('.sub-question-number');
		if (numIn) numIn.value = questionNumber;
	}

	div.querySelectorAll('.correct-radio').forEach(r => r.name = radioName);
	
	if (part) {
		adjustOptionDVisibility(div, part);
	}
	
	return div;
}

function addSubQuestionBtn(button) {
	const blockDiv = button.closest('.question-block');
	const part = blockDiv.dataset.part;
	const subContainer = blockDiv.querySelector('.sub-questions-container');
	const nextNumber = getLastQuestionNumber() + 1;
	subContainer.appendChild(createSubQuestionDOM(blockDiv.dataset.blockId, nextNumber, part));
	updateQuestionCount();
}

function removeSubQuestion(button) {
	const subQuestion = button.closest('.sub-question-item');
	const block = subQuestion.closest('.question-block');
	
	// track explicitly deleted question id
	if (subQuestion.dataset.questionId) {
		AppState.deletedQuestionIds.add(subQuestion.dataset.questionId);
	}
	
	subQuestion.remove();

	if (block.querySelectorAll('.sub-question-item').length === 0) {
		// track explicitly deleted passage id
		if (block.dataset.passageId) {
			AppState.deletedPassageIds.add(block.dataset.passageId);
		}
		block.remove();
	}
	
	updateAllQuestionNumbers();
	updateQuestionCount();
}

function removeBlock(button) {
	const block = button.closest('.question-block');
	
	// track explicitly deleted question or passage id
	if (block.dataset.questionId) {
		AppState.deletedQuestionIds.add(block.dataset.questionId);
	}
	if (block.dataset.passageId) {
		AppState.deletedPassageIds.add(block.dataset.passageId);
	}
	
	// also track any sub-questions inside the block
	block.querySelectorAll('.sub-question-item').forEach(subQ => {
		if (subQ.dataset.questionId) {
			AppState.deletedQuestionIds.add(subQ.dataset.questionId);
		}
	});

	block.remove();
	updateAllQuestionNumbers();
	updateQuestionCount();
}

function deleteAllBlocks() {
	document.querySelectorAll('.question-block').forEach(block => {
		if (block.dataset.questionId) {
			AppState.deletedQuestionIds.add(block.dataset.questionId);
		}
		if (block.dataset.passageId) {
			AppState.deletedPassageIds.add(block.dataset.passageId);
		}
		block.querySelectorAll('.sub-question-item').forEach(subQ => {
			if (subQ.dataset.questionId) {
				AppState.deletedQuestionIds.add(subQ.dataset.questionId);
			}
		});
	});

	const container = document.getElementById('questions-container');
	if (container) container.innerHTML = '';
	AppState.globalBlockCounter = 0;
	AppState.globalSubQuestionCounter = 0;
	updateQuestionCount();
}

function adjustOptionDVisibility(container, part) {
	const isPart2 = String(part) === '2';
	const isPart1Or2 = String(part) === '1' || String(part) === '2';
	
	// For single questions
	const optionItems = container.querySelectorAll('.option-item');
	if (optionItems.length >= 4) {
		const optD = optionItems[3];
		if (isPart2) {
			optD.style.display = 'none';
			const input = optD.querySelector('.option-content');
			if (input) {
				input.removeAttribute('required');
				input.value = '';
			}
		} else {
			optD.style.display = '';
			const input = optD.querySelector('.option-content');
			if (input) input.setAttribute('required', '');
		}
	}
	
	// For sub questions
	const subOptions = container.querySelectorAll('.sub-option');
	if (subOptions.length >= 4) {
		const optD = subOptions[3];
		if (isPart2) {
			optD.style.display = 'none';
			const input = optD.querySelector('.option-content');
			if (input) {
				input.removeAttribute('required');
				input.value = '';
			}
		} else {
			optD.style.display = '';
			const input = optD.querySelector('.option-content');
			if (input) input.setAttribute('required', '');
		}
	}

	// remove required and values for part 1 and 2 options since they are listening items
	if (isPart1Or2) {
		container.querySelectorAll('.option-content').forEach(input => {
			input.removeAttribute('required');
		});
		const qContent = container.querySelector('.question-content');
		if (qContent) qContent.removeAttribute('required');
	}
}

