/**
 * validation.js - Kiểm tra tính hợp lệ của dữ liệu trước khi gửi đi
 */

function validateAllBlocks(blocks, part) {
    let isValid = true;
    const seenQuestionNumbers = new Set();

    const checkError = (condition, msg) => {
        if (condition) { showMessage(msg, 'error'); isValid = false; return true; }
        return false;
    };

    blocks.forEach((block, blockIndex) => {
        if (!isValid) return; 

        if (block.dataset.type === 'single') {
            const qNumStr = block.querySelector('.question-number')?.value.trim();
            if (checkError(!qNumStr, `Câu #${blockIndex + 1}: Vui lòng nhập số thứ tự câu hỏi`)) return;
            
            const qNum = parseInt(qNumStr);
            if (checkError(seenQuestionNumbers.has(qNum), `Câu #${blockIndex + 1}: Số thứ tự ${qNumStr} bị trùng lặp`)) return;
            if (checkError(AppState.allTestQuestionNumbers.has(qNum) && !block.dataset.questionId, `Câu #${blockIndex + 1}: Số thứ tự ${qNumStr} đã tồn tại trong đề`)) return;
            if (checkError(qNum < 1 || qNum > 200, `Câu #${blockIndex + 1}: Số thứ tự phải từ 1-200`)) return;
            seenQuestionNumbers.add(qNum);

            if (checkError(!block.querySelector('.question-content')?.value.trim(), `Câu #${blockIndex + 1}: Vui lòng nhập nội dung câu hỏi`)) return;

            const options = block.querySelectorAll('.option-content');
            options.forEach(opt => { checkError(!opt.value.trim(), `Câu #${blockIndex + 1}: Vui lòng nhập đầy đủ 4 đáp án`); });
            if (!isValid) return;

            if (checkError(!block.querySelector('.correct-radio:checked'), `Câu #${blockIndex + 1}: Vui lòng chọn đáp án đúng`)) return;

            const hasMedia = (block.querySelector('.audio-file')?.files[0] || block.querySelector('.audio-file')?.dataset.existingUrl) || 
                             (block.querySelector('.image-file')?.files[0] || block.querySelector('.image-file')?.dataset.existingUrl);
            const hasAudio = (block.querySelector('.audio-file')?.files[0] || block.querySelector('.audio-file')?.dataset.existingUrl);
            
            if (checkError(part === '1' && !hasMedia, `Câu #${blockIndex + 1}: Part 1 cần hình ảnh hoặc âm thanh`)) return;
            if (checkError(['2', '3', '4'].includes(part) && !hasAudio, `Câu #${blockIndex + 1}: Part ${part} cần âm thanh`)) return;

        } else {
            if (checkError(!block.querySelector('.passage-content')?.value.trim(), `Cụm #${blockIndex + 1}: Vui lòng nhập nội dung đoạn văn`)) return;
            
            const subQs = block.querySelectorAll('.sub-question-item');
            if (checkError(subQs.length === 0, `Cụm #${blockIndex + 1}: Vui lòng thêm ít nhất 1 câu hỏi`)) return;

            const hasMedia = (block.querySelector('.group-audio-file')?.files[0] || block.querySelector('.group-audio-file')?.dataset.existingUrl) || 
                             (block.querySelector('.group-image-file')?.files[0] || block.querySelector('.group-image-file')?.dataset.existingUrl);
            const hasAudio = (block.querySelector('.group-audio-file')?.files[0] || block.querySelector('.group-audio-file')?.dataset.existingUrl);

            if (checkError(part === '1' && !hasMedia, `Cụm #${blockIndex + 1}: Part 1 cần hình ảnh hoặc âm thanh`)) return;
            if (checkError(['2', '3', '4'].includes(part) && !hasAudio, `Cụm #${blockIndex + 1}: Part ${part} cần âm thanh`)) return;

            subQs.forEach((subQ, subIndex) => {
                if (!isValid) return;
                const qNumStr = subQ.querySelector('.sub-question-number')?.value.trim();
                if (checkError(!qNumStr, `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Thiếu số thứ tự`)) return;
                
                const qNum = parseInt(qNumStr);
                if (checkError(seenQuestionNumbers.has(qNum), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Trùng số thứ tự ${qNumStr}`)) return;
                if (checkError(AppState.allTestQuestionNumbers.has(qNum) && !subQ.dataset.questionId, `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số ${qNumStr} đã tồn tại`)) return;
                if (checkError(qNum < 1 || qNum > 200, `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số thứ tự phải từ 1-200`)) return;
                seenQuestionNumbers.add(qNum);

                if (checkError(!subQ.querySelector('.question-content')?.value.trim(), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Thiếu nội dung`)) return;
                subQ.querySelectorAll('.option-content').forEach(opt => { checkError(!opt.value.trim(), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Thiếu đáp án`); });
                if (checkError(!subQ.querySelector('input[type="radio"]:checked'), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Chưa chọn đáp án đúng`)) return;
            });
        }
    });
    return isValid;
}
