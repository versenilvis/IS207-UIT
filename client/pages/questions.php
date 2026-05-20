<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Form Nhập Câu Hỏi TOEIC</title>
<<<<<<< HEAD
=======
	<?php include('./components/metadata.php'); ?>
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8
	<link href="../styles/questionsStyle.css" rel="stylesheet">
</head>

<body>
<<<<<<< HEAD
	<?php include('./components/metadata.php'); ?>
	<?php include('./components/navBar.php'); ?>
	<?php include('./components/header.php'); ?>
=======
	<?php include('./components/navbar.php'); ?>
>>>>>>> 6883b1c77eff62933e9c743ae90c545facc14fe8

	<div class="container-wrapper">
		<!-- Test Creation Form -->
		<?php include('./components/questions/test-form.php'); ?>

		<!-- Test & Part Configuration -->
		<?php include('./components/questions/test-config.php'); ?>

		<!-- Action Buttons -->
		<?php include('./components/questions/action-buttons.php'); ?>

		<!-- Questions List Container -->
		<div id="questions-container"></div>
	</div>

	<!-- Question Templates (Hidden) -->
	<?php include('./components/questions/question-templates.php'); ?>

	<script src="../js/questions/state.js"></script>
	<script src="../js/questions/ui.js"></script>
	<script src="../js/questions/api.js"></script>
	<script src="../js/questions/form-fill.js"></script>
	<script src="../js/questions/dom-builder.js"></script>
	<script src="../js/questions/validation.js"></script>
	<script src="../js/questions/utils.js"></script>
	<script src="../js/questions/main.js"></script>

	<?php include('./components/footer.php'); ?>
</body>
</html>