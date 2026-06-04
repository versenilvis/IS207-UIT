/**
 * state.js - Quản lý cấu hình và trạng thái ứng dụng
 */

const URL_PARAMS = new URLSearchParams(window.location.search);
const ACTION_PARAM = URL_PARAMS.get('action');
const TEST_ID_PARAM = URL_PARAMS.get('test_id');
const EDIT_MODE_PARAM = URL_PARAMS.get('mode') || 'exam';
const IS_FULL_TEST_EDIT = ACTION_PARAM === 'edit' && TEST_ID_PARAM && ['exam', 'answers'].includes(EDIT_MODE_PARAM);

const PART_CONFIG = {
	1: { name: 'Ảnh', requiresImage: true, requiresAudio: false, requiresContent: false },
	2: { name: 'Câu hỏi ngắn', requiresImage: false, requiresAudio: false, requiresContent: true },
	3: { name: 'Hội thoại', requiresImage: false, requiresAudio: false, requiresContent: true },
	4: { name: 'Độc thoại', requiresImage: false, requiresAudio: false, requiresContent: true },
	5: { name: 'Đọc câu hoàn chỉnh', requiresImage: false, requiresAudio: false, requiresContent: true },
	6: { name: 'Điền từ', requiresImage: false, requiresAudio: false, requiresContent: true },
	7: { name: 'Đọc hiểu', requiresImage: false, requiresAudio: false, requiresContent: true },
};

const AppState = {
	globalBlockCounter: 0,
	globalSubQuestionCounter: 0,
	activePart: '1',
	loadedQuestionIds: new Set(),
	loadedPassageIds: new Set(),
	allTestQuestionNumbers: new Set(),
	savedQuestions: [],
	deletedQuestionIds: new Set(),
	deletedPassageIds: new Set()
};
