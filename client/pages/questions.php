<?php
/**
 * Redirect old questions page to the new unified admin dashboard
 */

$query = $_SERVER['QUERY_STRING'] ?? '';
$redirect_url = 'admin.php?section=tests';
if ($query !== '') {
    $redirect_url .= '&' . $query;
}

header("Location: " . $redirect_url);
exit();