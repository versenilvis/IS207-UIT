<!-- phân hệ: doanh thu -->
<section id="section-revenue" class="section-content <?php echo $section === 'revenue' ? 'active' : ''; ?>">
    <div class="page-header">
        <div class="page-title-container">
            <div class="breadcrumbs">
                <span>Dòng tiền</span>
                <i class="bx bx-chevron-right"></i>
                <span>Doanh thu</span>
            </div>
            <h1 class="page-title">Doanh Thu & Lịch Sử Giao Dịch</h1>
        </div>
    </div>

    <div class="stats-grid grid-4">
        <div class="stat-card theme-green">
            <div class="stat-info">
                <h3>Doanh thu tháng này</h3>
                <p id="revenue-stat-month">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-trending-up"></i>
            </div>
        </div>
        <div class="stat-card theme-blue">
            <div class="stat-info">
                <h3>Doanh thu mọi thời gian</h3>
                <p id="revenue-stat-alltime">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-chart-line"></i>
            </div>
        </div>
        <div class="stat-card theme-orange">
            <div class="stat-info">
                <h3>Giao dịch thành công</h3>
                <p id="revenue-stat-success-count">...</p>
            </div>
            <div class="stat-icon">
                <i class="bx bx-badge-check"></i>
            </div>
        </div>
        <div class="stat-card theme-red">
            <div class="stat-info">
                <h3>Đã hoàn tiền</h3>
                <p id="revenue-stat-refund-amount">...</p>
                <span id="revenue-stat-refund-count" style="font-size: 12px; color: #b91c1c; font-weight: 500; display: block; margin-top: 4px;">...</span>
            </div>
            <div class="stat-icon">
                <i class="bx bx-refresh-ccw"></i>
            </div>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="revenueChart"></canvas>
    </div>

    <div class="table-container" style="overflow-x: auto; margin-bottom: 32px;">
        <h2 style="font-size: 15px; font-weight: 600; padding: 16px 20px 0 20px;">Phân tích doanh thu theo gói dịch vụ</h2>
        <table>
            <thead>
                <tr>
                    <th>Gói dịch vụ</th>
                    <th>Lượt mua thành công</th>
                    <th>Doanh thu gộp</th>
                    <th>Lượt hoàn tiền</th>
                    <th>Tổng hoàn tiền</th>
                    <th>Doanh thu ròng</th>
                    <th>Tỷ lệ hoàn tiền</th>
                </tr>
            </thead>
            <tbody id="revenueBreakdownTableBody">
                <!-- hiển thị bằng JS -->
            </tbody>
        </table>
    </div>

    <div class="table-container transaction-history-container" style="overflow-x: auto;">
        <h2 style="font-size: 15px; font-weight: 600; padding: 16px 20px 0 20px;">Lịch sử giao dịch thành công</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã giao dịch</th>
                    <th>Khách hàng</th>
                    <th>Gói mua</th>
                    <th>Số tiền</th>
                    <th>Thời hạn</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody id="transactionTableBody">
                <!-- hiển thị bằng JS -->
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper" id="transactions-pagination">
        <!-- phân trang giao dịch -->
    </div>
</section>
