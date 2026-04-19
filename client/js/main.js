/*Khi load trang sẽ tự động lấy danh sách đề thi từ db*/
document.addEventListener("DOMContentLoaded", function(){
    load_tests();
})

/*Lấy danh sách đề thi và hiển thị lên trang user*/
async function load_tests() {
    try {
        const response = await fetch('../../api/tests');
        if (!response.ok) {
            throw new Error("Could not fetch resource (function in main.js)");
        }

        const test_list = await response.json();
        const tests = test_list.data;
        const MAX_COLS = 4;

        let test_grid = document.querySelector('.test-grid');
        test_grid.innerHTML = "";

        //Mỗi row sẽ có 4 tests
        for (let i = 0; i < tests.length; i += MAX_COLS) {
            let test_row = `<div class="test-row">`;
            for (let j = i; j < i + MAX_COLS && j < tests.length; j++) {
                test_row += `
                        <div class="test-item">
                            <div class="card exam-card">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><b>${tests[j].title}</b></h5>
                                    <div class="test-meta">
                                        <span class="testitem-info">
                                            <span class="far fa-clock mr-1"></span>
                                        </span>
                                        <span class="testitem-info">120 minutes | ${tests[j].total_questions} questions</span>
                                    </div>
                                    <p class="card-text">${tests[j].description}</p>
                                    <a href="./exam.php?uuid=${tests[j].uuid}" class="btn btn-outline-primary mt-auto enter-test">Start test</a>
                                </div>
                            </div>
                        </div>
                `;
            }
            test_row += `</div>`;
            test_grid.innerHTML += test_row;    
        }
    } catch (error) {
        console.error(error);
        document.querySelector(".test-grid").innerHTML = error.message;
    }
}
