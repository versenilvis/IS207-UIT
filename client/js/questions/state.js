/**
 * state.js - Quản lý cấu hình và trạng thái ứng dụng
 */

const URL_PARAMS = new URLSearchParams(window.location.search);
const ACTION_PARAM = URL_PARAMS.get('action');
const TEST_ID_PARAM = URL_PARAMS.get('test_id');

const PART_CONFIG = {
	1: { name: 'Ảnh', requiresImage: true, requiresAudio: false, requiresContent: false },
	2: { name: 'Câu hỏi ngắn', requiresImage: false, requiresAudio: true, requiresContent: true },
	3: { name: 'Hội thoại', requiresImage: false, requiresAudio: true, requiresContent: true },
	4: { name: 'Độc thoại', requiresImage: false, requiresAudio: true, requiresContent: true },
	5: { name: 'Đọc câu hoàn chỉnh', requiresImage: false, requiresAudio: false, requiresContent: true },
	6: { name: 'Điền từ', requiresImage: false, requiresAudio: false, requiresContent: true },
	7: { name: 'Đọc hiểu', requiresImage: false, requiresAudio: false, requiresContent: true },
};

const AppState = {
	globalBlockCounter: 0,
	loadedQuestionIds: new Set(),
	loadedPassageIds: new Set(),
	allTestQuestionNumbers: new Set()
};
