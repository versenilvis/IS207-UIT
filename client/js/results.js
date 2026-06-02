const urlParams = new URLSearchParams(window.location.search);
const ATTEMPT_ID = urlParams.get('attempt_id');
const API_URL = `/api/score?attempt_id=${ATTEMPT_ID}`;

const TOEIC_PARTS = [
    { id: 1, name: "Part 1: Ảnh" },
    { id: 2, name: "Part 2: Câu hỏi ngắn" },
    { id: 3, name: "Part 3: Hội thoại" },
    { id: 4, name: "Part 4: Độc thoại" },
    { id: 5, name: "Part 5: Đọc câu hoàn chỉnh" },
    { id: 6, name: "Part 6: Điền từ" },
    { id: 7, name: "Part 7: Đọc hiểu" }
];

$(document).ready(async function () {
    if (!ATTEMPT_ID) { alert("Không tìm thấy ID bài làm!"); return; }

    try {
        const res = await fetch(API_URL);
        const json = await res.json();

        if (json.status === 'success' && json.summary) {
            renderResults(json.summary, json.data);
        } else {
            $('#wrong-questions-list').html('<div class="p-5 text-center" style="color:#94a3b8;">Không tìm thấy dữ liệu bài làm.</div>');
        }
    } catch (e) {
        console.error(e);
        $('#wrong-questions-list').html('<div class="p-5 text-center text-danger">Lỗi kết nối máy chủ. Thử lại sau.</div>');
    }

    // toggle lọc – dùng event delegation để hoạt động sau khi render động
    $(document).on('click', '#filter-all', function () {
        $('#filter-all').addClass('active');
        $('#filter-wrong').removeClass('active');
        $('.question-item').show();
    });

    $(document).on('click', '#filter-wrong', function () {
        $('#filter-wrong').addClass('active');
        $('#filter-all').removeClass('active');
        $('.question-item').each(function () {
            // data-correct="1" là đúng → ẩn; "" là sai → hiện
            $(this).attr('data-correct') === '1' ? $(this).hide() : $(this).show();
        });
    });

    // tạm dừng các trình phát âm thanh khác khi bắt đầu phát một trình phát mới
    document.addEventListener('play', function(e) {
        const audios = document.getElementsByTagName('audio');
        for (let i = 0; i < audios.length; i++) {
            if (audios[i] !== e.target) {
                audios[i].pause();
            }
        }
    }, true);
});

function renderResults(summary, questions) {
    $('#total-points').text(summary.total_score);
    $('#listening-points').html(`${summary.listening_score}<span class="stat-sub">/495</span>`);
    $('#reading-points').html(`${summary.reading_score}<span class="stat-sub">/495</span>`);

    if (summary.test_uuid) $('#btn-retake').attr('href', `exam.php?uuid=${summary.test_uuid}`);

    const total = parseInt(summary.listening_correct) + parseInt(summary.reading_correct);
    const totalQuestions = questions.length || 200;
    $('#accuracy-rate').text(((total / totalQuestions) * 100).toFixed(1) + '%');

    questions.forEach(q => {
        q.user_choice    = q.user_choice    ? q.user_choice.toUpperCase()    : '';
        q.correct_option = q.correct_option ? q.correct_option.toUpperCase() : '';
        q.status = (q.user_choice === q.correct_option) && (q.user_choice !== '');
    });

    renderReviewList(questions);
    renderAnswerGrid(questions);
}

function renderReviewList(questions) {
    let html = '';

    TOEIC_PARTS.forEach(part => {
        const partQuestions = questions.filter(q => parseInt(q.part) === part.id);
        if (!partQuestions.length) return;

        // tiêu đề từng part
        html += `
        <div class="part-section-header">${part.name}</div>
        <div class="review-questions-grid p-3">
        `;

        let lastParagraph = '';

        partQuestions.forEach(item => {
            const ok = item.status;
            
            // hiển thị tiêu đề và hình ảnh đoạn văn nếu sang nhóm mới
            if (item.paragraph && item.paragraph !== lastParagraph) {
                const hasImage = !!item.passage_image;
                const headerClass = hasImage ? 'passage-group-header mb-3 border-bottom pb-2 pt-2 px-3 bg-light rounded' : 'passage-group-header no-image mb-1 border-bottom pb-1 pt-1 px-3 bg-light rounded';
                let passageImgHtml = '';
                if (hasImage) {
                    passageImgHtml = `<img src="${item.passage_image}" class="passage-image img-fluid mb-3" style="max-height: 350px; display: block; margin: 10px auto;">`;
                }
                let passageTransHtml = '';
                if (item.passage_translation_en && item.passage_translation_en.trim() && item.passage_translation && item.passage_translation.trim()) {
                    const uniqueId = `trans_p_${item.question_number}`;
                    passageTransHtml = `
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#${uniqueId}" aria-expanded="false" style="font-size: 0.78rem; font-weight: 500;">
                            <i class="bx bx-translate me-1"></i>Xem đoạn văn & Bản dịch
                        </button>
                        <div class="collapse mt-2" id="${uniqueId}">
                            <div class="p-3 rounded text-dark" style="background-color: #f8fafc; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);">
                                <div class="row">
                                    <div class="col-md-6 border-end">
                                        <strong style="color: #475569; display: block; margin-bottom: 8px;"><i class="bx bx-file me-1"></i>Reading text:</strong>
                                        <div style="font-size: 0.88rem; line-height: 1.6; color: #334155;">
                                            ${item.passage_translation_en.trim()}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <strong style="color: #2563eb; display: block; margin-bottom: 8px;"><i class="bx bx-globe me-1"></i>Dịch đoạn văn:</strong>
                                        <div style="font-size: 0.88rem; line-height: 1.6; color: #334155;">
                                            ${item.passage_translation.trim()}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                } else if (item.passage_translation && item.passage_translation.trim()) {
                    const uniqueId = `trans_p_${item.question_number}`;
                    passageTransHtml = `
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#${uniqueId}" aria-expanded="false" style="font-size: 0.78rem; font-weight: 500;">
                            <i class="bx bx-translate me-1"></i>Dịch đoạn văn
                        </button>
                        <div class="collapse mt-2" id="${uniqueId}">
                            <div class="p-3 rounded text-dark" style="background-color: #f1f5f9; font-size: 0.88rem; line-height: 1.5; border-left: 3px solid #3b82f6;">
                                ${item.passage_translation.trim()}
                            </div>
                        </div>
                    </div>`;
                }
                let passageAudioHtml = '';
                if (item.passage_audio && item.passage_audio !== 'null') {
                    passageAudioHtml = `<audio controls src="${item.passage_audio}" class="mt-2" style="max-width: 320px; display: block;"></audio>`;
                }
                const isPlaceholder = item.paragraph.trim().startsWith('Questions ') && !item.paragraph.includes('<');
                let passageHtml = '';
                
                if (isPlaceholder) {
                    passageHtml = `<span class="fw-bold text-dark" style="font-size: 0.95rem;">${item.paragraph}</span>`;
                } else {
                    const passageQuestions = partQuestions.filter(q => q.paragraph === item.paragraph);
                    const nums = passageQuestions.map(q => parseInt(q.question_number)).filter(Number.isInteger);
                    const min = Math.min(...nums);
                    const max = Math.max(...nums);
                    const rangeHeader = (Number.isInteger(min) && Number.isInteger(max))
                        ? `<span class="fw-bold text-dark d-block mb-1" style="font-size: 0.95rem;">Questions ${min} - ${max}:</span>`
                        : '';

                    let titleHtml = '';
                    let bodyHtml = item.paragraph;
                    const headerMatch = item.paragraph.match(/^(\s*<h[1-6]>.*?<\/h[1-6]>)(.*)$/is);
                    if (headerMatch) {
                        titleHtml = headerMatch[1];
                        bodyHtml = headerMatch[2];
                    }

                    passageHtml = `
                        ${rangeHeader}
                        ${titleHtml}
                        <div class="passage-text-card">
                            ${bodyHtml}
                        </div>
                    `;
                }

                html += `
                    <div class="${headerClass}">
                        ${passageHtml}
                        ${passageImgHtml}
                        ${passageAudioHtml}
                        ${passageTransHtml}
                    </div>
                `;
                lastParagraph = item.paragraph;
            } else if (!item.paragraph) {
                lastParagraph = '';
            }

            let mediaHtml = '';
            if (item.image_url && item.image_url !== 'null') mediaHtml += `<img src="${item.image_url}" class="img-fluid mt-2" style="max-height:280px;">`;
            if (item.audio_url && item.audio_url !== 'null') mediaHtml += `<audio controls src="${item.audio_url}" class="mt-2" style="max-width: 320px; display: block;"></audio>`;

            const displayContent = item.question_content || (parseInt(item.part) === 2 ? 'Mark your answer on your answer sheet.' : '');
            const contentHtml = displayContent || (mediaHtml ? '' : '<i style="color:#94a3b8;">Nội dung không khả dụng.</i>');
            
            // tạo các lựa chọn A, B, C, D kèm nội dung chi tiết
            const numOptions = parseInt(item.part) === 2 ? 3 : 4;
            const optionsList = ['A', 'B', 'C', 'D'].slice(0, numOptions);
            let optionsHtml = '';
            
            optionsList.forEach(opt => {
                let textClass = 'ans-text';
                let isSystemHighlight = false;
                
                if (opt === item.correct_option) {
                    textClass += ' fw-bold text-success';
                    isSystemHighlight = true;
                } else if (opt === item.user_choice && opt !== item.correct_option) {
                    textClass += ' fw-bold text-danger';
                    isSystemHighlight = true;
                }
                
                const optText = (item.options && item.options[opt]) ? item.options[opt].trim() : '';
                const translation = (item.option_translations && item.option_translations[opt]) ? item.option_translations[opt].trim() : '';
                const isPlaceholder = optText === '' || 
                                    optText.toUpperCase() === opt.toUpperCase() || 
                                    optText.toUpperCase() === `(${opt.toUpperCase()})`;
                const displayContent = isPlaceholder ? '' : ' ' + optText;
                
                let displayOptText = `<span class="fw-bold me-2">(${opt})</span>${displayContent}`;
                if (translation) {
                    const arrowClass = isSystemHighlight ? '' : 'text-secondary';
                    const transClass = isSystemHighlight ? '' : 'text-muted';
                    displayOptText += ` <span class="${arrowClass} mx-1">→</span> <span class="option-translation ${transClass}" style="font-style: italic;">${translation}</span>`;
                }

                optionsHtml += `
                <div class="mb-2">
                    <div class="${textClass}">${displayOptText}</div>
                </div>`;
            });

            let explanationHtml = '';
            if (item.explanation && item.explanation.trim()) {
                explanationHtml = `
                <div class="explanation-box mt-3 p-3 rounded" style="font-size: 0.9rem; background-color: #f8fafc; border-left: 4px solid #3b82f6; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);">
                    <strong style="color: #2563eb; display: block; margin-bottom: 4px;"><i class="bx bx-info-circle me-1"></i>Giải thích:</strong>
                    <div style="color: #4b5563; line-height: 1.5;">${item.explanation.trim()}</div>
                </div>`;
            }

            html += `
            <div class="question-item" id="question-target-${item.question_number}" data-correct="${ok ? '1' : ''}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="q-num-badge ${ok ? 'correct' : 'wrong'}">
                        <i class="bx ${ok ? 'bx-check' : 'bx-x'}"></i> Câu ${item.question_number}
                    </span>
                </div>
                <div class="q-content ${ok ? 'correct' : 'wrong'}">
                    <span>${contentHtml}</span>
                    ${mediaHtml}
                </div>
                <div class="options-list mt-3">
                    ${optionsHtml}
                </div>
                ${explanationHtml}
            </div>`;
        });
        html += '</div>';
    });

    $('#wrong-questions-list').html(html);
}

function renderAnswerGrid(questions) {
    let html = '';
    TOEIC_PARTS.forEach(part => {
        const pq = questions.filter(q => parseInt(q.part) === part.id);
        if (!pq.length) return;
        html += `<div class="part-label">${part.name}</div><div style="display:flex;flex-wrap:wrap;">`;
        pq.forEach(item => {
            let cls = 'unanswered';
            if (item.user_choice) cls = item.status ? 'correct' : 'wrong';
            html += `<div class="question-box ${cls}" onclick="scrollToQuestion(${item.question_number})">${item.question_number}</div>`;
        });
        html += '</div>';
    });
    $('#answer-grid').html(html);
}

function scrollToQuestion(qNo) {
    const target = $(`#question-target-${qNo}`);
    if (!target.length) return;
    
    const container = $('#wrong-questions-list');
    container.animate({
        scrollTop: container.scrollTop() + target.offset().top - container.offset().top - 30
    }, 300);
    
    $('.question-item').removeClass('highlight-flash');
    target.addClass('highlight-flash');
    setTimeout(() => target.removeClass('highlight-flash'), 500);
}