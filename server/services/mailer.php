<?php
//Tạo và trả Phpmailer object, dùng để gửi mail qua SMTP. 
//Dùng cho phần login. Cần gửi verification tới email của ng dùng

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/../../vendor/autoload.php";

$mailConfig = require __DIR__ . "/../config/mail.php";

$mail = new PHPMailer(true);

$mail->CharSet = PHPMailer::CHARSET_UTF8;
$mail->Encoding = 'base64';

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = $mailConfig['host'];
$mail->Username = $mailConfig['username'];
$mail->Password = $mailConfig['password'];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = $mailConfig['port'];

$mail->setFrom($mail->Username, $mailConfig['from_name']);

$mail->isHtml(true);

return $mail;
?>
