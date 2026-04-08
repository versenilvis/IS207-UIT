## TOEIC Format Overview

| Part | Mô tả | Câu hỏi | Âm thanh | Hình ảnh | Đoạn văn | Loại |
|------|-------|---------|---------|---------|---------|------|
| **1** | Ảnh | 10 | Có (optional) | Bắt buộc | Không | Câu đơn |
| **2** | Câu hỏi ngắn | 30 | Bắt buộc | Không | Không | Câu đơn |
| **3** | Hội thoại (2-3 người) | 30 | Bắt buộc | Không | Có (conversation) | Nhóm (3 câu/group) |
| **4** | Độc thoại | 20 | Bắt buộc | Không | Có (monologue) | Nhóm (2-3 câu/group) |
| **5** | Đọc câu hoàn chỉnh | 40 | Không | Không | Không | Câu đơn |
| **6** | Điền từ vào câu | 12 | Không | Không | Không | Câu đơn |
| **7** | Đọc hiểu | 28 | Không | Có (optional) | Bắt buộc | Nhóm (2-5 câu/passage) |

**Tổng: 200 câu**

---

## Implementation Steps

### Step 1: Backend - Passage Model (NEW)
**File:** `server/models/passage.php`
- Implement `create($data)` method to insert passage with `test_id`, `content`, `audio_url`, `image_url`
- **Ngôn ngữ:** Passages được sử dụng chung cho nhóm câu hỏi (Part 3, 4, 7)
- Fields cần validate:
  - `test_id`: NOT NULL, FK to tests
  - `content`: TEXT (Part 6, 7 có nội dung)
  - `audio_url`: VARCHAR (Part 3, 4 có audio)
  - `image_url`: VARCHAR (Part 1, 7 có thể có ảnh)

### Step 2: Backend - Question Model
**File:** `server/models/question.php`
- Implement `create($data)` method insert question with TOEIC-specific fields:
  - `test_id`: NOT NULL (FK)
  - `passage_id`: NULLABLE (FK - for grouped questions)
  - `part`: TINYINT 1-7
  - `question_number`: INT (1-200)
  - `content`: TEXT (nội dung câu hỏi - có thể null cho Part 1)
  - `audio_url`: VARCHAR (âm thanh riêng - Part 1, 2)
  - `image_url`: VARCHAR (hình ảnh riêng - Part 1)
  - `correct_answer`: CHAR(1) A/B/C/D
  - `explanation`: TEXT (giải thích - tùy chọn)
- Implement `addQuestionOptions($questionId, $options)` - **DEPRECATED**: TOEIC không dùng bảng options riêng
- **Lưu ý:** Đáp án A/B/C/D là text trong form, lưu direct vào DB, không tạo bảng options

### Step 3: Backend - File Upload Handler (SHARED UTILITY)
**File:** `server/utils/fileHandler.php`
- Implement `uploadFile($file, $type)` - generic file upload handler
  - `$type` can be: 'audio', 'image', 'json', 'excel'
  - `$type = 'audio'`: MP3, WAV, OGG (max 50MB)
  - `$type = 'image'`: JPG, PNG, GIF (max 5MB)
- Implement `validateAudio($file)` - validate audio
- Implement `validateImage($file)` - validate image
- Implement `validateJsonFile($file)` - validate JSON format, max 10MB
- Implement `validateExcelFile($file)` - validate Excel format (xlsx), max 10MB
- Implement `saveUploadedFile($file, $directory)` - save with UUID filename to `/uploads/{directory}/`
- Return relative path (e.g., `/uploads/audio/uuid-123.mp3`)
- **Note:** This utility will be reused in Week 3 for import feature

### Step 4: Backend - Validation Functions
**File:** `server/utils/validator.php`
- Implement `validateQuestionContent($content, $part)` - check length (5-5000 chars), may be null for Part 1
- Implement `validateCorrectAnswer($answer)` - check is A/B/C/D exactly
- Implement `validateOptions($options)` - check exactly 4 options (A, B, C, D), each 1-500 chars
- Implement `validateTestExists($db, $testId)` - check exists and is_active
- Implement `validateToeicPart($part)` - check 1-7 inclusive
- Implement `validateQuestionNumber($questionNumber, $part)` - check positive integer
- Implement `validatePassageExists($db, $passageId, $testId)` - check exists in same test
- Implement `validateAudioUrl($url)` - check valid URL or relative path
- Implement `validateImageUrl($url)` - check valid URL or relative path
- Implement `validateExplanation($explanation)` - check length limit (max 5000 chars, optional)
- **Part-specific logic:**
  - Part 1: `image_url` bắt buộc, `content` NULL OK
  - Part 2-4: `audio_url` bắt buộc, `content` bắt buộc
  - Part 5-6: `content` bắt buộc, không cần media
  - Part 7: `passage_id` bắt buộc, `content` bắt buộc

### Step 5: Backend - API Routes Organization
**File:** `server/routes/api.php` (NEW)
- Define all API routes for questions and passages:
  - `POST /api/questions` - create question
  - `GET /api/questions` - get all questions (with filters)
  - `GET /api/questions/:id` - get single question
  - `PUT /api/questions/:id` - update question
  - `DELETE /api/questions/:id` - delete question
  - `GET /api/tests` - get all tests (for dropdown)
  - `GET /api/passages` - get passages (filtered by test_id)
  - `POST /api/passages` - create passage
  - `DELETE /api/passages/:id` - delete passage

**File:** `server/index.php` (UPDATE)
- Simple router that includes `routes/api.php`
- Handle method & path matching
- Return 404 if route not found

### Step 6: Backend - Controller Logic
**File:** `server/controllers/test-controller.php`
- Implement `createQuestion()` method:
  1. Validate all inputs using Validator class
  2. If `passage_id` provided: validate passage exists
  3. If audio file uploaded: use FileHandler to upload and get URL
  4. If image file uploaded: use FileHandler to upload and get URL
  5. Call Question model `create()` with validated data
  6. Return success/error JSON response (include question_id on success)

### Step 7: Frontend - Admin Form HTML
**File:** `client/html/adminPanel.html` (NEW - separate from testFormat.html)
- Create form for single question input:
  - **Test selection dropdown** (REQUIRED)
    - Load list from `GET /api/tests` (admin can see all tests)
  - **Part selection dropdown** (1-7)
    - Change part dynamically updates field requirements
  - **Question number input** (numeric, 1-200)
  - **Passage selection dropdown** (OPTIONAL, visible only for Part 3, 4, 7)
    - Load passages filtered by selected test
  - **Question content textarea** (REQUIRED for Part 2-7, OPTIONAL for Part 1)
    - Placeholder: "Nhập nội dung câu hỏi..."
  - **4 option input fields** (A, B, C, D - REQUIRED)
  - **Correct answer radio buttons** (A/B/C/D)
  - **Audio file upload** (OPTIONAL, but REQUIRED for Part 2-4)
    - Accept: `.mp3, .wav, .ogg`
    - Preview audio player
  - **Image file upload** (OPTIONAL, but REQUIRED for Part 1)
    - Accept: `.jpg, .png, .gif`
    - Preview image
  - **Submit button** (Lưu câu hỏi)
  - **Reset button** (Nhập lại)
- Add success/error message display area

### Step 8: Frontend - Dynamic Form Validation
**File:** `client/js/admin.js`
- Implement `initializeAdminForm()`:
  - Load all tests on page load(dropdown chỉ cần tên test)
  - Add event listener to part dropdown
- Implement `onPartChange(part)`:
  - Show/hide fields based on part:
    - Part 1: Show image upload (bắt buộc), hide passage if no Part 7
    - Part 2: Show audio upload (bắt buộc), show question content (bắt buộc)
    - Part 3-4: Show audio + passage dropdown (bắt buộc)
    - Part 5-6: Hide media uploads
    - Part 7: Show passage dropdown (bắt buộc), optional image
  - Update field validation rules dynamically
- Implement form submission handler:
  - Client-side validation before sending
  - Use FormData for files + data
  - Show loading state
  - Call API to save question
  - Display success/error message
  - Reset form after successful save

### Step 9: Frontend - API Integration
**File:** `client/js/api.js`
- Add `getTests()` - GET `/api/tests` (return array of active tests)
- Add `getPassages(testId)` - GET `/api/passages?test_id=` (filter by test)
- Add `createQuestion(formData)` - POST `/api/questions`
  - Use FormData to send multipart/form-data
  - Handle file uploads
  - Return {success: bool, question_id: int, message: string}

---

## Key Differences from Generic Quiz Format

### TOEIC Structure
1. **Passages (Nhóm văn bản/audio):** Tách riêng để share với nhiều câu hỏi
   - Part 3: Conversation (1 audio + 3 câu hỏi)
   - Part 4: Monologue (1 audio + 2-3 câu hỏi)
   - Part 7: Reading (1 passage + 2-5 câu hỏi)

2. **Question Numbers:** Tuần tự 1-200 qua toàn bộ test, not reset per part

3. **Media Handling:** Part-specific requirements
   - Part 1: Image bắt buộc
   - Part 2-4: Audio bắt buộc
   - Part 7: Passage text bắt buộc

4. **Options:** Luôn là 4 options A/B/C/D (text, không có media trong options)

### Implementation Priority
1. **Step 1-6:** Backend MVP (Question + Passage models, validators, controller)
2. **Step 7-9:** Frontend (Admin form + API integration)
3. **Week 3+:** Import feature reuses FileHandler từ Step 3

---

## Testing Checklist for Week 1

- [ ] Create Question: Part 1 with required image
- [ ] Create Question: Part 2 with required audio + content
- [ ] Create Question: Part 3 with passage + audio + 3 questions
- [ ] Create Question: Part 5 (text only, no media)
- [ ] Create Question: Part 7 with passage + 2-5 questions
- [ ] Validate: Missing required fields should return error
- [ ] Validate: Invalid part number should return error
- [ ] Validate: Audio/image upload with wrong format should reject
- [ ] File saving: Check UUID filenames saved correctly in `/uploads/`
- [ ] Database: Verify all fields saved correctly with correct test_id

## Success Criteria
- [ ] Question can be created with content and 4 options
- [ ] Correct answer is properly selected and stored
- [ ] Audio file can be uploaded and URL saved to database
- [ ] Image file can be uploaded and URL saved to database
- [ ] Test assignment is required and validated
- [ ] Part and question number are properly recorded
- [ ] Data is correctly saved to `questions` and `options` tables
- [ ] Validation catches invalid input
- [ ] User receives success/error feedback
- [ ] Admin can create multiple questions in succession

## Testing Checklist
- [ ] Test with valid question data (no media)
- [ ] Test with audio file upload
- [ ] Test with image file upload
- [ ] Test with both audio and image
- [ ] Test with missing required fields
- [ ] Test with invalid file formats
- [ ] Test with oversized files
- [ ] Test with invalid correct answer value
- [ ] Test with missing options
- [ ] Test without selecting test (should fail)
- [ ] Verify database records are created correctly
- [ ] Test error handling and user feedback