<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Nhập Câu Hỏi TOEIC</title>
    <link href="../styles/questionsStyle.css" rel="stylesheet">
</head>

<body>
    <!-- Icon -->
    <?php include ('../pages/components/head.php'); ?>
    <!-- NavBar & Header -->
    <?php include('./componants/navBar.php'); ?>
    <?php include('./componants/header.php'); ?>

    <div class="container-wrapper">
        
        <!-- Chọn đề thi và phần -->
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

        <!-- Hiển thị thông báo -->
        <div id="messageBox" class="message-box"></div>

        <!-- Thông tin yêu cầu media và nội dung cho từng part -->
        <div id="partInfo" class="part-info"></div>
        <!-- Action buttons thêm, xóa,lưu -->
        <div class="header-actions">
            <button class="btn btn-add" onclick="addBlock('single')">+ Thêm Câu Đơn</button>
            <button class="btn btn-add-group" onclick="addBlock('group')">+ Thêm Cụm Câu Hỏi</button>
            <button class="btn btn-delete-all" onclick="deleteAllBlocks()">
                <i class="bx bx-trash-alt" style="font-size: 1.2rem; vertical-align: -0.125em; margin-right: 5px;"></i>Xóa Tất Cả</button>
            <button class="btn btn-submit" onclick="submitData()">Lưu Bài Test</button>
        </div>

        <div id="questions-container"></div>
    </div>

    <!-- Templates cho block câu hỏi -->
    <template id="single-question-template">
        <div class="question-block single-type" data-type="single">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div class="badge single block-title">Câu hỏi đơn</div>
                <button class="btn-remove" onclick="removeBlock(this)">Xóa</button>
            </div>

            <!-- Số thứ tự câu hỏi -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Số thứ tự câu hỏi</label>
                    <input type="number" class="question-number form-control" min="1" max="200">
                </div>
                <div style="visibility: hidden;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Placeholder</label>
                </div>
            </div>
            <!-- Phần upload Media -->
            <div class="media-upload-section">
                <div class="upload-item">
                    <label><i class="bx bx-camera-alt" style="font-size: 1.2rem; vertical-align: -0.125em; "></i> Hình ảnh <span class="media-required-badge" style="color: red;"></span></label>
                    <input type="file" accept="image/*" class="image-file" onchange="previewMedia(this, 'image')">
                    <small class="media-hint" style="color: #666;">Tùy chọn</small>
                    <div class="preview-container"></div>
                </div>
                <div class="upload-item">
                    <label><i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em; "></i> Âm thanh <span class="media-required-badge" style="color: red;"></span></label>
                    <input type="file" accept="audio/*" class="audio-file" onchange="previewMedia(this, 'audio')">
                    <small class="media-hint" style="color: #666;">Tùy chọn</small>
                    <div class="preview-container"></div>
                </div>
            </div>

            <label><strong>Nội dung câu hỏi:</strong></label>
            <textarea class="form-control question-content" placeholder="Nhập câu hỏi..." onpaste="handleAutoFillPaste(event)"></textarea>

            <div class="options-container">
                <label style="font-weight: 600; display: block; margin-bottom: 10px;">Đáp án <span style="color: red;">*</span></label>
                <div class="option-item"><input type="radio" class="correct-radio" value="A"><span>A.</span><input type="text" class="form-control option-content" placeholder="Đáp án A" required></div>
                <div class="option-item"><input type="radio" class="correct-radio" value="B"><span>B.</span><input type="text" class="form-control option-content" placeholder="Đáp án B" required></div>
                <div class="option-item"><input type="radio" class="correct-radio" value="C"><span>C.</span><input type="text" class="form-control option-content" placeholder="Đáp án C" required></div>
                <div class="option-item"><input type="radio" class="correct-radio" value="D"><span>D.</span><input type="text" class="form-control option-content" placeholder="Đáp án D" required></div>
                <small style="color: #666; display: block; margin-top: 8px;">Chọn đáp án đúng</small>
            </div>

            <label style="font-weight: 600; display: block; margin-top: 15px; margin-bottom: 5px;">Giải thích (Tùy chọn)</label>
            <textarea class="form-control explanation" placeholder="Giải thích đáp án..." rows="2"></textarea>
        </div>
    </template>

    <template id="group-question-template">
        <div class="question-block group-type" data-type="group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div class="badge group block-title">Cụm câu hỏi</div>
                <button class="btn-remove" onclick="removeBlock(this)">Xóa</button>
            </div>

            <!-- Shared Media Section -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="upload-item">
                    <label> <i class="bx bx-camera-alt" style="font-size: 1.2rem; vertical-align: -0.125em; "></i> Hình ảnh <span class="media-required-badge" style="color: red;"></span></label>
                    <input type="file" accept="image/*" class="group-image-file" onchange="previewMedia(this, 'image')">
                    <small class="media-hint" style="color: #666;">Tùy chọn</small>
                    <div class="preview-container"></div>
                </div>
                <div class="upload-item">
                    <label> <i class="bx bx-volume-full" style="font-size: 1.2rem; vertical-align: -0.125em; "></i> Âm thanh <span class="media-required-badge" style="color: red;"></span></label>
                    <input type="file" accept="audio/*" class="group-audio-file" onchange="previewMedia(this, 'audio')">
                    <small class="media-hint" style="color: #666;">Tùy chọn</small>
                    <div class="preview-container"></div>
                </div>
                <div class="upload-item">
                    <label> <i class="bx bx-file" style="font-size: 1.2rem; vertical-align: -0.125em; "></i> Đoạn văn (Passages)</label>
                    <textarea class="form-control passage-content" placeholder="Dán đoạn văn dùng chung vào đây..." style="height: 120px;"></textarea>
                </div>
            </div>

            <div class="sub-questions-container"></div>

            <button class="btn-add-sub" onclick="addSubQuestionBtn(this)">+ Thêm 1 câu hỏi vào cụm</button>
        </div>
    </template>

    <script>
        // ====== Yêu cầu từng phần ======
        const PART_CONFIG = {
            1: {
                name: 'Ảnh',
                requiresImage: true,
                requiresAudio: false,
                requiresContent: false
            },
            2: {
                name: 'Câu hỏi ngắn',
                requiresImage: false,
                requiresAudio: true,
                requiresContent: true
            },
            3: {
                name: 'Hội thoại',
                requiresImage: false,
                requiresAudio: true,
                requiresContent: true
            },
            4: {
                name: 'Độc thoại',
                requiresImage: false,
                requiresAudio: true,
                requiresContent: true
            },
            5: {
                name: 'Đọc câu hoàn chỉnh',
                requiresImage: false,
                requiresAudio: false,
                requiresContent: true
            },
            6: {
                name: 'Điền từ',
                requiresImage: false,
                requiresAudio: false,
                requiresContent: true
            },
            7: {
                name: 'Đọc hiểu',
                requiresImage: false,
                requiresAudio: false,
                requiresContent: true
            },
        };

        let globalBlockCounter = 0; // Đếm số lượng block được thêm
        let loadedQuestionIds = new Set(); // Theo dõi các ID câu hỏi đã load
        let loadedPassageIds = new Set(); // Theo dõi các ID passage đã load
        let allTestQuestionNumbers = new Set(); // Theo dõi TẤT CẢ số thứ tự câu hỏi trong bài test (tất cả các parts)

        // Khởi tạo trang
        document.addEventListener('DOMContentLoaded', () => {
            // Khóa chọn part cho đến khi chọn được đề thi
            document.getElementById('partSelect').disabled = true;
            loadTests();
            addBlock('single');
        });

        // ====== LOAD TESTS ======
        async function loadTests() {
            try {
                // Sư dụng URL tương đối mà không hardcode port để tránh lỗi khi deploy hoặc chạy trên môi trường khác
                const apiUrl = '/IS207-UIT/server/index.php?path=/api/tests';
                const response = await fetch(apiUrl);
                // Kiểm tra lỗi HTTP trước khi parse JSON
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Không thể tải danh sách đề thi');
                }

                if (!result.data || !Array.isArray(result.data)) {
                    throw new Error('Định dạng dữ liệu không hợp lệ');
                }

                const testSelect = document.getElementById('testSelect');
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
                console.error('Error loading tests - Full details:', {
                    message: error.message,
                    stack: error.stack,
                    type: error.name,
                    url: '/IS207-UIT/server/index.php?path=/api/tests'
                });
                showMessage('Lỗi tải danh sách đề thi', 'error');
            }
        }

        // ====== Khi thay đổi test ======
        function onTestChange() {
            const testId = document.getElementById('testSelect').value;
            const partSelect = document.getElementById('partSelect');

            if (!testId) {
                showMessage('Vui lòng chọn đề thi', 'error');
                // Đặt lại lựa chọn part nếu không chọn đề thi
                partSelect.value = '';
                document.getElementById('partInfo').classList.remove('show');
                return;
            }

            // Cho phép chọn part khi đã chọn đề thi
            partSelect.disabled = false;

            showMessage('Vui lòng chọn part', 'warning');
        }

        // ====== Khi thay đổi part ======
        function onPartChange() {
            const part = document.getElementById('partSelect').value;

            if (!part) {
                document.getElementById('partInfo').classList.remove('show');
                return;
            }

            // Clear "Vui lòng chọn part" message khi đã chọn part
            document.getElementById('messageBox').className = 'message-box';
            document.getElementById('messageBox').textContent = '';

            const config = PART_CONFIG[parseInt(part)];
            const partInfo = document.getElementById('partInfo');
            partInfo.innerHTML = `
                <strong>${config.name}</strong>
                Yêu cầu: ${config.requiresImage ? '✓ Hình ảnh' : ''} 
                ${config.requiresAudio ? '✓ Âm thanh' : ''} 
                ${config.requiresContent ? '✓ Nội dung' : ''}
            `;
            partInfo.classList.add('show');

            // Cập nhật các badge media cho tất cả block hiện có (nếu có)
            document.querySelectorAll('.question-block').forEach(block => {
                updateMediaBadges(block, part);
            });

            // TẢI các câu hỏi đã lưu cho part này vào form
            loadSavedQuestionsToForm();
        }

        // ====== TẢI CÂU HỎI ĐÃ LƯU ======
        async function loadSavedQuestionsToForm() {
            const testId = document.getElementById('testSelect').value;
            const part = document.getElementById('partSelect').value;

            console.log('loadSavedQuestionsToForm: testId=', testId, 'part=', part);

            if (!testId || !part) {
                console.log('Skipping load: testId or part is empty');
                return;
            }

            // Clear các Set theo dõi câu hỏi từ part trước đó để tránh bug xóa câu hỏi ở các part khác
            loadedQuestionIds.clear();
            loadedPassageIds.clear();

            try {
                // Su dụng URL tương đối mà không hardcode port để tránh lỗi khi deploy hoặc chạy trên môi trường khác
                const apiUrl = '/IS207-UIT/server/index.php?path=/api/questions&test_id=' + testId;
                console.log('Fetching from:', apiUrl);
                const response = await fetch(apiUrl);

                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP ${response.status}: ${text}`);
                }

                const result = await response.json();
                console.log('API response:', result);

                // Tạo set tất cả số thỨ tự câu hỏi trong bài test (để validation)
                allTestQuestionNumbers.clear();
                if (result.success && result.data && Array.isArray(result.data)) {
                    result.data.forEach(q => {
                        if (q.question_number) {
                            allTestQuestionNumbers.add(parseInt(q.question_number));
                        }
                    });
                }
                console.log('All question numbers in test:', Array.from(allTestQuestionNumbers).sort((a, b) => a - b));

                if (!result.success || !result.data) {
                    // Không có câu hỏi, xóa form hiện tại
                    console.log('No questions in API response');
                    deleteAllBlocks();
                    addBlock('single');
                    return;
                }

                console.log('Total questions from API:', result.data.length);

                // Lọc câu hỏi cho part đã chọn
                const partQuestions = result.data.filter(q => parseInt(q.part) === parseInt(part));
                console.log('Questions for part', part, ':', partQuestions.length, 'filtered from:', result.data.map(q => ({
                    id: q.id,
                    part: q.part,
                    num: q.question_number
                })));

                if (partQuestions.length === 0) {
                    // Không có câu hỏi cho part này, xóa form hiện tại
                    console.log('No questions for this part, showing empty form');
                    deleteAllBlocks();
                    addBlock('single');
                    return;
                }

                // Sắp xếp theo question_number để duy trì thứ tự chèn vào form (đảm bảo câu hỏi nhóm vẫn đứng sau câu đơn nếu cùng số thứ tự)
                partQuestions.sort((a, b) => parseInt(a.question_number) - parseInt(b.question_number));

                // Xóa form hiện tại
                deleteAllBlocks();

                // Phân tách câu đơn và câu nhóm vẫn giữ thứ tự
                const singleQuestions = partQuestions.filter(q => !q.passage_id);
                const groupQuestions = partQuestions.filter(q => q.passage_id);

                // Lấy passages cho các câu hỏi nhóm
                let passagesMap = {};
                if (groupQuestions.length > 0) {
                    const passagesUrl = '/IS207-UIT/server/index.php?path=/api/passages&test_id=' + testId;
                    const passagesResponse = await fetch(passagesUrl);

                    if (!passagesResponse.ok) {
                        const errorText = await passagesResponse.text();
                        console.error('Passages API error:', errorText);
                        showMessage('Lỗi tải passages', 'error');
                        throw new Error(`HTTP ${passagesResponse.status}: ${errorText}`);
                    }

                    const passagesResult = await passagesResponse.json();

                    if (passagesResult.success && passagesResult.data) {
                        passagesResult.data.forEach(p => {
                            passagesMap[p.id] = p;
                        });
                    }
                }
                
                // Tạo bản đồ passage -> câu hỏi để tìm kiếm nhanh
                const passageToQuestions = {};
                // Duyệt qua tất cả câu hỏi nhóm và phân loại theo passage_id
                groupQuestions.forEach(q => {
                    if (!passageToQuestions[q.passage_id]) {
                        passageToQuestions[q.passage_id] = [];
                    }
                    passageToQuestions[q.passage_id].push(q);
                });

                // Sắp xếp câu hỏi con trong mỗi passage theo question_number để đảm bảo thứ tự đúng khi hiển thị
                Object.keys(passageToQuestions).forEach(pid => {
                    passageToQuestions[pid].sort((a, b) => parseInt(a.question_number) - parseInt(b.question_number));
                });

                // Theo dõi các passage đã xử lý để tránh thêm block passage nhiều lần nếu có nhiều câu hỏi cùng passage_id
                const processedPassages = new Set();

                // Duyệt qua tất cả câu hỏi của part (đã được sắp xếp) và thêm vào form theo thứ tự, đảm bảo câu hỏi nhóm đứng sau câu đơn nếu cùng số thứ tự
                try {
                    partQuestions.forEach(q => {
                        console.log('Processing question:', q.id, 'passage_id:', q.passage_id, 'question_number:', q.question_number);
                        try {
                            // Đây là câu hỏi nhóm - thêm cả block passage 1 lần và sau đó thêm câu hỏi con vào đúng block passage đó
                            if (q.passage_id) {                      
                                if (!processedPassages.has(q.passage_id)) {
                                    console.log('Adding group block for passage:', q.passage_id);
                                    processedPassages.add(q.passage_id);
                                    addBlock('group');
                                    const blockDiv = document.querySelector('.question-block.group-type:last-child');
                                    if (blockDiv) {
                                        console.log('Calling loadGroupQuestion with passage:', passagesMap[q.passage_id], 'subQuestions:', passageToQuestions[q.passage_id]);
                                        loadGroupQuestion(passagesMap[q.passage_id], passageToQuestions[q.passage_id], blockDiv);
                                    } else {
                                        console.error('Could not find added group block');
                                    }
                                }
                            } else {
                                // Dây là câu hỏi đơn - thêm trực tiếp vào form
                                console.log('Adding single block for question:', q.id);
                                addBlock('single');
                                const blockDiv = document.querySelector('.question-block.single-type:last-child');
                                if (blockDiv) {
                                    console.log('Calling loadSingleQuestion with question:', q);
                                    loadSingleQuestion(q, blockDiv);
                                } else {
                                    console.error('Could not find added single block');
                                }
                            }
                        } catch (questionError) {
                            console.error('Error processing question', q.id, ':', questionError);
                            showMessage(`Lỗi xử lý câu hỏi ${q.id}`, 'warning');
                        }
                    });
                    console.log('Finished loading all questions');
                } catch (blockError) {
                    console.error('Error while processing questions:', blockError);
                    showMessage('Lỗi xử lý danh sách câu hỏi', 'warning');
                    throw blockError;
                }

            } catch (error) {
                console.error('Error loading saved questions:', error);
                showMessage('Lỗi tải câu hỏi đã lưu', 'warning');
                // Vẫn tạo form trống
                deleteAllBlocks();
                addBlock('single');
            }
        }

        // ====== HIỂN THỊ CÂU HỎI ĐÃ LƯU ======
        function displaySavedQuestions(questions) {
            const container = document.getElementById('savedQuestionsContainer');
            const list = document.getElementById('savedQuestionsList');

            list.innerHTML = '';
            // Hiển thị mỗi câu hỏi với số thứ tự, loại (đơn hay nhóm), nội dung tóm tắt và đáp án đúng
            questions.forEach(q => {
                const div = document.createElement('div');
                div.style.cssText = 'padding: 10px; margin-bottom: 10px; background: white; border-radius: 4px; border: 1px solid #ddd;';

                const passageLabel = q.passage_id ? '[Cụm]' : '[Đơn]';
                const content = q.content ? q.content.substring(0, 100) + (q.content.length > 100 ? '...' : '') : '(Không có nội dung)';
                const answer = q.correct_answer || '?';

                div.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Q${q.question_number} ${passageLabel}</strong><br>
                            <small style="color: #666;">${content}</small><br>
                            <small>Đáp án: <strong>${answer}</strong></small>
                        </div>
                        <div>
                            <button onclick="editQuestion(${q.id})" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;">Sửa</button>
                            <button onclick="deleteQuestion(${q.id})" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Xóa</button>
                        </div>
                    </div>
                `;

                list.appendChild(div);
            });

            container.style.display = 'block';
        }

        // ====== TẢI CÂU HỎI ĐƠN ======
        function loadSingleQuestion(question, block) {
            if (!block) return;

            // Đặt số thứ tự câu hỏi
            const numberInput = block.querySelector('.question-number');
            if (numberInput) numberInput.value = question.question_number;

            // Đặt nội dung
            const contentInput = block.querySelector('.question-content');
            if (contentInput) contentInput.value = question.content || '';

            // Đặt các đáp án
            const optionInputs = block.querySelectorAll('.options-container .option-content');
            if (question.options && question.options.length === 4) {
                question.options.forEach((opt, idx) => {
                    if (optionInputs[idx]) {
                        optionInputs[idx].value = opt.content || '';
                    }
                });
            }

            // Đặt đáp án đúng
            const correctRadios = block.querySelectorAll('.correct-radio');
            correctRadios.forEach(radio => {
                if (radio.value === question.correct_answer) {
                    radio.checked = true;
                }
            });

            // Đặt giải thích
            const explanationInput = block.querySelector('.explanation');
            if (explanationInput) explanationInput.value = (question.explanation && question.explanation !== 'null') ? question.explanation : '';

            // TẢI media (ảnh và âm thanh)
            const mediaSection = block.querySelector('.media-upload-section');
            if (mediaSection) {
                // Load hình ảnh
                if (question.image_url) {
                    const imageInput = mediaSection.querySelector('.upload-item:nth-child(1) input[type="file"]');
                    if (imageInput) {
                        // Đánh dau rằng media đã tồn tại - validation sẽ biết không yêu cầu file mới
                        imageInput.dataset.existingUrl = question.image_url;
                    }
                    // Tìm preview container cho hình ảnh và hiển thị hình ảnh nếu có
                    const imagePreview = mediaSection.querySelector('.upload-item:nth-child(1) .preview-container');
                    if (imagePreview) {
                        imagePreview.innerHTML = `<img src="${question.image_url}" alt="Question image" style="max-width: 200px;">`;
                    }
                }

                // Load âm thanh
                if (question.audio_url) {
                    const audioInput = mediaSection.querySelector('.upload-item:nth-child(2) input[type="file"]');
                    if (audioInput) {
                        // Đánh dau rằng media đã tồn tại - validation sẽ biết không yêu cầu file mới
                        audioInput.dataset.existingUrl = question.audio_url;
                    }
                    // Tìm preview container cho âm thanh và hiển thị player nếu có
                    const audioPreview = mediaSection.querySelector('.upload-item:nth-child(2) .preview-container');
                    if (audioPreview) {
                        audioPreview.innerHTML = `<audio controls src="${question.audio_url}" style="width: 100%;"></audio>`;
                    }
                }
            }

            // Lưu ID câu hỏi để theo dõi
            block.dataset.questionId = question.id;
            loadedQuestionIds.add(question.id);
        }

        // ====== TẢI CẬP CÂU HỎI ======
        function loadGroupQuestion(passage, subQuestions, block) {
            if (!block) return;

            // Đặt nội dung đoạn văn
            const passageInput = block.querySelector('.passage-content');
            if (passageInput) passageInput.value = passage.content || '';

            // TẢI media passage - tìm preview containers cho audio và hình ảnh
            const audioUploadItem = block.querySelector('.group-audio-file')?.closest('.upload-item');
            const imageUploadItem = block.querySelector('.group-image-file')?.closest('.upload-item');

            // Load hình ảnh passage
            if (passage.image_url && imageUploadItem) {
                const imageInput = block.querySelector('.group-image-file');
                if (imageInput) {
                    // Dánh dau rằng media đã tồn tại - validation sẽ biết không yêu cầu file mới
                    imageInput.dataset.existingUrl = passage.image_url;
                }
                const imagePreview = imageUploadItem.querySelector('.preview-container');
                if (imagePreview) {
                    imagePreview.innerHTML = `<img src="${passage.image_url}" alt="Passage image" style="max-width: 200px;">`;
                }
            }

            // Load passage audio
            if (passage.audio_url && audioUploadItem) {
                const audioInput = block.querySelector('.group-audio-file');
                if (audioInput) {
                    // Đánh dau rằng media đã tồn tại - validation sẽ biết không yêu cầu file mới
                    audioInput.dataset.existingUrl = passage.audio_url;
                }
                const audioPreview = audioUploadItem.querySelector('.preview-container');
                if (audioPreview) {
                    audioPreview.innerHTML = `<audio controls src="${passage.audio_url}" style="width: 100%;"></audio>`;
                }
            }

            // Lưu ID passage để tham khảo
            block.dataset.passageId = passage.id;
            loadedPassageIds.add(passage.id);

            // Xóa câu hỎi con mặc định
            const subContainer = block.querySelector('.sub-questions-container');
            const defaultSubs = subContainer.querySelectorAll('.sub-question-item');
            defaultSubs.forEach(sub => sub.remove());

            // Tải các câu hỏi con
            if (subQuestions && subQuestions.length > 0) {
                subQuestions.forEach((subQ, index) => {
                    const subDiv = createSubQuestionDOM(block.dataset.blockId, subQ.question_number || index + 1);

                    // Fill sub-question data
                    subDiv.querySelector('.sub-question-number').value = subQ.question_number || index + 1;
                    subDiv.querySelector('.question-content').value = subQ.content || '';

                    // Fill options
                    const optionInputs = subDiv.querySelectorAll('.sub-options-grid .option-content');
                    if (subQ.options && subQ.options.length === 4) {
                        subQ.options.forEach((opt, idx) => {
                            if (optionInputs[idx]) {
                                optionInputs[idx].value = opt.content || '';
                            }
                        });
                    }

                    // Đặt đáp án đúng
                    const radios = subDiv.querySelectorAll('input[type="radio"]');
                    radios.forEach(radio => {
                        if (radio.value === subQ.correct_answer) {
                            radio.checked = true;
                        }
                    });

                    // Đặt giải thích
                    const explanationInput = subDiv.querySelector('.explanation');
                    if (explanationInput) explanationInput.value = (subQ.explanation && subQ.explanation !== 'null') ? subQ.explanation : '';
                    // Lưu ID câu hỏi để theo dõi 
                    subDiv.dataset.questionId = subQ.id;
                    loadedQuestionIds.add(subQ.id);

                    subContainer.appendChild(subDiv);
                });
            }
        }

        // ====== CẬP NHẬT BADGE MEDIA ======
        function updateMediaBadges(block, part) {
            const config = PART_CONFIG[parseInt(part)];
            const audioLabels = block.querySelectorAll('.upload-item:nth-child(2) .media-required-badge');
            const imageLabels = block.querySelectorAll('.upload-item:nth-child(1) .media-required-badge');
            const audioHints = block.querySelectorAll('.upload-item:nth-child(2) .media-hint');
            const imageHints = block.querySelectorAll('.upload-item:nth-child(1) .media-hint');
            // Cập nhật text badge và hint dựa trên yêu cầu của part
            audioLabels.forEach(label => {
                label.textContent = config.requiresAudio ? '(Bắt buộc)' : '';
            });
            imageLabels.forEach(label => {
                label.textContent = config.requiresImage ? '(Bắt buộc)' : '';
            });
            audioHints.forEach(hint => {
                hint.textContent = config.requiresAudio ? 'MP3, WAV, OGG - tối đa 50MB' : 'Tùy chọn';
            });
            imageHints.forEach(hint => {
                hint.textContent = config.requiresImage ? 'JPG, PNG, GIF - tối đa 5MB' : 'Tùy chọn';
            });
        }

        // ====== CẬP NHẬT SỐ LƯỢNG CÂU HỎI ======
        function updateQuestionCount() {
            const singleQCount = document.querySelectorAll('.single-type').length;
            const subQCount = Array.from(document.querySelectorAll('.group-type')).reduce((sum, group) => {
                return sum + group.querySelectorAll('.sub-question-item').length;
            }, 0);

            const total = singleQCount + subQCount;
            const countElement = document.getElementById('questionCount');
            if (countElement) {
                countElement.textContent = total;
            }
        }

        // ====== THÊM BỘ BLOCK ======
        function addBlock(type) {
            const testId = document.getElementById('testSelect').value;
            const part = document.getElementById('partSelect').value;

            console.log('addBlock called with type:', type, 'testId:', testId, 'part:', part);

            if (!testId) {
                showMessage('Vui lòng chọn đề thi trước', 'error');
                return;
            }

            if (!part) {
                showMessage('Vui lòng chọn part trước', 'error');
                return;
            }

            globalBlockCounter++;
            // Tìm container để thêm block mới
            const container = document.getElementById('questions-container');
            // Chọn template dựa trên loại block (đơn hay nhóm)
            const templateId = type === 'single' ? 'single-question-template' : 'group-question-template';
            // Clone nội dung template để tạo block mới
            const clone = document.getElementById(templateId).content.cloneNode(true);
            
            // Gán blockId duy nhất cho block mới để dễ dàng quản lý sau này
            const blockDiv = clone.querySelector('.question-block');
            blockDiv.dataset.blockId = globalBlockCounter;
            console.log('Created', type, 'block with ID:', globalBlockCounter);

            // Tính số thỨ tự câu hỏi (số cuối + 1, hoặc 1 nếu đầu tiên)
            const nextNumber = getLastQuestionNumber() + 1;

            if (type === 'single') {
                // Đặt số thứ tự câu hỏi cho block đơn mới tạo
                const questionNumberInput = blockDiv.querySelector('.question-number');
                
                if (questionNumberInput) {
                    questionNumberInput.value = nextNumber;
                }
                // Đặt name cho các radio đáp án để đảm bảo mỗi block có nhóm radio riêng biệt
                const radios = blockDiv.querySelectorAll('.correct-radio');
                radios.forEach(radio => radio.name = `correct_block_${globalBlockCounter}`);
            } else if (type === 'group') {
                // Đối với block nhóm, không đặt số thứ tự cho block chính, mà sẽ đặt cho từng câu hỏi con
                const subContainer = blockDiv.querySelector('.sub-questions-container');
                // Bắt đầu đánh số từ nextNumber cho các câu hỏi nhóm
                for (let i = 0; i < 3; i++) {
                    const subQuestion = createSubQuestionDOM(globalBlockCounter, nextNumber + i);
                    subContainer.appendChild(subQuestion);
                }
            }
            // Thêm block mới vào DOM
            container.appendChild(clone);

            // Log số lượng block sau khi thêm để kiểm tra
            const blocksCount = document.querySelectorAll('.question-block').length;
            console.log('After adding block, total blocks in DOM:', blocksCount);

            // Cập nhật các badge media cho part hiện tại
            const config = PART_CONFIG[parseInt(part)];
            updateMediaBadges(blockDiv, part);
            // Cập nhật số lượng câu hỏi sau khi thêm block mới
            updateQuestionCount();
        }

        // ====== TẠO DOM CÂU HỎI CON ======
        function createSubQuestionDOM(blockId, questionNumber = null) {
            // Tạo một subId duy nhất để đảm bảo name của radio không bị trùng lặp giữa các câu hỏi con
            const subId = Date.now() + Math.floor(Math.random() * 1000);
            const radioName = `correct_group_${blockId}_sub_${subId}`;

            // Tạo DOM cho câu hỏi con với số thứ tự được truyền vào (nếu có) hoặc để trống để người dùng nhập thủ công
            const div = document.createElement('div');
            div.className = 'sub-question-item';
            div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <button class="btn-remove-sub" onclick="removeSubQuestion(this)">Xóa</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Số thứ tự câu hỏi</label>
                        <input type="number" class="sub-question-number form-control" min="1" max="200" value="${questionNumber || 1}">
                    </div>
                    <div style="visibility: hidden;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Placeholder</label>
                    </div>
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Nội dung câu hỏi</label>
                    <textarea class="form-control question-content" placeholder="Nhập câu hỏi..." rows="2" onpaste="handleAutoFillPaste(event)"></textarea>
                </div>
                <div style="margin-top: 10px; margin-bottom: 5px; font-weight: 600;">Đáp án <span style="color: red;">*</span></div>
                <div class="sub-options-grid">
                    <div class="sub-option"><input type="radio" name="${radioName}" value="A" required><span>A.</span><input type="text" class="form-control option-content" placeholder="Đáp án A" required></div>
                    <div class="sub-option"><input type="radio" name="${radioName}" value="B" required><span>B.</span><input type="text" class="form-control option-content" placeholder="Đáp án B" required></div>
                    <div class="sub-option"><input type="radio" name="${radioName}" value="C" required><span>C.</span><input type="text" class="form-control option-content" placeholder="Đáp án C" required></div>
                    <div class="sub-option"><input type="radio" name="${radioName}" value="D" required><span>D.</span><input type="text" class="form-control option-content" placeholder="Đáp án D" required></div>
                </div>
                <label style="font-weight: 600; display: block; margin-top: 15px; margin-bottom: 5px;">Giải thích (Tùy chọn)</label>
                <textarea class="form-control explanation" placeholder="Giải thích đáp án..." rows="2"></textarea>
            `;
            return div;
        }

        // ====== NÚT THÊM CÂU HỎI CON ======
        function addSubQuestionBtn(button) {
            // Lấy block chứa nút này để xác định blockId và container của câu hỏi con
            const blockDiv = button.closest('.question-block');
            // Lấy blockId từ data attribute của block để gắn vào câu hỏi con mới tạo
            const blockId = blockDiv.dataset.blockId;
            // Tạo câu hỏi con mới với blockId và số thứ tự tiếp theo (số cuối cùng + 1)
            const subContainer = blockDiv.querySelector('.sub-questions-container');
            const nextNumber = getLastQuestionNumber() + 1;
            // Tạo DOM cho câu hỏi con mới và thêm vào container của block
            const newSubQuestion = createSubQuestionDOM(blockId, nextNumber);
            subContainer.appendChild(newSubQuestion);

            updateQuestionCount();
        }


        // ====== XÓA CÂU HỎI CON ======
        function removeSubQuestion(button) {
            // Tìm phần tử câu hỏi con chứa nút này, sau đó tìm block chứa nó để kiểm tra số lượng câu hỏi con còn lại
            const subQuestion = button.closest('.sub-question-item');
            const block = subQuestion.closest('.question-block');

            // Xóa câu hỏi con khỏi DOM
            subQuestion.remove();

            // Kiểm tra nếu còn câu hỏi con nào trong block không, nếu không còn thì xóa cả block
            const remainingSubQuestions = block.querySelectorAll('.sub-question-item');

            // Nếu không còn câu hỏi con nào, xóa cả block để tránh block nhóm trống
            if (remainingSubQuestions.length === 0) {
                console.log('No sub-questions left, removing block');
                block.remove();
            }
            
            // Cập nhật số thứ tự cho TẤT CẢ câu hỏi trong form (đơn và con) để đảm bảo liên tục
            updateAllQuestionNumbers();
            
            // Cập nhật số lượng câu hỏi sau khi xóa câu hỏi con
            updateQuestionCount();
        }

        // ====== CẬP NHẬT SỐ THỨ TỰ TẤT CẢ CÂU HỎI ======
        function updateAllQuestionNumbers() {
            let currentNumber = 1;
            
            // Duyệt qua tất cả các block theo thứ tự từ trên xuống dưới
            const allBlocks = document.querySelectorAll('.question-block');
            allBlocks.forEach(block => {
                if (block.classList.contains('single-type')) {
                    // Cập nhật câu hỏi đơn
                    const numberInput = block.querySelector('.question-number');
                    if (numberInput) {
                        numberInput.value = currentNumber;
                        currentNumber++;
                    }
                } else if (block.classList.contains('group-type')) {
                    // Cập nhật các câu hỏi con trong block nhóm
                    const subQuestions = block.querySelectorAll('.sub-question-item');
                    subQuestions.forEach((subQ) => {
                        const input = subQ.querySelector('.sub-question-number');
                        if (input) {
                            input.value = currentNumber;
                            currentNumber++;
                        }
                    });
                }
            });
        }

        // ====== XÓA BLOCK ======
        function removeBlock(button) {
            const blockDiv = button.closest('.question-block');
            blockDiv.remove();

            // Cập nhật số thứ tự cho TẤT CẢ câu hỏi trong form
            updateAllQuestionNumbers();
            // Cập nhật số lượng câu hỏi sau khi xóa block
            updateQuestionCount();
        }

        // ====== XÓA TẤT CẢ ======
        function deleteAllBlocks() {
            const container = document.getElementById('questions-container');
            if (container) {
                container.innerHTML = '';
            }
            globalBlockCounter = 0;
            updateQuestionCount();
        }

        // ====== LẤY SỐ THỨ TỰ CÂU HỎI CUỐI CÙNG ======
        function getLastQuestionNumber() {
            // Tìm tất cả input số thứ tự câu hỏi (cả câu đơn và câu con) để xác định số thứ tự lớn nhất hiện có trong form
            const singleQuestions = document.querySelectorAll('.single-type .question-number');
            const subQuestions = document.querySelectorAll('.sub-question-item .sub-question-number');
            const allQuestionInputs = [...singleQuestions, ...subQuestions];

            if (allQuestionInputs.length === 0) {
                // Nếu form trống, lấy số lớn nhất từ tất cả câu hỏi của test (để tiếp tục đánh số khi part chưa có câu hỏi)
                if (allTestQuestionNumbers.size === 0) return 0;
                return Math.max(...Array.from(allTestQuestionNumbers));
            }
            // Tìm số thứ tự lớn nhất hiện có trong form để đặt số thứ tự cho câu hỏi mới tiếp theo
            let maxNumber = 0;
            allQuestionInputs.forEach(input => {
                const num = parseInt(input.value) || 0;
                if (num > maxNumber) maxNumber = num;
            });

            return maxNumber;
        }

        // ====== XEM TRƯỚC MEDIA ======
        function previewMedia(input, type) {
            // Tìm container để hiển thị preview - tìm phần tử kế tiếp có class 'preview-container'
            let container = input.nextElementSibling;
            // Nếu không tìm thấy, tiếp tục tìm kiếm trong các phần tử kế tiếp cho đến khi tìm thấy hoặc hết
            while (container && !container.classList.contains('preview-container')) {
                container = container.nextElementSibling;
            }

            if (!container) {
                // Nếu không tìm thấy trong phần tử kế tiếp, thử tìm trong phần tử cha (trường hợp cấu trúc HTML có thể khác nhau)
                const uploadItem = input.closest('.upload-item');
                if (uploadItem) {
                    container = uploadItem.querySelector('.preview-container');
                }
            }
            
            if (!container) return;
            // Xóa preview cũ nếu có trước khi hiển thị preview mới
            container.innerHTML = '';
            // Kiểm tra nếu có file được chọn, nếu không có thì không làm gì
            if (!input.files || !input.files[0]) return;
            // Tạo URL cho file đã chọn để hiển thị preview mà không cần tải lên server
            const file = input.files[0];
            const url = URL.createObjectURL(file);

            // Validate file size
            const maxSize = type === 'audio' ? 50 * 1024 * 1024 : 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showMessage(`File quá lớn! Tối đa ${type === 'audio' ? '50MB' : '5MB'}`, 'error');
                input.value = '';
                return;
            }
            // Kiểm tra loại file dựa trên type đã chọn hoặc tự động nhận diện nếu type là 'auto'
            const isAudio = type === 'audio' || (type === 'auto' && file.type.startsWith('audio'));
            const isImage = type === 'image' || (type === 'auto' && file.type.startsWith('image'));

            if (isImage) {
                const img = document.createElement('img');
                img.src = url;
                img.style.maxWidth = '200px';
                container.appendChild(img);
            } else if (isAudio) {
                const audio = document.createElement('audio');
                audio.controls = true;
                audio.src = url;
                audio.style.width = '100%';
                container.appendChild(audio);
            }
        }

        // ====== XỬ LÝ DÁN TỰ ĐỘNG ======
        function handleAutoFillPaste(e) {
            // Lấy text dán vào, tách thành các dòng, loại bỏ dòng trống và khoảng trắng thừa
            const pasteText = (e.clipboardData || window.clipboardData).getData('text').trim();
            const lines = pasteText.split('\n').map(line => line.trim()).filter(line => line.length > 0);
            // Nếu có ít nhất 5 dòng (1 dòng cho câu hỏi và 4 dòng cho đáp án), thì tự động điền vào form
            if (lines.length >= 5) {
                // Ngăn chặn hành vi dán mặc định để chúng ta có thể xử lý dữ liệu dán theo cách của mình
                e.preventDefault();
                // Tìm block câu hỏi tương ứng với nơi người dùng dán - có thể là block đơn hoặc câu hỏi con trong block nhóm
                const targetBlock = e.target.closest('.single-type') || e.target.closest('.sub-question-item');
                const optionInputs = targetBlock.querySelectorAll('.option-content');
                // Giả sử 4 dòng cuối là đáp án, còn phần đầu là nội dung câu hỏi (có thể có nhiều dòng), chúng ta sẽ tách riêng phần nội dung câu hỏi và phần đáp án
                const options = lines.slice(-4);
                const questionText = lines.slice(0, lines.length - 4).join('\n');
                // Điền nội dung câu hỏi vào textarea
                e.target.value = questionText;
                // Điều chỉnh đáp án để loại bỏ các ký tự đầu như "A.", "B)", "1-", v.v. nếu người dùng có dán theo định dạng đó
                for (let i = 0; i < 4; i++) {
                    if (optionInputs[i]) {
                        const cleanContent = options[i].replace(/^[A-Da-d1-4][\.\)\s\-\/\]]+/, '').trim();
                        optionInputs[i].value = cleanContent;
                    }
                }
            }
        }

        // ====== HIỂN THỊ THÔNG BÁO ======
        function showMessage(message, type) {
            const messageBox = document.getElementById('messageBox');
            messageBox.textContent = message;
            messageBox.className = `message-box ${type}`;

            // Nếu là thông báo thành công, tự động ẩn sau 5 giây
            if (type === 'success') {
                setTimeout(() => {
                    messageBox.className = 'message-box';
                }, 5000);
            }
        }

        // ====== GỬI DỮ LIỆU ======
        async function submitData() {
            const testId = document.getElementById('testSelect').value;
            const part = document.getElementById('partSelect').value;

            if (!testId) {
                showMessage('Vui lòng chọn đề thi', 'error');
                return;
            }

            if (!part) {
                showMessage('Vui lòng chọn part', 'error');
                return;
            }
            // Kiểm tra nếu không có block nào được thêm vào form, hoặc nếu tất cả các block đều là block nhóm mà không có câu hỏi con nào, thì hiển thị lỗi yêu cầu người dùng thêm ít nhất 1 câu hỏi
            const blocks = document.querySelectorAll('.question-block');
            if (blocks.length === 0 && loadedQuestionIds.size === 0 && loadedPassageIds.size === 0) {
                showMessage('Vui lòng thêm ít nhất 1 câu hỏi', 'error');
                return;
            }

            // Trước khi gửi dữ liệu, thực hiện validate tất cả các câu hỏi trong form để đảm bảo dữ liệu hợp lệ - nếu có lỗi thì hiển thị thông báo lỗi và không gửi dữ liệu
            let hasError = false;
            const seenQuestionNumbers = new Set();

            // Duyệt qua tất cả các block câu hỏi trong form để validate từng câu hỏi - cả câu hỏi đơn và câu hỏi con trong block nhóm
            blocks.forEach((block, blockIndex) => {
                if (block.dataset.type === 'single') {
                    // Validate câu hỏi đơn
                    const questionNumber = block.querySelector('.question-number')?.value.trim();
                    if (!questionNumber) {
                        showMessage(`Câu #${blockIndex + 1}: Vui lòng nhập số thứ tự câu hỏi`, 'error');
                        hasError = true;
                    } else {
                        const qNum = parseInt(questionNumber);
                        // Kiểm tra trùng lặp trong form hiện tại
                        if (seenQuestionNumbers.has(qNum)) {
                            showMessage(`Câu #${blockIndex + 1}: Số thứ tự ${questionNumber} bị trùng lặp trong form hiện tại`, 'error');
                            hasError = true;
                        }
                        // Kiểm tra trùng lặp với tất cả câu hỏi đã tồn tại trong đề thi (các câu hỏi đã tải vào form từ server) - chỉ kiểm tra nếu câu hỏi này chưa có ID (tức là chưa tồn tại trên server, nếu đã tồn tại thì cho phép giữ nguyên số thứ tự)
                        else if (allTestQuestionNumbers.has(qNum) && !block.dataset.questionId) {
                            showMessage(`Câu #${blockIndex + 1}: Số thứ tự ${questionNumber} đã tồn tại ở phần khác của đề thi này`, 'error');
                            hasError = true;
                        } else {
                            seenQuestionNumbers.add(qNum);
                        }
                        // Kiểm tra phạm vi số thứ tự câu hỏi phải nằm trong khoảng 1-200
                        if (qNum < 1 || qNum > 200) {
                            showMessage(`Câu #${blockIndex + 1}: Số thứ tự phải nằm trong khoảng 1-200`, 'error');
                            hasError = true;
                        }
                    }

                    // Validate nội dung câu hỏi không được để trống
                    const content = block.querySelector('.question-content')?.value.trim();
                    if (!content) {
                        showMessage(`Câu #${blockIndex + 1}: Vui lòng nhập nội dung câu hỏi`, 'error');
                        hasError = true;
                    }

                    // Validate đáp án không được để trống và phải đủ 4 đáp án
                    const options = block.querySelectorAll('.option-content');
                    options.forEach((opt, i) => {
                        if (!opt.value.trim()) {
                            showMessage(`Câu #${blockIndex + 1}: Vui lòng nhập đầy đủ 4 đáp án`, 'error');
                            hasError = true;
                        }
                    });

                    // Validate phải chọn đáp án đúng
                    if (!block.querySelector('.correct-radio:checked')) {
                        showMessage(`Câu #${blockIndex + 1}: Vui lòng chọn đáp án đúng`, 'error');
                        hasError = true;
                    }

                    // Validate media theo yêu cầu của part - đối với câu hỏi đơn thì kiểm tra trực tiếp trên block, còn đối với câu hỏi nhóm thì kiểm tra trên phần passage chính của block
                    const audioInput = block.querySelector('.audio-file');
                    const imageInput = block.querySelector('.image-file');
                    const audioFile = audioInput?.files[0];
                    const imageFile = imageInput?.files[0];
                    const hasExistingAudio = audioInput?.dataset.existingUrl;
                    const hasExistingImage = imageInput?.dataset.existingUrl;

                    if (part === '1' && !audioFile && !imageFile && !hasExistingAudio && !hasExistingImage) {
                        showMessage(`Câu #${blockIndex + 1}: Part 1 cần ít nhất hình ảnh hoặc âm thanh`, 'error');
                        hasError = true;
                    } else if (['2', '3', '4'].includes(part) && !audioFile && !hasExistingAudio) {
                        showMessage(`Câu #${blockIndex + 1}: Part ${part} cần âm thanh`, 'error');
                        hasError = true;
                    }
                } else {
                    // Validate block nhóm (passage + các câu hỏi con)
                    const passageContent = block.querySelector('.passage-content')?.value.trim();
                    const audioInput = block.querySelector('.group-audio-file');
                    const imageInput = block.querySelector('.group-image-file');
                    const audioFile = audioInput?.files[0];
                    const imageFile = imageInput?.files[0];
                    const hasExistingAudio = audioInput?.dataset.existingUrl;
                    const hasExistingImage = imageInput?.dataset.existingUrl;

                    // Đối với block nhóm, phần passage sẽ được yêu cầu media theo cấu hình của part, còn các câu hỏi con sẽ không yêu cầu media riêng mà sẽ kế thừa media của passage chính - nên chỉ cần kiểm tra media ở phần passage chính của block nhóm
                    if (part === '1' && !audioFile && !imageFile && !hasExistingAudio && !hasExistingImage) {
                        showMessage(`Cụm #${blockIndex + 1}: Part 1 cần ít nhất hình ảnh hoặc âm thanh`, 'error');
                        hasError = true;
                    } else if (['2', '3', '4'].includes(part) && !audioFile && !hasExistingAudio) {
                        showMessage(`Cụm #${blockIndex + 1}: Part ${part} cần âm thanh`, 'error');
                        hasError = true;
                    }

                    // Validate nội dung passage không được để trống
                    if (!passageContent) {
                        showMessage(`Cụm #${blockIndex + 1}: Vui lòng nhập nội dung đoạn văn`, 'error');
                        hasError = true;
                    }

                    // Validate phải có ít nhất 1 câu hỏi con trong block nhóm
                    const subQuestions = block.querySelectorAll('.sub-question-item');
                    if (subQuestions.length === 0) {
                        showMessage(`Cụm #${blockIndex + 1}: Vui lòng thêm ít nhất 1 câu hỏi`, 'error');
                        hasError = true;
                        return;
                    }

                    // Validate từng câu hỏi con trong block nhóm
                    subQuestions.forEach((subQ, subIndex) => {
                        // Validate số thứ tự câu hỏi con không được để trống, phải là số, không được trùng lặp trong form hiện tại và không được trùng với các câu hỏi đã tồn tại trên server (nếu là câu hỏi mới chưa có ID)
                        const qNumber = subQ.querySelector('.sub-question-number')?.value.trim();
                        if (!qNumber) {
                            showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Vui lòng nhập số thứ tự câu hỏi`, 'error');
                            hasError = true;
                        } else {
                            const qNum = parseInt(qNumber);
                            // Kiểm tra trùng lặp trong form hiện tại - cần kiểm tra cả câu hỏi đơn và câu hỏi con để đảm bảo không có số thứ tự
                            if (seenQuestionNumbers.has(qNum)) {
                                showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số thứ tự ${qNumber} bị trùng lặp trong form hiện tại`, 'error');
                                hasError = true;
                            }
                            // Kiểm tra trùng lặp với tất cả câu hỏi đã tồn tại trong đề thi (các câu hỏi đã tải vào form từ server) - chỉ kiểm tra nếu câu hỏi này chưa có ID (tức là chưa tồn tại trên server, nếu đã tồn tại thì cho phép giữ nguyên số thứ tự)
                            else if (allTestQuestionNumbers.has(qNum) && !subQ.dataset.questionId) {
                                showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số thứ tự ${qNumber} đã tồn tại ở phần khác của đề thi này`, 'error');
                                hasError = true;
                            } else {
                                seenQuestionNumbers.add(qNum);
                            }
                            // Kiểm tra phạm vi số thứ tự câu hỏi phải nằm trong khoảng 1-200
                            if (qNum < 1 || qNum > 200) {
                                showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số thứ tự phải nằm trong khoảng 1-200`, 'error');
                                hasError = true;
                            }
                        }

                        // Validate nội dung câu hỏi con không được để trống
                        const qContent = subQ.querySelector('.question-content')?.value.trim();
                        if (!qContent) {
                            showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Vui lòng nhập nội dung câu hỏi`, 'error');
                            hasError = true;
                        }

                        // Validate đáp án của câu hỏi con không được để trống và phải đủ 4 đáp án
                        const options = subQ.querySelectorAll('.option-content');
                        options.forEach(opt => {
                            if (!opt.value.trim()) {
                                showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Vui lòng nhập đầy đủ 4 đáp án`, 'error');
                                hasError = true;
                            }
                        });

                        // Validate phải chọn đáp án đúng cho câu hỏi con
                        if (!subQ.querySelector('input[type="radio"]:checked')) {
                            showMessage(`Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Vui lòng chọn đáp án đúng`, 'error');
                            hasError = true;
                        }
                    });
                }
            });

            if (hasError) return;

            // Xác định câu hỏi và passage nào đã bị xóa khỏi form so với dữ liệu đã tải vào ban đầu để gửi yêu cầu xóa tương ứng đến server - chúng ta sẽ so sánh ID của các câu hỏi và passage hiện có trong form với các ID đã lưu khi tải dữ liệu từ server để xác định những câu nào đã bị xóa
            const currentQuestionIds = new Set();
            const currentPassageIds = new Set();
            // Duyệt qua tất cả các block hiện có trong form để thu thập ID của các câu hỏi và passage hiện tại - nếu một block hoặc câu hỏi con nào đó có ID nhưng không còn trong form sau khi người dùng chỉnh sửa, thì đó là dấu hiệu cho thấy nó đã bị xóa
            blocks.forEach(block => {
                if (block.dataset.questionId) {
                    currentQuestionIds.add(parseInt(block.dataset.questionId));
                }
                if (block.dataset.passageId) {
                    currentPassageIds.add(parseInt(block.dataset.passageId));
                }
                // Đối với block nhóm, cũng cần kiểm tra các câu hỏi con để lấy ID của chúng nếu có, vì người dùng có thể xóa một câu hỏi con mà không xóa cả block nhóm, nên chúng ta cần theo dõi ID của các câu hỏi con để biết được nếu có câu hỏi con nào bị xóa
                const subQuestions = block.querySelectorAll('.sub-question-item');
                subQuestions.forEach(sub => {
                    if (sub.dataset.questionId) {
                        currentQuestionIds.add(parseInt(sub.dataset.questionId));
                    }
                });
            });

            // So sánh với loadedQuestionIds và loadedPassageIds để tìm ra những ID nào đã bị xóa (có trong loaded nhưng không có trong current), sau đó chúng ta sẽ gửi yêu cầu xóa đến server cho những ID này
            const deletedQuestionIds = Array.from(loadedQuestionIds).filter(id => !currentQuestionIds.has(id));
            const deletedPassageIds = Array.from(loadedPassageIds).filter(id => !currentPassageIds.has(id));
            console.log('Deleted questions:', deletedQuestionIds, 'Deleted passages:', deletedPassageIds);

            // Vô hiệu hóa nút submit và hiển thị trạng thái đang lưu để ngăn người dùng gửi nhiều lần trong khi chờ phản hồi từ server, điều này cũng giúp tránh việc gửi trùng lặp nếu người dùng nhấn nhiều lần
            const submitBtn = event?.target || document.querySelector('.btn-submit');
            const originalText = submitBtn?.textContent;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang lưu...';
            }

            try {
                // Xử lý việc xóa các câu hỏi và passage đã bị xóa khỏi form trước khi gửi dữ liệu mới
                for (const qId of deletedQuestionIds) {
                    try {
                        // Gửi yêu cầu DELETE đến server để xóa câu hỏi với ID này
                        const deleteResponse = await fetch('/IS207-UIT/server/index.php?path=/api/questions/' + qId, {
                            method: 'DELETE'
                        });
                        
                        if (deleteResponse.ok) {
                            console.log('Deleted question:', qId);
                        } else {
                            console.warn('Failed to delete question:', qId, deleteResponse.status);
                            showMessage(`Không thể xóa câu hỏi`, 'warning');
                        }
                    } catch (deleteError) {
                        console.error('Error deleting question', qId, ':', deleteError);
                        showMessage(`Lỗi xóa câu hỏi`, 'warning');
                    }
                }

                // Gửi yêu cầu xóa cho các passage đã bị xóa khỏi form
                for (const pId of deletedPassageIds) {
                    try {
                        // Gửi yêu cầu DELETE đến server để xóa passage với ID này
                        const deleteResponse = await fetch('/IS207-UIT/server/index.php?path=/api/passages/' + pId, {
                            method: 'DELETE'
                        });
                        if (deleteResponse.ok) {
                            console.log('Deleted passage:', pId);
                        } else {
                            console.warn('Failed to delete passage:', pId, deleteResponse.status);
                            showMessage(`Không thể xóa cụm câu`, 'warning');
                        }
                    } catch (deleteError) {
                        console.error('Error deleting passage', pId, ':', deleteError);
                        showMessage(`Lỗi xóa cụm câu`, 'warning');
                    }
                }

                let totalCreated = 0;
                let totalErrors = 0;
                
                for (let blockIndex = 0; blockIndex < blocks.length; blockIndex++) {
                    const block = blocks[blockIndex];
                    if (block.dataset.type === 'single') {
                        // Gửi dữ liệu của câu hỏi đơn này lên server bằng hàm submitSingleQuestion 
                        // Nếu thành công thì tăng biến đếm totalCreated, nếu có lỗi thì tăng biến đếm totalErrors
                        const success = await submitSingleQuestion(block, testId, part);
                        if (success) totalCreated++;
                        else totalErrors++;
                    } else {
                        // Gửi dữ liệu của phần passage chính và tất cả các câu hỏi con bên trong block nhóm đó 
                        const result = await submitGroupQuestions(block, testId, part);
                        // Hàm submitGroupQuestions sẽ trả về số lượng câu hỏi đã tạo thành công và số lượng lỗi nếu có
                        totalCreated += result.created;
                        totalErrors += result.errors;
                    }
                }
                // Hiển thị thông báo tổng kết về số lượng câu hỏi đã lưu thành công và số lượng lỗi nếu có
                if (totalErrors === 0) {
                    const deleteMsg = deletedQuestionIds.length > 0 || deletedPassageIds.length > 0 ?
                        ` (xóa ${deletedQuestionIds.length + deletedPassageIds.length} câu/cụm)` :
                        '';
                    showMessage(`Thành công! Đã lưu ${totalCreated} câu hỏi${deleteMsg}`, 'success');
                    // Form được làm mới lại để hiển thị dữ liệu mới đã được cập nhật từ server
                    const savedPart = part; // Lưu lại part hiện tại trước khi xóa form để chuẩn bị tải lại dữ liệu mới từ server
                    deleteAllBlocks(); // Xóa tất cả block hiện có trong form để chuẩn bị tải lại dữ liệu mới từ server
                    loadedQuestionIds.clear(); // Xóa bộ nhớ tạm của ID câu hỏi đã tải trước đó để chuẩn bị cho việc lưu lại ID mới sau khi tải lại dữ liệu mới từ server
                    loadedPassageIds.clear(); // Xóa bộ nhớ tạm của ID passage đã tải trước đó để chuẩn bị cho việc lưu lại ID mới sau khi tải lại dữ liệu mới từ server
                    document.getElementById('testSelect').value = testId; // Giữ nguyên đề thi đã chọn
                    document.getElementById('partSelect').value = savedPart; // Giữ nguyên phần đã chọn
                    document.getElementById('partInfo').classList.remove('show'); // Ẩn thông tin phần nếu đang hiển thị
                    
                    // Đặt timeout để tải lại dữ liệu mới từ server sau khi đã xóa form và reset trạng thái
                    console.log('Scheduled reload in 800ms for part:', savedPart);
                    setTimeout(() => {
                        console.log('Triggering loadSavedQuestionsToForm with part:', document.getElementById('partSelect').value);
                        loadSavedQuestionsToForm();
                    }, 800);
                } else {
                    showMessage(`Lưu ${totalCreated} câu hỏi thành công, ${totalErrors} lỗi`, 'warning');
                }
            } catch (error) {
                console.error('Error submitting data:', error);
                showMessage('Lỗi lưu dữ liệu', 'error');
            } finally {
                // Kích hoạt lại nút submit và đặt lại text của nó về trạng thái ban đầu
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText || 'Lưu Bài Test';
                }
            }
        }

        // ====== GỬI CÂU HỎI ĐƠN ======
        async function submitSingleQuestion(block, testId, part) {
            try {
                // Lấy dữ liệu từ form của câu hỏi đơn này để chuẩn bị gửi lên server
                const questionNumber = block.querySelector('.question-number').value;
                const content = block.querySelector('.question-content').value.trim() || null;
                const correctAnswer = block.querySelector('.correct-radio:checked')?.value;
                const explanation = block.querySelector('.explanation').value.trim() || null;

                // Lấy dữ liệu đáp án từ các input option
                const optionElements = block.querySelectorAll('.options-container .option-item .option-content');
                const options = {
                    A: optionElements[0]?.value.trim() || '',
                    B: optionElements[1]?.value.trim() || '',
                    C: optionElements[2]?.value.trim() || '',
                    D: optionElements[3]?.value.trim() || ''
                };

                // Validate đáp án không được để trống và phải đủ 4 đáp án - nếu có bất kỳ đáp án nào bị thiếu thì sẽ ném lỗi và hiển thị thông báo lỗi cho người dùng
                Object.values(options).forEach(opt => {
                    if (!opt) throw new Error('Thiếu đáp án');
                });

                // Nếu block này đã có questionId (tức là đã tồn tại trên server), thì chúng ta sẽ gửi yêu cầu DELETE để xóa câu hỏi cũ trước khi tạo mới
                const questionId = block.dataset.questionId;
                if (questionId) {
                    // Gửi yêu cầu DELETE đến server để xóa câu hỏi cũ với ID này trước khi tạo mới
                    await fetch('/IS207-UIT/server/index.php?path=/api/questions/' + questionId, {
                        method: 'DELETE'
                    });
                }

                // Tạo FormData để gửi dữ liệu câu hỏi lên server, bao gồm cả dữ liệu văn bản và file nếu có
                const formData = new FormData();
                formData.append('test_id', testId);
                formData.append('part', part);
                formData.append('question_number', questionNumber);
                formData.append('content', content);
                formData.append('correct_answer', correctAnswer);
                formData.append('explanation', explanation);
                formData.append('options', JSON.stringify(options));

                // Xử lý file âm thanh
                const audioInput = block.querySelector('.audio-file');
                const audioFile = audioInput?.files[0];
                if (audioFile) {
                    // Nếu có file mới được chọn, thì thêm file đó vào FormData để gửi lên server
                    formData.append('audio_file', audioFile);
                } else if (audioInput?.dataset.existingUrl) {
                    // Nếu không có file mới nhưng có URL của file cũ, thì gửi URL đó để server biết giữ nguyên file cũ
                    formData.append('audio_url', audioInput.dataset.existingUrl);
                }

                // Xử lý file hình ảnh
                const imageInput = block.querySelector('.image-file');
                const imageFile = imageInput?.files[0];
                if (imageFile) {
                    // Nếu có file mới được chọn, thì thêm file đó vào FormData để gửi lên server
                    formData.append('image_file', imageFile);
                } else if (imageInput?.dataset.existingUrl) {
                    // Nếu không có file mới nhưng có URL của file cũ, thì gửi URL đó để server biết giữ nguyên file cũ
                    formData.append('image_url', imageInput.dataset.existingUrl);
                }

                // Gửi yêu cầu đến API
                const response = await fetch('/IS207-UIT/server/index.php?path=/api/questions', {
                    method: 'POST',
                    body: formData
                });

                let result;
                try {
                    result = await response.json();
                } catch (parseError) {
                    const text = await response.text();
                    console.error('Response parse error:', {
                        status: response.status,
                        text: text,
                        parseError: parseError.message
                    });
                    showMessage(`Phản hồi server không hợp lệ`, 'error');
                    throw new Error(`Server response invalid: ${text}`);
                }

                console.log('API Response:', {
                    status: response.status,
                    success: result.success,
                    message: result.message,
                    fullResult: result
                });

                if (!result.success) {
                    console.error('API Error Response:', {
                        status: response.status,
                        result: result
                    });
                    showMessage(`Câu ${questionNumber}: ${result.message || 'Lỗi không xác định'}`, 'error');
                    return false;
                }

                return true;
            } catch (error) {
                showMessage(`Lỗi lưu câu hỏi`, 'error');
                return false;
            }
        }

        // ====== GỬI CẶP CÂU HỎI ======
        async function submitGroupQuestions(block, testId, part) {
            let created = 0;
            let errors = 0;

            try {
                const passageContent = block.querySelector('.passage-content').value.trim() || null;
                const subQuestions = block.querySelectorAll('.sub-question-item');

                let passageId = null;
                if (passageContent || subQuestions.length > 0) {
                    try {
                        // Nếu block này đã có passageId (tức là đã tồn tại trên server), thì chúng ta sẽ gửi yêu cầu DELETE để xóa passage cũ trước khi tạo mới
                        const existingPassageId = block.dataset.passageId;
                        if (existingPassageId) {
                            await fetch('/IS207-UIT/server/index.php?path=/api/passages/' + existingPassageId, {
                                method: 'DELETE'
                            });
                            console.log('Deleted old passage:', existingPassageId);
                        }
                        // Tạo FormData để gửi dữ liệu passage lên server, bao gồm cả nội dung văn bản và file nếu có
                        const passageFormData = new FormData();
                        passageFormData.append('test_id', testId);
                        passageFormData.append('part', part);
                        if (passageContent) {
                            passageFormData.append('content', passageContent);
                        }

                        // Xử lý file âm thanh
                        const audioFile = block.querySelector('.group-audio-file')?.files[0];
                        if (audioFile) {
                            // Nếu có file mới được chọn, thì thêm file đó vào FormData để gửi lên server
                            passageFormData.append('audio_file', audioFile);
                            console.log('Added audio file:', audioFile.name);
                        } else {
                            // Nếu không có file mới nhưng có URL của file cũ, thì gửi URL đó để server biết giữ nguyên file cũ
                            const audioInput = block.querySelector('.group-audio-file');
                            if (audioInput?.dataset.existingUrl) {
                                passageFormData.append('audio_url', audioInput.dataset.existingUrl);
                                console.log('Keeping existing audio:', audioInput.dataset.existingUrl);
                            }
                        }

                        // Xử lý file hình ảnh
                        const imageFile = block.querySelector('.group-image-file')?.files[0];
                        if (imageFile) {
                            // Nếu có file mới được chọn, thì thêm file đó vào FormData để gửi lên server
                            passageFormData.append('image_file', imageFile);
                            console.log('Added image file:', imageFile.name);
                        } else {
                            // Nếu không có file mới nhưng có URL của file cũ, thì gửi URL đó để server biết giữ nguyên file cũ 
                            const imageInput = block.querySelector('.group-image-file');
                            if (imageInput?.dataset.existingUrl) {
                                passageFormData.append('image_url', imageInput.dataset.existingUrl);
                                console.log('Keeping existing image:', imageInput.dataset.existingUrl);
                            }
                        }

                        console.log('Passage FormData ready, audio:', audioFile?.name || 'none', 'image:', imageFile?.name || 'none');
                        // Gửi yêu cầu đến API để tạo passage mới và nhận về passageId mới
                        const passageResponse = await fetch('/IS207-UIT/server/index.php?path=/api/passages', {
                            method: 'POST',
                            body: passageFormData
                        });
                        // Phân tích phản hồi từ server sau khi gửi yêu cầu tạo passage mới
                        const passageResult = await passageResponse.json();

                        console.log('Passage API Response:', {
                            status: passageResponse.status,
                            success: passageResult.success,
                            message: passageResult.message,
                            data: passageResult.data,
                            fullResult: passageResult
                        });

                        if (passageResult.success) {
                            // Lưu passageId mới vào biến để sử dụng khi tạo các câu hỏi con bên dưới, đồng thời xóa dataset passageId cũ trên block để tránh nhầm lẫn
                            passageId = passageResult.data.passage_id;
                            subQuestions.forEach(subQ => {
                                delete subQ.dataset.questionId;
                            });
                        } else {
                            showMessage(`Lỗi tạo đoạn văn: ${passageResult.message}`, 'error');
                            errors++;
                        }
                    } catch (error) {
                        showMessage(`Lỗi tạo đoạn văn`, 'error');
                        errors++;
                    }
                }

                // Gửi dữ liệu của tất cả các câu hỏi con bên trong block nhóm đó lên server 
                for (let i = 0; i < subQuestions.length; i++) {
                    // Lấy dữ liệu từ form của câu hỏi con này để chuẩn bị gửi lên server
                    const subQ = subQuestions[i];
                    const questionNumber = subQ.querySelector('.sub-question-number').value;
                    const content = subQ.querySelector('.question-content').value.trim();
                    const correctAnswer = subQ.querySelector('input[type="radio"]:checked')?.value;
                    const explanation = subQ.querySelector('.explanation').value.trim() || null;

                    const optionElements = subQ.querySelectorAll('.sub-options-grid .option-content');
                    const options = {
                        A: optionElements[0]?.value.trim() || '',
                        B: optionElements[1]?.value.trim() || '',
                        C: optionElements[2]?.value.trim() || '',
                        D: optionElements[3]?.value.trim() || ''
                    };

                    try {
                        // Tạo FormData để gửi dữ liệu câu hỏi con lên server, bao gồm cả dữ liệu văn bản và liên kết đến passage chính
                        const formData = new FormData();
                        formData.append('test_id', testId);
                        formData.append('part', part);
                        formData.append('question_number', questionNumber);
                        formData.append('passage_id', passageId);
                        formData.append('content', content);
                        formData.append('correct_answer', correctAnswer);
                        formData.append('explanation', explanation);
                        formData.append('options', JSON.stringify(options));
                        // Gửi yêu cầu đến API để tạo câu hỏi con mới với liên kết đến passage chính thông qua passageId
                        const response = await fetch('/IS207-UIT/server/index.php?path=/api/questions', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        console.log(`Sub-Question ${questionNumber} API Response:`, {
                            status: response.status,
                            success: result.success,
                            message: result.message,
                            data: result.data,
                            fullResult: result
                        });

                        if (result.success) {
                            created++;
                        } else {
                            showMessage(`Câu ${questionNumber}: ${result.message}`, 'error');
                            errors++;
                        }
                    } catch (error) {
                        showMessage(`Câu ${questionNumber}: ${error.message}`, 'error');
                        errors++;
                    }
                }
            } catch (error) {
                showMessage(`Lỗi submit nhóm câu hỏi`, 'error');
                errors++;
            }

            return {
                created,
                errors
            };
        }
    </script>
    <script src="../js/api.js"></script>

    <?php include('./componants/footer.php'); ?>
</body>

</html>