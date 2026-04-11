/**
 * Tích hợp API cho Hệ thống Quản lý Câu hỏi TOEIC
 */

const API_BASE_URL = '/IS207-UIT/server/index.php';

/**
 * Lấy tất cả các bài kiểm tra hoạt động để lựa chọn trong danh sách thả xuống
 * 
 * @returns {Promise<Array>} Mảng các bài kiểm tra với id và tiêu đề
 */
async function getTests() {
    try {
        const response = await fetch(`${API_BASE_URL}?path=/api/tests`, {
            method: 'GET'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch tests');
        }

        return result.data || [];
    } catch (error) {
        console.error('Error fetching tests:', error);
        throw error;
    }
}

/**
 * Lấy các đoạn văn được lọc theo test_id
 * 
 * @param {number} testId - ID bài kiểm tra để lọc
 * @returns {Promise<Array>} Mảng các đoạn văn cho bài kiểm tra
 */
async function getPassages(testId) {
    try {
        if (!testId) {
            throw new Error('test_id is required');
        }

        const response = await fetch(`${API_BASE_URL}?path=/api/passages&test_id=${testId}`, {
            method: 'GET'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch passages');
        }

        return result.data || [];
    } catch (error) {
        console.error('Error fetching passages:', error);
        throw error;
    }
}

/**
 * Tạo một câu hỏi mới với các tệp phương tiện tùy chọn
 * 
 * @param {FormData} formData - Dữ liệu biểu mẫu chứa chi tiết câu hỏi và các tệp
 * @returns {Promise<Object>} {success: bool, question_id: int, message: string}
 */
async function createQuestion(formData) {
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        return result;
    } catch (error) {
        console.error('Error creating question:', error);
        throw error;
    }
}

/**
 * Tạo một đoạn văn mới với phương tiện tùy chọn
 * 
 * @param {FormData} formData - Dữ liệu biểu mẫu chứa chi tiết đoạn văn và các tệp
 * @returns {Promise<Object>} {success: bool, passage_id: int, message: string}
 */
async function createPassage(formData) {
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        return result;
    } catch (error) {
        console.error('Error creating passage:', error);
        throw error;
    }
}

/**
 * Xóa một câu hỏi
 * 
 * @param {number} questionId - ID câu hỏi cần xóa
 * @returns {Promise<Object>} {success: bool, message: string}
 */
async function deleteQuestion(questionId) {
    try {
        if (!questionId) {
            throw new Error('question_id is required');
        }

        const response = await fetch(`${API_BASE_URL}?path=/api/questions/${questionId}`, {
            method: 'DELETE'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        return result;
    } catch (error) {
        console.error('Error deleting question:', error);
        throw error;
    }
}

/**
 * Xóa một đoạn văn
 * 
 * @param {number} passageId - ID đoạn văn cần xóa
 * @returns {Promise<Object>} {success: bool, message: string}
 */
async function deletePassage(passageId) {
    try {
        if (!passageId) {
            throw new Error('passage_id is required');
        }

        const response = await fetch(`${API_BASE_URL}?path=/api/passages/${passageId}`, {
            method: 'DELETE'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        return result;
    } catch (error) {
        console.error('Error deleting passage:', error);
        throw error;
    }
}

/**
 * Lấy tất cả các câu hỏi cho một bài kiểm tra
 * 
 * @param {number} testId - ID bài kiểm tra để lọc
 * @returns {Promise<Array>} Mảng các câu hỏi cho bài kiểm tra
 */
async function getQuestionsByTest(testId) {
    try {
        if (!testId) {
            throw new Error('test_id is required');
        }

        const response = await fetch(`${API_BASE_URL}?path=/api/questions&test_id=${testId}`, {
            method: 'GET'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch questions');
        }

        return result.data || [];
    } catch (error) {
        console.error('Error fetching questions:', error);
        throw error;
    }
}

/**
 * Lấy một câu hỏi duy nhất theo ID
 * 
 * @param {number} questionId - ID câu hỏi
 * @returns {Promise<Object>} Dữ liệu câu hỏi
 */
async function getQuestion(questionId) {
    try {
        if (!questionId) {
            throw new Error('question_id is required');
        }

        const response = await fetch(`${API_BASE_URL}?path=/api/questions/${questionId}`, {
            method: 'GET'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch question');
        }

        return result.data;
    } catch (error) {
        console.error('Error fetching question:', error);
        throw error;
    }
}