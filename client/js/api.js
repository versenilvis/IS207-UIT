/**
 * API Integration for TOEIC Question Management System
 */

const API_BASE_URL = '/IS207-UIT/server/index.php';

/**
 * Get all active tests for dropdown selection
 * 
 * @returns {Promise<Array>} Array of tests with id and title
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
 * Get passages filtered by test_id
 * 
 * @param {number} testId - The test ID to filter by
 * @returns {Promise<Array>} Array of passages for the test
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
 * Create a new question with optional media files
 * 
 * @param {FormData} formData - Form data containing question details and files
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
 * Create a new passage with optional media
 * 
 * @param {FormData} formData - Form data containing passage details and files
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
 * Delete a question
 * 
 * @param {number} questionId - The question ID to delete
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
 * Delete a passage
 * 
 * @param {number} passageId - The passage ID to delete
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
 * Get all questions for a test
 * 
 * @param {number} testId - The test ID to filter by
 * @returns {Promise<Array>} Array of questions for the test
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
 * Get a single question by ID
 * 
 * @param {number} questionId - The question ID
 * @returns {Promise<Object>} Question data
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