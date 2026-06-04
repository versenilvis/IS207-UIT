<?php
$testimonials = [
    ["text" => "Prephub giúp mình tối ưu hóa việc luyện TOEIC thông qua những giáo án cá nhân hóa, lộ trình ngắn gọn hiệu quả.", "name" => "Nguyễn Văn An", "role" => "Sinh viên Bách Khoa", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Nguyen+Van+An"],
    ["text" => "Hệ thống làm đề rất sát thực tế, giao diện mượt mà giúp mình tập trung tối đa khi luyện tập.", "name" => "Lê Thị Bình", "role" => "Nhân viên văn phòng", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Le+Thi+Binh"],
    ["text" => "Giá cả quá hợp lý so với những gì nhận được. Mình cực kỳ hài lòng với gói Premium của Prephub.", "name" => "Trần Hoàng Cường", "role" => "Freelancer", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Tran+Hoang+Cuong"],
    ["text" => "Nhờ có Prephub mà em tăng từ 450 lên 800 chỉ sau 2 tháng. Lộ trình bài bản, dễ hiểu lắm.", "name" => "Phạm Minh Đức", "role" => "Học viên khóa 1", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Pham+Minh+Duc"],
    ["text" => "Kho đề thi cực kỳ đa dạng và luôn cập nhật mới nhất. Giải thích đáp án rất chi tiết và dễ hiểu.", "name" => "Đỗ Thanh Hoa", "role" => "Sinh viên Ngoại Thương", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Do+Thanh+Hoa"],
    ["text" => "Mình đã thử nhiều app nhưng Prephub vẫn là tốt nhất. Cảm giác học rất chuyên nghiệp và hiệu quả.", "name" => "Hoàng Gia Huy", "role" => "Học viên khóa 2", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Hoang+Gia+Huy"],
    ["text" => "Tính năng phân tích điểm yếu theo từng Part thực sự giúp mình cải thiện kỹ năng nhanh chóng.", "name" => "Bùi Tuyết Mai", "role" => "Giáo viên tiếng Anh", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Bui+Tuyet+Mai"],
    ["text" => "Đội ngũ hỗ trợ rất nhiệt tình. Mỗi khi có thắc mắc về bài tập đều được giải đáp nhanh chóng.", "name" => "Ngô Quốc Nam", "role" => "Sinh viên Kinh Tế", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Ngo+Quoc+Nam"],
    ["text" => "Giao diện tối giản nhưng hiện đại, không bị quảng cáo làm phiền như các trang web khác.", "name" => "Trịnh Thu Phương", "role" => "Học viên khóa 3", "image" => "https://api.dicebear.com/7.x/avataaars/svg?seed=Trinh+Thu+Phuong"]
];

$firstColumn = array_slice($testimonials, 0, 3);
$secondColumn = array_slice($testimonials, 3, 3);
$thirdColumn = array_slice($testimonials, 6, 3);

function renderTestimonialColumn($items, $duration, $extraClass = "") {
    echo "<div class='testimonials-wrapper $extraClass' style='--duration: {$duration}s;'>";
    echo "<div class='testimonials-column'>";
    for ($i = 0; $i < 2; $i++) {
        foreach ($items as $t) {
            echo "<div class='feedback-card'>";
            echo "<p class='feedback-text'>\"{$t['text']}\"</p>";
            echo "<div class='feedback-author'>";
            echo "<img src='{$t['image']}' alt='{$t['name']}'>";
            echo "<div>";
            echo "<h6 class='feedback-name'>{$t['name']}</h6>";
            echo "<p class='feedback-role'>{$t['role']}</p>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }
    echo "</div>";
    echo "</div>";
}
?>

<section class="feedback-section testimonials-section" id="feedback">
	<div class="container">
		<div class="row align-items-end mb-5">
			<div class="col-lg-5">
				<span class="section-badge">Đánh giá</span>
				<h2 class="section-title mt-3">Hàng loạt đánh giá tích<br>cực từ các thí sinh.</h2>
			</div>
			<div class="col-lg-7">
				<p class="section-desc">Các học viên đã luyện thi trên Prephub và đạt được những kết quả vượt mong đợi.</p>
			</div>
		</div>

		<div class="testimonials-grid-container">
			<?php renderTestimonialColumn($firstColumn, 25); ?>
			<?php renderTestimonialColumn($secondColumn, 35, "d-none d-md-block"); ?>
			<?php renderTestimonialColumn($thirdColumn, 30, "d-none d-lg-block"); ?>
		</div>
	</div>
</section>
