<?php
// api/detect-network.php

function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$user_ip = get_user_ip();
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'ip' => $user_ip,
    'agent' => $user_agent,
    'timestamp' => time()
]);
?>
