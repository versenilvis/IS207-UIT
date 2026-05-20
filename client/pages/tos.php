<?php
session_start();
// tos.php - Trang điều khoản & chính sách Prephub
$sections = [
	'gioi-thieu' => 'Giới thiệu',
	'lien-he' => 'Liên hệ',
	'bao-mat' => 'Chính sách bảo mật',
	'su-dung' => 'Điều khoản sử dụng',
	'huong-dan' => 'Hướng dẫn sử dụng',
	'thanh-toan' => 'Hướng dẫn thanh toán',
	'giao-dich' => 'Điều kiện giao dịch',
	'khieu-nai' => 'Phản hồi & khiếu nại',
];
$active = $_GET['tab'] ?? 'gioi-thieu';
if (!array_key_exists($active, $sections))
	$active = 'gioi-thieu';

function renderToc($active)
{
	$toc = [];
	if ($active === 'gioi-thieu') {
		$toc = ['Sứ mệnh', 'Những gì chúng tôi cung cấp', 'Đội ngũ phát triển', 'Cam kết của chúng tôi'];
	} elseif ($active === 'lien-he') {
		$toc = ['Thông tin liên hệ', 'Các trường hợp thường gặp', 'Thời gian phản hồi'];
	} elseif ($active === 'bao-mat') {
		$toc = ['1. Thông tin thu thập', '2. Mục đích sử dụng', '3. Bên thứ ba', '4. Cookie và Theo dõi', '5. Bảo mật dữ liệu', '6. Quyền người dùng', '7. Thay đổi chính sách'];
	} elseif ($active === 'su-dung') {
		$toc = ['1. Quyền hạn truy cập', '2. Trách nhiệm tài khoản', '3. Sở hữu trí tuệ', '4. Quy tắc ứng xử', '5. Miễn trừ trách nhiệm', '6. Chấm dứt dịch vụ'];
	} elseif ($active === 'huong-dan') {
		$toc = ['Bước 1 - Đăng ký và Thiết lập', 'Bước 2 - Lựa chọn lộ trình', 'Bước 3 - Quy trình học và thi', 'Bước 4 - Phân tích và Theo dõi', 'Nâng cấp Premium', 'Xử lý sự cố thường gặp'];
	} elseif ($active === 'thanh-toan') {
		$toc = ['Chi tiết các gói dịch vụ', 'Cổng thanh toán hỗ trợ', 'Quy trình giao dịch an toàn', 'Xác thực và Hoá đơn', 'Quản lý gia hạn', 'Xử lý lỗi thanh toán'];
	} elseif ($active === 'giao-dich') {
		$toc = ['1. Điều kiện chung', '2. Xác nhận và Kích hoạt', '3. Chính sách hoàn tiền chi tiết', '4. Quy trình yêu cầu hoàn trả', '5. Thay đổi biểu giá', '6. Nghĩa vụ thuế', '7. Giải quyết tranh chấp'];
	} elseif ($active === 'khieu-nai') {
		$toc = ['Kênh tiếp nhận phản hồi', 'Quy trình xử lý chuẩn', 'Bảng tra cứu giải quyết nhanh', 'Cam kết chất lượng phục vụ'];
	}

	if (empty($toc))
		return '';

	$html = '<div class="toc-box">';
	$html .= '<div class="toc-header" onclick="toggleToc()"><span>Mục lục</span><i class="bx bx-chevron-up" id="toc-icon"></i></div>';
	$html .= '<div class="toc-body" id="toc-body"><ul class="toc-list">';
	foreach ($toc as $index => $item) {
		$id = 'sec-' . $index;
		$html .= '<li><a href="#' . $id . '" class="toc-link">' . $item . '</a></li>';
	}
	$html .= '</ul></div></div>';
	return $html;
}

function anchorId($index)
{
	return 'sec-' . $index;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= $sections[$active] ?> - Prephub</title>
	<?php include './components/metadata.php'; ?>
	<link rel="stylesheet" href="../styles/tos.css">
</head>

<body>

	<?php $navbarMode = 'light'; $scrollThreshold = 20; include './components/navbar.php'; ?>

	<main class="tos-page">
		<div class="tos-container">

			<!-- SIDEBAR NAV -->
			<aside class="tos-sidebar">
				<nav class="sticky-nav">
					<ul class="nav-links">
						<?php foreach ($sections as $key => $label): ?>
							<li>
								<a href="?tab=<?= $key ?>" class="<?= $active === $key ? 'active' : '' ?>">
									<?= $label ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			</aside>

			<!-- MAIN CONTENT -->
			<article class="tos-content">

				<h1 class="page-title"><?= $sections[$active] ?></h1>
				<p class="last-updated">Cập nhật lần cuối: 15/05/2026</p>

				<?= renderToc($active) ?>

				<div class="tos-body-text">
					<?php if ($active === 'gioi-thieu'): ?>
						<p><strong>Prephub</strong> là nền tảng luyện thi TOEIC trực tuyến hàng đầu, được xây dựng bởi đội
							ngũ tâm huyết với mục tiêu giúp người học Việt Nam chinh phục bài thi TOEIC một cách hiệu quả
							nhất thông qua công nghệ hiện đại</p>

						<h2 id="<?= anchorId(0) ?>">Sứ mệnh</h2>
						<p>Chúng tôi tin rằng mọi người đều có thể đạt được mục tiêu TOEIC của mình nếu có lộ trình học tập
							đúng đắn. Sứ mệnh của Prephub là:</p>
						<ul>
							<li>Phá bỏ rào cản chi phí trong việc tiếp cận tài liệu luyện thi chất lượng cao</li>
							<li>Ứng dụng công nghệ AI để cá nhân hoá lộ trình học cho từng cá nhân</li>
							<li>Xây dựng cộng đồng học tập văn minh, hỗ trợ lẫn nhau cùng tiến bộ</li>
							<li>Cập nhật liên tục những thay đổi mới nhất từ IIG để học viên luôn sẵn sàng cho kỳ thi thực
								tế</li>
						</ul>

						<h2 id="<?= anchorId(1) ?>">Những gì chúng tôi cung cấp</h2>
						<ul>
							<li>Hơn 2.000 bài tập và 50+ đề thi thực hành đầy đủ 7 Part, cập nhật theo định dạng mới nhất
							</li>
							<li>Hệ thống giải thích đáp án chi tiết, bao gồm cả từ vựng và cấu trúc ngữ pháp quan trọng
								trong câu</li>
							<li>Lộ trình học 45 ngày được thiết kế khoa học, chia nhỏ mục tiêu giúp giảm áp lực cho người
								học</li>
							<li>Công cụ phân tích điểm yếu (Weakness Analysis) tự động nhận diện các chủ điểm ngữ pháp bạn
								hay sai</li>
							<li>Môi trường thi thử giống 99% so với thi thực tế trên máy tính tại các trung tâm khảo thí
							</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">Đội ngũ phát triển</h2>
						<p>Prephub tập hợp những kỹ sư phần mềm xuất sắc và các chuyên gia ngôn ngữ đạt điểm số TOEIC 990.
							Chúng tôi không ngừng nghiên cứu các phương pháp sư phạm hiện đại để tích hợp vào nền tảng, đảm
							bảo mỗi phút học viên bỏ ra trên Prephub đều mang lại giá trị thực tế</p>

						<h2 id="<?= anchorId(3) ?>">Cam kết của chúng tôi</h2>
						<ul>
							<li><strong>Chất lượng:</strong> Nội dung đề thi được kiểm duyệt nghiêm ngặt về độ chính xác và
								tính thực tiễn</li>
							<li><strong>Minh bạch:</strong> Thông báo rõ ràng về các gói dịch vụ, không có phí ẩn hoặc gia
								hạn gây hiểu lầm</li>
							<li><strong>Lắng nghe:</strong> Mọi đóng góp từ người dùng đều được ghi nhận và phản hồi trong
								thời gian sớm nhất</li>
							<li><strong>Bảo mật:</strong> Đặt an toàn thông tin người dùng lên hàng đầu với các tiêu chuẩn
								mã hoá cao nhất</li>
						</ul>

					<?php elseif ($active === 'lien-he'): ?>
						<p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy liên hệ qua một trong các kênh dưới đây</p>

						<h2 id="<?= anchorId(0) ?>">Thông tin liên hệ</h2>
						<table>
							<tr>
								<th>Email hỗ trợ</th>
								<td>support@prephub.up.railway.app</td>
							</tr>
							<tr>
								<th>Email hợp tác / kinh doanh</th>
								<td>contact@prephub.up.railway.app</td>
							</tr>
							<tr>
								<th>Fanpage Facebook</th>
								<td>facebook.com/prephub.up.railway.app</td>
							</tr>
							<tr>
								<th>Giờ làm việc</th>
								<td>Thứ 2 - Thứ 7 · 8:00 - 21:00</td>
							</tr>
							<tr>
								<th>Địa chỉ</th>
								<td>Thành phố Hồ Chí Minh, Việt Nam</td>
							</tr>
						</table>

						<h2 id="<?= anchorId(1) ?>">Các trường hợp thường gặp</h2>
						<ul>
							<li><strong>Hỗ trợ tài khoản & đăng nhập:</strong> support@prephub.up.railway.app</li>
							<li><strong>Vấn đề thanh toán / hoàn tiền:</strong> billing@prephub.up.railway.app</li>
							<li><strong>Báo lỗi nội dung đề thi:</strong> ghi rõ tên đề + số câu trong email gửi về
								support@prephub.up.railway.app</li>
							<li><strong>Hợp tác truyền thông / affiliate:</strong> partner@prephub.up.railway.app</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">Thời gian phản hồi</h2>
						<p>Chúng tôi cố gắng phản hồi mọi email trong vòng <strong>24 giờ làm việc</strong>. Với các trường
							hợp khẩn cấp liên quan đến thanh toán, thời gian xử lý tối đa là <strong>48 giờ</strong></p>

					<?php elseif ($active === 'bao-mat'): ?>
						<p>Prephub coi trọng quyền riêng tư của bạn. Chính sách này mô tả chi tiết cách chúng tôi xử lý dữ
							liệu để mang lại trải nghiệm học tập tốt nhất</p>

						<h2 id="<?= anchorId(0) ?>">1. Thông tin thu thập</h2>
						<ul>
							<li><strong>Thông tin định danh:</strong> Họ tên, email, ảnh đại diện khi bạn đăng ký qua
								Google/Facebook hoặc trực tiếp</li>
							<li><strong>Dữ liệu học tập:</strong> Điền số từng bài thi, thời gian hoàn thành, các câu hỏi đã
								đánh dấu, lịch sử làm bài và tiến trình lộ trình</li>
							<li><strong>Dữ liệu thiết bị:</strong> Địa chỉ IP, loại trình duyệt, độ phân giải màn hình để
								tối ưu hiển thị giao diện thi</li>
							<li><strong>Dữ liệu thanh toán:</strong> Chúng tôi chỉ nhận kết quả giao dịch (Thành công/Thất
								bại) từ cổng thanh toán. Thông tin thẻ của bạn được bảo mật tuyệt đối bởi đối tác thanh toán
							</li>
						</ul>

						<h2 id="<?= anchorId(1) ?>">2. Mục đích sử dụng</h2>
						<ul>
							<li>Xây dựng biểu đồ thống kê năng lực cá nhân và gợi ý bài học phù hợp</li>
							<li>Gửi email xác nhận giao dịch, thông báo kết quả thi hoặc nhắc nhở lộ trình học hằng ngày
							</li>
							<li>Phát hiện các hành vi gian lận hoặc đăng nhập bất thường để bảo vệ tài khoản người dùng</li>
							<li>Nghiên cứu thị hiếu người dùng để phát triển các tính năng mới hữu ích hơn</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">3. Bên thứ ba</h2>
						<p>Chúng tôi cam kết <strong>không bao giờ bán</strong> hoặc cho thuê dữ liệu cá nhân của bạn. Thông
							tin chỉ được chia sẻ trong các giới hạn sau:</p>
						<ul>
							<li>Các đối tác thanh toán (MoMo, ZaloPay, VNPay) để hoàn tất quy trình nâng cấp Premium</li>
							<li>Dịch vụ lưu trữ đám mây có độ bảo mật cao để vận hành hệ thống</li>
							<li>Cơ quan pháp luật trong trường hợp có yêu cầu chính thức theo quy định của pháp luật Việt
								Nam</li>
						</ul>

						<h2 id="<?= anchorId(3) ?>">4. Cookie và Theo dõi</h2>
						<ul>
							<li><strong>Cookie phiên:</strong> Giúp bạn duy trì trạng thái đăng nhập khi chuyển giữa các
								trang bài học</li>
							<li><strong>Cookie tuỳ chọn:</strong> Lưu trữ cấu hình giao diện (ví dụ: chế độ Dark mode) theo
								sở thích của bạn</li>
							<li><strong>Phân tích:</strong> Sử dụng dữ liệu ẩn danh để đo lường lưu lượng truy cập và hiệu
								năng website</li>
						</ul>

						<h2 id="<?= anchorId(4) ?>">5. Bảo mật dữ liệu</h2>
						<ul>
							<li>Sử dụng giao thức HTTPS (SSL) cho toàn bộ website để mã hoá dữ liệu truyền tải</li>
							<li>Mật khẩu người dùng được băm (hashing) bằng thuật toán hiện đại, nhân viên Prephub không thể
								xem mật khẩu gốc</li>
							<li>Hệ thống sao lưu (backup) tự động hàng ngày để phòng ngừa sự cố mất dữ liệu</li>
						</ul>

						<h2 id="<?= anchorId(5) ?>">6. Quyền người dùng</h2>
						<ul>
							<li>Bạn có quyền truy cập, sửa đổi hoặc yêu cầu trích xuất dữ liệu cá nhân của mình trong phần
								cài đặt</li>
							<li>Bạn có quyền yêu cầu xoá tài khoản vĩnh viễn. Khi đó, toàn bộ dữ liệu học tập sẽ bị huỷ bỏ
								và không thể khôi phục</li>
							<li>Tuỳ chỉnh việc nhận thông báo marketing qua email bất kỳ lúc nào</li>
						</ul>

						<h2 id="<?= anchorId(6) ?>">7. Thay đổi chính sách</h2>
						<p>Prephub có quyền cập nhật chính sách bảo mật này để phù hợp với các quy định pháp lý mới. Các
							thay đổi lớn sẽ được thông báo nổi bật trên trang chủ hoặc qua email của bạn ít nhất 7 ngày
							trước khi có hiệu lực</p>

					<?php elseif ($active === 'su-dung'): ?>
						<p>Bằng cách sử dụng bất kỳ dịch vụ nào của Prephub, bạn mặc định đồng ý với các điều khoản sử dụng
							sau đây. Nếu bạn không đồng ý, vui lòng ngừng truy cập nền tảng</p>

						<h2 id="<?= anchorId(0) ?>">1. Quyền hạn truy cập</h2>
						<ul>
							<li>Prephub cấp cho bạn một quyền sử dụng có giới hạn, không độc quyền để học tập cá nhân</li>
							<li>Nghiêm cấm mọi hành vi sử dụng công cụ tự động (scripts, bots, scrapers) để truy xuất dữ
								liệu từ website</li>
							<li>Bạn không được phép chỉnh sửa, dịch ngược (reverse engineer) hoặc can thiệp vào mã nguồn của
								nền tảng</li>
						</ul>

						<h2 id="<?= anchorId(1) ?>">2. Trách nhiệm tài khoản</h2>
						<ul>
							<li>Thông tin đăng ký phải chính xác và được cập nhật thường xuyên</li>
							<li>Mỗi tài khoản Premium chỉ dành cho <strong>duy nhất một cá nhân</strong>. Việc chia sẻ tài
								khoản cho nhiều người sử dụng sẽ dẫn đến khóa tài khoản vĩnh viễn mà không được hoàn tiền
							</li>
							<li>Bạn chịu hoàn toàn trách nhiệm đối với mọi hoạt động diễn ra dưới tài khoản của mình</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">3. Sở hữu trí tuệ</h2>
						<ul>
							<li>Mọi nội dung bao gồm văn bản đề thi, file âm thanh (audio), hình ảnh, video giải thích và
								giao diện website đều thuộc sở hữu trí tuệ của Prephub</li>
							<li>Việc sao chép, tái xuất bản hoặc phân phối nội dung của Prephub lên các nền tảng khác khi
								chưa có sự đồng ý bằng văn bản là vi phạm pháp luật</li>
							<li>Học viên chỉ được phép in ấn tài liệu phục vụ mục đích học tập cá nhân, không được dùng để
								kinh doanh hoặc giảng dạy thu phí</li>
						</ul>

						<h2 id="<?= anchorId(3) ?>">4. Quy tắc ứng xử</h2>
						<ul>
							<li>Không đăng tải hoặc bình luận các nội dung thô tục, khiêu khích, hoặc vi phạm thuần phong mỹ
								tục</li>
							<li>Nghiêm cấm việc quảng cáo các dịch vụ khác trên các diễn đàn hoặc phần thảo luận của Prephub
							</li>
							<li>Mọi hành vi gian lận điểm số nhằm trục lợi từ các chương trình khuyến mãi sẽ bị xử lý nghiêm
								khắc</li>
						</ul>

						<h2 id="<?= anchorId(4) ?>">5. Miễn trừ trách nhiệm</h2>
						<ul>
							<li>Prephub nỗ lực tối đa để duy trì sự ổn định của hệ thống nhưng không đảm bảo website sẽ luôn
								hoạt động 100% thời gian mà không có lỗi kỹ thuật hoặc bảo trì</li>
							<li>Kết quả điểm thi TOEIC thực tế phụ thuộc vào nhiều yếu tố chủ quan của học viên. Prephub
								không cam kết một mức điểm cụ thể cho bất kỳ trường hợp nào</li>
							<li>Chúng tôi không chịu trách nhiệm cho các mất mát dữ liệu do lỗi từ phía thiết bị hoặc kết
								nối internet của người dùng</li>
						</ul>

						<h2 id="<?= anchorId(5) ?>">6. Chấm dứt dịch vụ</h2>
						<p>Prephub có quyền tạm đình chỉ hoặc chấm dứt quyền truy cập của bất kỳ người dùng nào vi phạm
							nghiêm trọng các điều khoản trên mà không cần báo trước</p>

					<?php elseif ($active === 'huong-dan'): ?>
						<h2 id="<?= anchorId(0) ?>">Bước 1 - Đăng ký và Thiết lập</h2>
						<ul>
							<li>Truy cập trang chủ <strong>prephub.up.railway.app</strong>, nhấn nút <strong>Đăng nhập</strong> và
								chọn <strong>Tạo tài khoản</strong></li>
							<li>Chúng tôi khuyến khích sử dụng Google Login để đăng nhập nhanh chóng và bảo mật</li>
							<li>Sau khi đăng nhập, hãy hoàn thiện mục tiêu (ví dụ: TOEIC 750+) trong phần cá nhân để hệ
								thống tuỳ chỉnh kho đề thi phù hợp</li>
						</ul>

						<h2 id="<?= anchorId(1) ?>">Bước 2 - Lựa chọn lộ trình</h2>
						<ul>
							<li><strong>Lộ trình bài bản:</strong> Dành cho người muốn học từ đầu, hệ thống sẽ chia lộ trình
								45-60 ngày với các bài học ngữ pháp và nghe hiểu xen kẽ</li>
							<li><strong>Luyện đề chuyên sâu:</strong> Dành cho người sắp thi, tập trung vào việc giải đề
								Full Test và nhận xét chi tiết</li>
							<li><strong>Luyện tập theo Part:</strong> Nếu bạn yếu Part 5 hoặc Part 7, hãy chọn chế độ luyện
								riêng lẻ để tối ưu thời gian</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">Bước 3 - Quy trình học và thi</h2>
						<ul>
							<li><strong>Chế độ Luyện tập:</strong> Bạn có thể xem giải thích đáp án ngay sau mỗi câu nhấn
								chọn. Phù hợp để học kiến thức mới</li>
							<li><strong>Chế độ Thi thử:</strong> Đồng hồ đếm ngược 120 phút sẽ kích hoạt, giao diện khoá các
								nút xem đáp án để đảm bảo tính khách quan như thi thật</li>
							<li><strong>Lưu nháp:</strong> Bạn có thể tạm dừng bài thi và quay lại làm tiếp bất cứ lúc nào,
								dữ liệu sẽ được lưu tự động trên đám mây</li>
						</ul>

						<h2 id="<?= anchorId(3) ?>">Bước 4 - Phân tích và Theo dõi</h2>
						<ul>
							<li>Sau khi nộp bài, hãy nhấn vào <strong>Xem chi tiết</strong> để biết tỷ lệ đúng/sai của từng Part
							</li>
							<li>Hệ thống sẽ liệt kê các "Câu hỏi cần lưu ý" - đây là những câu bạn làm sai hoặc mất quá
								nhiều thời gian để suy nghĩ</li>
							<li>Vào Dashboard để xem biểu đồ tăng trưởng điểm số qua các tuần học tập</li>
						</ul>

						<h2 id="<?= anchorId(4) ?>">Nâng cấp Premium</h2>
						<ul>
							<li>Tài khoản miễn phí có giới hạn số lượng đề thi mỗi tháng</li>
							<li>Nâng cấp Premium giúp bạn mở khoá 100% kho đề thi, không quảng cáo và nhận được sự hỗ trợ ưu
								tiên từ đội ngũ giáo viên</li>
						</ul>

						<h2 id="<?= anchorId(5) ?>">Xử lý sự cố thường gặp</h2>
						<ul>
							<li><strong>Lỗi Audio không phát:</strong> Kiểm tra lại kết nối internet và đảm bảo trình duyệt
								không chặn tự động phát âm thanh. Thử tải lại trang (F5)</li>
							<li><strong>Mất dữ liệu bài thi:</strong> Đảm bảo bạn không sử dụng chế độ ẩn danh hoặc xoá
								cache trình duyệt khi đang trong phiên làm bài</li>
							<li><strong>Không thấy nút Nộp bài:</strong> Cuộn xuống cuối danh sách câu hỏi hoặc kiểm tra
								panel điều hướng bên phải màn hình thi</li>
						</ul>

					<?php elseif ($active === 'thanh-toan'): ?>
						<h2 id="<?= anchorId(0) ?>">Chi tiết các gói dịch vụ</h2>
						<p>Chúng tôi cung cấp đa dạng các lựa chọn phù hợp với từng giai đoạn ôn luyện của học viên. Dưới đây là thông tin chi tiết về các gói dịch vụ chính thức:</p>
						<table>
							<thead>
								<tr>
									<th>Loại gói</th>
									<th>Thời hạn</th>
									<th>Đặc quyền</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong>Gói Premium</strong></td>
									<td>30 ngày</td>
									<td>Mở khoá toàn bộ kho đề thi, giải thích đáp án chi tiết và phân tích kết quả cơ bản</td>
								</tr>
								<tr>
									<td><strong>Gói Premium</strong></td>
									<td>365 ngày</td>
									<td>Tiết kiệm 20% chi phí, ưu tiên hỗ trợ 24/7, phân tích điểm yếu chuyên sâu bằng AI</td>
								</tr>
								<tr>
									<td><strong>Khoá học</strong></td>
									<td>Vĩnh viễn</td>
									<td>Sở hữu trọn đời nội dung khoá học đã mua, bao gồm video bài giảng và tài liệu đính kèm</td>
								</tr>
								<tr>
									<td><strong>Gói Trọn bộ</strong></td>
									<td>Vĩnh viễn</td>
									<td>Sở hữu trọn đời toàn bộ kho đề Premium, tất cả khoá học hiện có và các nội dung mới phát hành trong tương lai</td>
								</tr>
							</tbody>
						</table>

						<h2 id="<?= anchorId(1) ?>">Cổng thanh toán hỗ trợ</h2>
						<p>Prephub hỗ trợ đa dạng các hình thức thanh toán hiện đại, an toàn tuyệt đối thông qua các đối tác uy tín:</p>
						<ul>
							<li><strong>Chuyển khoản ngân hàng:</strong> Hỗ trợ tất cả các ngân hàng nội địa (Vietcombank, MB Bank, Techcombank...) qua hệ thống VietQR kích hoạt tự động</li>
							<li><strong>Ví điện tử:</strong> MoMo, ZaloPay, ShopeePay (AirPay)</li>
							<li><strong>Thẻ quốc tế:</strong> Visa, Mastercard, JCB thông qua cổng thanh toán bảo mật</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">Quy trình thanh toán và Kích hoạt</h2>
						<ul>
							<li><strong>Bước 1:</strong> Lựa chọn gói dịch vụ tại trang <strong>Pricing</strong></li>
							<li><strong>Bước 2:</strong> Chọn phương thức thanh toán phù hợp và quét mã QR hoặc nhập thông tin thẻ</li>
							<li><strong>Bước 3:</strong> Hệ thống xác thực giao dịch và tự động kích hoạt tài khoản ngay lập tức (thông thường trong vòng 30 giây)</li>
							<li><strong>Bước 4:</strong> Nhận email xác nhận giao dịch và hoá đơn điện tử từ Prephub</li>
						</ul>

						<h2 id="<?= anchorId(3) ?>">Quản lý gia hạn và Hoá đơn</h2>
						<ul>
							<li><strong>Gia hạn tự động:</strong> Đối với gói tháng, hệ thống sẽ tự động gia hạn vào ngày hết hạn. Bạn có thể tắt tính năng này bất cứ lúc nào trong <strong>Cài đặt tài khoản</strong></li>
							<li><strong>Hoá đơn tài chính:</strong> Toàn bộ lịch sử giao dịch được lưu trữ minh bạch. Nếu cần hoá đơn VAT cho doanh nghiệp, vui lòng liên hệ bộ phận kế toán trong vòng 24 giờ sau khi thanh toán</li>
						</ul>

						<h2 id="<?= anchorId(4) ?>">Xử lý sự cố thanh toán</h2>
						<p>Trường hợp đã trừ tiền nhưng tài khoản chưa được nâng cấp, bạn vui lòng thực hiện các bước sau:</p>
						<ul>
							<li>Chụp ảnh màn hình biên lai giao dịch thành công</li>
							<li>Gửi thông tin về email <strong>billing@prephub.up.railway.app</strong> hoặc nhắn tin trực tiếp qua Fanpage</li>
							<li>Đội ngũ kỹ thuật sẽ kiểm tra và kích hoạt thủ công cho bạn trong vòng tối đa 15 phút</li>
						</ul>

						<h2 id="<?= anchorId(5) ?>">Cam kết an toàn</h2>
						<p>Mọi thông tin thanh toán của bạn đều được mã hoá và xử lý trực tiếp bởi các đối tác cổng thanh toán đạt tiêu chuẩn quốc tế. Prephub cam kết không lưu trữ thông tin thẻ hay mật khẩu ngân hàng của người dùng</p>

					<?php elseif ($active === 'giao-dich'): ?>
						<h2 id="<?= anchorId(0) ?>">1. Điều kiện chung</h2>
						<p>Các điều khoản này áp dụng cho mọi người dùng thực hiện giao dịch trên Prephub.vn. Việc thanh
							toán đồng nghĩa với việc bạn đã đọc và chấp nhận các điều kiện này</p>

						<h2 id="<?= anchorId(1) ?>">2. Xác nhận và Kích hoạt</h2>
						<ul>
							<li>Dịch vụ được coi là đã cung cấp khi hệ thống mở khoá quyền truy cập Premium cho tài khoản
								của bạn</li>
							<li>Prephub có trách nhiệm đảm bảo nội dung Premium luôn đúng như mô tả tại trang Pricing</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">3. Chính sách hoàn tiền chi tiết</h2>
						<ul>
							<li><strong>Thời hạn:</strong> Yêu cầu hoàn tiền phải được gửi trong vòng 07 ngày kể từ ngày
								thanh toán đầu tiên</li>
							<li><strong>Điều kiện:</strong> Người dùng chưa làm quá 05 đề thi Premium và chưa tải xuống bất
								kỳ tài liệu PDF nào từ hệ thống</li>
							<li><strong>Lý do chấp nhận:</strong> Lỗi kỹ thuật hệ thống kéo dài quá 48h không khắc phục
								được, nội dung đề thi sai lệch nghiêm trọng so với quảng cáo</li>
							<li><strong>Trường hợp không hoàn tiền:</strong> Thay đổi ý định cá nhân, đã sử dụng phần lớn
								dịch vụ, hoặc vi phạm điều khoản sử dụng dẫn đến khoá tài khoản</li>
						</ul>

						<h2 id="<?= anchorId(3) ?>">4. Quy trình yêu cầu hoàn trả</h2>
						<ul>
							<li>Gửi email tới billing@prephub.up.railway.app với đầy đủ thông tin: Mã đơn hàng, Email tài
								khoản, Lý do hoàn tiền, Thông tin tài khoản ngân hàng nhận tiền</li>
							<li>Prephub sẽ phản hồi kết quả xem xét trong vòng 03 ngày làm việc</li>
							<li>Nếu được chấp nhận, tiền sẽ được hoàn trả về tài khoản của bạn sau 07-10 ngày làm việc tuỳ
								thuộc vào quy trình của ngân hàng</li>
						</ul>

						<h2 id="<?= anchorId(4) ?>">5. Thay đổi biểu giá</h2>
						<ul>
							<li>Prephub có quyền điều chỉnh giá gói cước để phù hợp với thị trường và chi phí vận hành</li>
							<li>Mọi sự thay đổi về giá sẽ không ảnh hưởng đến gói cước bạn đang sử dụng cho đến khi hết chu
								kỳ thanh toán đó</li>
						</ul>

						<h2 id="<?= anchorId(5) ?>">6. Nghĩa vụ thuế</h2>
						<p>Tất cả giá niêm yết trên Prephub đã bao gồm các loại thuế liên quan theo quy định của pháp luật
							Việt Nam. Người dùng không phải trả thêm bất kỳ khoản thuế nào khác</p>

						<h2 id="<?= anchorId(6) ?>">7. Giải quyết tranh chấp</h2>
						<p>Mọi tranh chấp phát sinh từ giao dịch sẽ được ưu tiên giải quyết thông qua thương lượng. Nếu
							không đạt được thoả thuận, tranh chấp sẽ được đưa ra Toà án có thẩm quyền tại TP. Hồ Chí Minh để
							giải quyết theo luật pháp Việt Nam</p>

					<?php elseif ($active === 'khieu-nai'): ?>
						<p>Sự hài lòng của học viên là ưu tiên số một của chúng tôi. Nếu có bất kỳ vấn đề gì, hãy cho chúng
							tôi biết</p>

						<h2 id="<?= anchorId(0) ?>">Kênh tiếp nhận phản hồi</h2>
						<ul>
							<li><strong>Email Hỗ trợ:</strong> support@prephub.up.railway.app (Tiếp nhận lỗi kỹ thuật, nội
								dung)</li>
							<li><strong>Email Thanh toán:</strong> billing@prephub.up.railway.app (Vấn đề nạp tiền, hoàn
								tiền, hoá đơn)</li>
							<li><strong>Hotline:</strong> 090x xxx xxx (Hỗ trợ khẩn cấp từ 8:00 - 20:00 hằng ngày)</li>
							<li><strong>Inbox Fanpage:</strong> facebook.com/prephub.up.railway.app (Tư vấn lộ trình và giải
								đáp nhanh)</li>
						</ul>

						<h2 id="<?= anchorId(1) ?>">Quy trình xử lý chuẩn</h2>
						<ul>
							<li><strong>Bước 1 - Ghi nhận:</strong> Hệ thống tự động gửi email xác nhận đã nhận yêu cầu của
								bạn</li>
							<li><strong>Bước 2 - Phân loại:</strong> Khiếu nại được chuyển đến bộ phận chuyên trách (Kỹ
								thuật, Nội dung hoặc Tài chính)</li>
							<li><strong>Bước 3 - Xử lý:</strong> Đội ngũ hỗ trợ kiểm tra và đưa ra phương án giải quyết
								trong vòng 24h-48h làm việc</li>
							<li><strong>Bước 4 - Phản hồi:</strong> Gửi thông báo kết quả xử lý và xin ý kiến đánh giá từ
								người dùng</li>
						</ul>

						<h2 id="<?= anchorId(2) ?>">Bảng tra cứu giải quyết nhanh</h2>
						<table>
							<thead>
								<tr>
									<th>Tình huống</th>
									<th>Thời gian xử lý dự kiến</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Báo lỗi đáp án sai</td>
									<td>Trong vòng 24 giờ</td>
								</tr>
								<tr>
									<td>Lỗi đăng nhập/Tài khoản</td>
									<td>Trong vòng 12 giờ</td>
								</tr>
								<tr>
									<td>Khiếu nại thanh toán</td>
									<td>24 - 48 giờ</td>
								</tr>
								<tr>
									<td>Yêu cầu hoàn tiền</td>
									<td>3 - 5 ngày làm việc</td>
								</tr>
							</tbody>
						</table>

						<h2 id="<?= anchorId(3) ?>">Cam kết chất lượng phục vụ</h2>
						<ul>
							<li>Không để bất kỳ email nào bị bỏ sót quá 72 giờ làm việc</li>
							<li>Giải quyết khiếu nại dựa trên tinh thần hỗ trợ tối đa cho học viên</li>
							<li>Liên tục cải thiện hệ thống dựa trên những góp ý thực tế từ cộng đồng người dùng</li>
						</ul>
					<?php endif; ?>
				</div>
			</article>
		</div>
	</main>

	<?php include './components/footer.php'; ?>

	<script>
		// --- TOC COLLAPSE LOGIC ---
		function toggleToc() {
			const body = document.getElementById('toc-body');
			const icon = document.getElementById('toc-icon');
			if (body.style.display === 'none') {
				body.style.display = 'block';
				icon.classList.remove('bx-chevron-down');
				icon.classList.add('bx-chevron-up');
			} else {
				body.style.display = 'none';
				icon.classList.remove('bx-chevron-up');
				icon.classList.add('bx-chevron-down');
			}
		}

		// --- SMOOTH SCROLL FOR TOC LINKS ---
		document.querySelectorAll('.toc-link').forEach(anchor => {
			anchor.addEventListener('click', function (e) {
				e.preventDefault();
				const targetId = this.getAttribute('href').substring(1);
				const targetEl = document.getElementById(targetId);

				if (targetEl) {
					const yOffset = -100;
					const y = targetEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
					window.scrollTo({ top: y, behavior: 'smooth' });
				}
			});
		});
	</script>
	<!-- khối pop up ads nâng cấp nổi ở góc màn hình -->
	<?php include './components/pro-card.php'; ?>
	<?php include './components/pro-card-script.php'; ?>
</body>

</html>