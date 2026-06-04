/**
 * utils.js - Các hàm bổ trợ dùng chung
 */

function updateAllQuestionNumbers() {
	let currentNumber = 1;
	document.querySelectorAll('.question-block').forEach(block => {
		if (block.classList.contains('single-type')) {
			const input = block.querySelector('.question-number');
			if (input) { input.value = currentNumber++; }
		} else {
			block.querySelectorAll('.sub-question-item').forEach(subQ => {
				const input = subQ.querySelector('.sub-question-number');
				if (input) { input.value = currentNumber++; }
			});
		}
	});
}

function getLastQuestionNumber() {
	const inputs = [...document.querySelectorAll('.single-type .question-number'), ...document.querySelectorAll('.sub-question-item .sub-question-number')];
	if (inputs.length === 0) {
		return AppState.allTestQuestionNumbers.size > 0 ? Math.max(...Array.from(AppState.allTestQuestionNumbers)) : 0;
	}
	return Math.max(...inputs.map(i => parseInt(i.value) || 0), 0);
}

function handleAutoFillPaste(e) {
	const pasteText = (e.clipboardData || window.clipboardData).getData('text').trim();
	const lines = pasteText.split('\n').map(line => line.trim()).filter(line => line.length > 0);
	
	if (lines.length >= 5) {
		e.preventDefault();
		const targetBlock = e.target.closest('.single-type') || e.target.closest('.sub-question-item');
		const optionInputs = targetBlock.querySelectorAll('.option-content');
		
		const options = lines.slice(-4);
		const questionText = lines.slice(0, lines.length - 4).join('\n');
		e.target.value = questionText;
		
		for (let i = 0; i < 4; i++) {
			if (optionInputs[i]) {
				optionInputs[i].value = options[i].replace(/^[A-Da-d1-4][\.\)\s\-\/\]]+/, '').trim();
			}
		}
	}
}
