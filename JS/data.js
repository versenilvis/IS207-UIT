// 1. Danh mục sách (Dùng để hiển thị ở trang chủ)
const bookData = [
    { id: 1, title: "ETS 2019", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-blue", icon: "fas fa-star" },
    { id: 2, title: "ETS 2020", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-amber", icon: "fas fa-book" },
    { id: 3, title: "ETS 2021", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-grey", icon: "fas fa-star" },
    { id: 4, title: "ETS 2022", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-blue", icon: "fas fa-book" },
    { id: 5, title: "ETS 2023", description: "10 Bài Test tiêu chuẩn", colorClass: "bg-pastel-amber", icon: "fas fa-star" },
    { id: 6, title: "ETS 2024", description: "10 Bài Test mới nhất", colorClass: "bg-pastel-grey", icon: "fas fa-book" },
];

// 2. Danh sách đề thi (Bảng tests trong ERD)
const tests = [
    { id: 101, book_id: 1, title: "ETS 2019 - Test 1", duration: 120, total_questions: 200 },
    { id: 102, book_id: 1, title: "ETS 2019 - Test 2", duration: 120, total_questions: 200 },
    { id: 103, book_id: 1, title: "ETS 2019 - Test 3", duration: 120, total_questions: 200 },
    { id: 104, book_id: 1, title: "ETS 2019 - Test 4", duration: 120, total_questions: 200 },
    { id: 105, book_id: 1, title: "ETS 2019 - Test 5", duration: 120, total_questions: 200 },
    { id: 106, book_id: 1, title: "ETS 2019 - Test 6", duration: 120, total_questions: 200 },
    { id: 107, book_id: 1, title: "ETS 2019 - Test 7", duration: 120, total_questions: 200 },
    { id: 108, book_id: 1, title: "ETS 2019 - Test 8", duration: 120, total_questions: 200 },
    { id: 109, book_id: 1, title: "ETS 2019 - Test 9", duration: 120, total_questions: 200 },
    { id: 110, book_id: 1, title: "ETS 2019 - Test 10", duration: 120, total_questions: 200 },
    { id: 201, book_id: 2, title: "ETS 2020 - Test 1", duration: 120, total_questions: 200 },
    { id: 202, book_id: 2, title: "ETS 2020 - Test 2", duration: 120, total_questions: 200 },
    { id: 203, book_id: 2, title: "ETS 2020 - Test 3", duration: 120, total_questions: 200 },
    { id: 204, book_id: 2, title: "ETS 2020 - Test 4", duration: 120, total_questions: 200 },
    { id: 205, book_id: 2, title: "ETS 2020 - Test 5", duration: 120, total_questions: 200 },
    { id: 206, book_id: 2, title: "ETS 2020 - Test 6", duration: 120, total_questions: 200 },
    { id: 207, book_id: 2, title: "ETS 2020 - Test 7", duration: 120, total_questions: 200 },
    { id: 208, book_id: 2, title: "ETS 2020 - Test 8", duration: 120, total_questions: 200 },
    { id: 209, book_id: 2, title: "ETS 2020 - Test 9", duration: 120, total_questions: 200 },
    { id: 210, book_id: 2, title: "ETS 2020 - Test 10", duration: 120, total_questions: 200 },
    { id: 301, book_id: 3, title: "ETS 2021 - Test 1", duration: 120, total_questions: 200 },
    { id: 302, book_id: 3, title: "ETS 2021 - Test 2", duration: 120, total_questions: 200 },
    { id: 303, book_id: 3, title: "ETS 2021 - Test 3", duration: 120, total_questions: 200 },
    { id: 304, book_id: 3, title: "ETS 2021 - Test 4", duration: 120, total_questions: 200 },
    { id: 305, book_id: 3, title: "ETS 2021 - Test 5", duration: 120, total_questions: 200 },
    { id: 306, book_id: 3, title: "ETS 2021 - Test 6", duration: 120, total_questions: 200 },
    { id: 307, book_id: 3, title: "ETS 2021 - Test 7", duration: 120, total_questions: 200 },
    { id: 308, book_id: 3, title: "ETS 2021 - Test 8", duration: 120, total_questions: 200 },
    { id: 309, book_id: 3, title: "ETS 2021 - Test 9", duration: 120, total_questions: 200 },
    { id: 310, book_id: 3, title: "ETS 2021 - Test 10", duration: 120, total_questions: 200 },
    { id: 401, book_id: 4, title: "ETS 2022 - Test 1", duration: 120, total_questions: 200 },
    { id: 402, book_id: 4, title: "ETS 2022 - Test 2", duration: 120, total_questions: 200 },
    { id: 403, book_id: 4, title: "ETS 2022 - Test 3", duration: 120, total_questions: 200 },
    { id: 404, book_id: 4, title: "ETS 2022 - Test 4", duration: 120, total_questions: 200 },
    { id: 405, book_id: 4, title: "ETS 2022 - Test 5", duration: 120, total_questions: 200 },
    { id: 406, book_id: 4, title: "ETS 2022 - Test 6", duration: 120, total_questions: 200 },
    { id: 407, book_id: 4, title: "ETS 2022 - Test 7", duration: 120, total_questions: 200 },
    { id: 408, book_id: 4, title: "ETS 2022 - Test 8", duration: 120, total_questions: 200 },
    { id: 409, book_id: 4, title: "ETS 2022 - Test 9", duration: 120, total_questions: 200 },
    { id: 410, book_id: 4, title: "ETS 2022 - Test 10", duration: 120, total_questions: 200 },
    { id: 501, book_id: 5, title: "ETS 2023 - Test 1", duration: 120, total_questions: 200 },
    { id: 502, book_id: 5, title: "ETS 2023 - Test 2", duration: 120, total_questions: 200 },
    { id: 503, book_id: 5, title: "ETS 2023 - Test 3", duration: 120, total_questions: 200 },
    { id: 504, book_id: 5, title: "ETS 2023 - Test 4", duration: 120, total_questions: 200 },
    { id: 505, book_id: 5, title: "ETS 2023 - Test 5", duration: 120, total_questions: 200 },
    { id: 506, book_id: 5, title: "ETS 2023 - Test 6", duration: 120, total_questions: 200 },
    { id: 507, book_id: 5, title: "ETS 2023 - Test 7", duration: 120, total_questions: 200 },
    { id: 508, book_id: 5, title: "ETS 2023 - Test 8", duration: 120, total_questions: 200 },
    { id: 509, book_id: 5, title: "ETS 2023 - Test 9", duration: 120, total_questions: 200 },
    { id: 510, book_id: 5, title: "ETS 2023 - Test 10", duration: 120, total_questions: 200 },
    { id: 601, book_id: 6, title: "ETS 2024 - Test 1", duration: 120, total_questions: 200 },
    { id: 602, book_id: 6, title: "ETS 2024 - Test 2", duration: 120, total_questions: 200 },
    { id: 603, book_id: 6, title: "ETS 2024 - Test 3", duration: 120, total_questions: 200 },
    { id: 604, book_id: 6, title: "ETS 2024 - Test 4", duration: 120, total_questions: 200 },
    { id: 605, book_id: 6, title: "ETS 2024 - Test 5", duration: 120, total_questions: 200 },
    { id: 606, book_id: 6, title: "ETS 2024 - Test 6", duration: 120, total_questions: 200 },
    { id: 607, book_id: 6, title: "ETS 2024 - Test 7", duration: 120, total_questions: 200 },
    { id: 608, book_id: 6, title: "ETS 2024 - Test 8", duration: 120, total_questions: 200 },
    { id: 609, book_id: 6, title: "ETS 2024 - Test 9", duration: 120, total_questions: 200 },
    { id: 610, book_id: 6, title: "ETS 2024 - Test 10", duration: 120, total_questions: 200 },
];

// 3. Câu hỏi mẫu 7 part (Bảng questions + options trong ERD)
const sampleQuestions = [
    // PART 1: PHOTO DESCRIPTION (Có Ảnh + Audio + 4 đáp án)
    {
        id: 1,
        test_id: 101,
        part: 1,
        question_number: 1,
        image_url: "assets/images/part1_01.jpg",
        audio_url: "assets/audio/part1_01.mp3",
        content: "Look at the picture and choose the best description.",
        options: [
            { label: "A", text: "They're working at their desks." },
            { label: "B", text: "They're standing in a hallway." },
            { label: "C", text: "They're walking out of a building." },
            { label: "D", text: "They're sitting in a cafeteria." }
        ],
        correct_answer: "A"
    },

    // PART 2: QUESTION-RESPONSE (Chỉ có Audio + 3 đáp án A, B, C)
    {
        id: 2,
        test_id: 101,
        part: 2,
        question_number: 7,
        audio_url: "assets/audio/part2_07.mp3",
        content: "(Listen to the audio and choose the best response)",
        options: [
            { label: "A", text: "In the conference room." },
            { label: "B", text: "Yes, at three o'clock." },
            { label: "C", text: "Mr. Kim handled that." }
        ],
        correct_answer: "C"
    },

    // PART 3: CONVERSATIONS (Audio + Câu hỏi chữ + 4 đáp án)
    {
        id: 3,
        test_id: 101,
        part: 3,
        question_number: 32,
        audio_url: "assets/audio/part3_32.mp3",
        content: "What does the woman imply about the schedule?",
        options: [
            { label: "A", text: "It has been cancelled." },
            { label: "B", text: "It needs to be revised." },
            { label: "C", text: "It is too tight." },
            { label: "D", text: "It was sent by email." }
        ],
        correct_answer: "B"
    },

    // PART 4: SHORT TALKS (Tương tự Part 3 nhưng là độc thoại)
    {
        id: 4,
        test_id: 101,
        part: 4,
        question_number: 71,
        audio_url: "assets/audio/part4_71.mp3",
        content: "What is the purpose of the announcement?",
        options: [
            { label: "A", text: "To introduce a new employee" },
            { label: "B", text: "To announce a building closure" },
            { label: "C", text: "To describe a policy change" },
            { label: "D", text: "To advertise a grand opening" }
        ],
        correct_answer: "D"
    },

    // PART 5: INCOMPLETE SENTENCES (Chỉ có chữ + 4 đáp án)
    {
        id: 5,
        test_id: 101,
        part: 5,
        question_number: 101,
        content: "The new software update _______ the system speed significantly.",
        options: [
            { label: "A", text: "improves" },
            { label: "B", text: "improved" },
            { label: "C", text: "improving" },
            { label: "D", text: "improvement" }
        ],
        correct_answer: "B"
    },

    // PART 6: TEXT COMPLETION (Có đoạn văn ngắn + Điền từ)
    {
        id: 6,
        test_id: 101,
        part: 6,
        passage_id: 10, // Liên kết với bảng Passages trong ERD
        question_number: 131,
        content: "Please _______ your application by the end of the week.",
        options: [
            { label: "A", text: "submit" },
            { label: "B", text: "submission" },
            { label: "C", text: "submitting" },
            { label: "D", text: "submitted" }
        ],
        correct_answer: "A"
    },

    // PART 7: READING COMPREHENSION (Có đoạn văn dài + Câu hỏi)
    {
        id: 7,
        test_id: 101,
        part: 7,
        passage_id: 11, // Liên kết với bảng Passages
        question_number: 147,
        content: "What is indicated about the delivery?",
        options: [
            { label: "A", text: "It will be delayed." },
            { label: "B", text: "It is free for members." },
            { label: "C", text: "It requires a signature." },
            { label: "D", text: "It has already arrived." }
        ],
        correct_answer: "C"
    }
];