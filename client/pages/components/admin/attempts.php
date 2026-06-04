<!-- phân hệ: lượt thi -->
<section id="section-attempts" class="section-content <?php echo $section === 'attempts' ? 'active' : ''; ?>">
    <div class="page-header">
        <div class="page-title-container">
            <div class="breadcrumbs">
                <span>Quản lý</span>
                <i class="bx bx-chevron-right"></i>
                <span>Lượt thi</span>
            </div>
            <h1 class="page-title">Lịch Sử Làm Bài</h1>
        </div>
    </div>

    <div class="table-container" style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Người làm</th>
                    <th>Bài thi</th>
                    <th>Số câu đúng</th>
                    <th>Tổng điểm</th>
                    <th>Thời gian làm</th>
                    <th>Tiến trình thi thử</th>
                    <th>Ngày nộp</th>
                </tr>
            </thead>
            <tbody id="attemptTableBody">
                <!-- hiển thị bằng JS -->
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper" id="attempts-pagination">
        <!-- phân trang lượt thi -->
    </div>
</section>
