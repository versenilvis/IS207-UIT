<?php

require_once __DIR__ . '/env.php';

return [
    'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'username' => getenv('MAIL_USERNAME') ?: '',
    'password' => getenv('MAIL_PASSWORD') ?: '',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'PrepHub',
    'port' => (int) (getenv('MAIL_PORT') ?: 587),
];
