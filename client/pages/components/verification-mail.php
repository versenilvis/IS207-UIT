<?php
$verificationLink = $verificationLink ?? '#';
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực tài khoản PrepHub</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Inter, 'Segoe UI', Arial, sans-serif; color:#05102b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb; margin:0; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px; background:#ffffff; border:1px solid #dbe3ef; border-radius:16px; overflow:hidden; font-family:Inter, 'Segoe UI', Arial, sans-serif;">
                    <tr>
                        <td style="background:#05102b; padding:28px 32px;">
                            <div style="font-size:24px; line-height:1.2; font-weight:700; color:#ffffff;">PrepHub</div>
                            <div style="margin-top:8px; font-size:13px; line-height:1.5; color:rgba(255,255,255,0.72);">Luyện thi TOEIC online</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 12px;">
                            <h1 style="margin:0; font-family:Inter, 'Segoe UI', Arial, sans-serif; font-size:30px; line-height:1.35; font-weight:700; color:#05102b;">Xác thực tài khoản</h1>
                            <p style="margin:18px 0 0; font-size:16px; line-height:1.65; color:#64748b;">
                                Cảm ơn bạn đã đăng ký PrepHub. Vui lòng xác thực email để kích hoạt tài khoản và bắt đầu luyện thi TOEIC.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 32px 28px;">
                            <a href="<?= htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') ?>" style="display:block; width:100%; box-sizing:border-box; padding:15px 20px; border-radius:8px; background:#05102b; color:#ffffff; text-decoration:none; text-align:center; font-size:16px; font-weight:700;">
                                Xác thực email
                            </a>
                            <p style="margin:18px 0 0; font-size:14px; line-height:1.6; color:#64748b;">
                                Nếu bạn không đăng ký tài khoản PrepHub, bạn có thể bỏ qua email này. Tài khoản sẽ chỉ được kích hoạt sau khi email được xác thực.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
