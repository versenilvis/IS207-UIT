<?php

require_once __DIR__ . '/env.php';

return [
    'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
    'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:3000/client/pages/redirect.php',
];
