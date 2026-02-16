<?php
// api/detect-vpn.php

function is_vpn_or_proxy() {
    $proxy_headers = [
        'HTTP_VIA',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR_IP',
        'VIA',
        'X_FORWARDED_FOR',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION'
    ];

    foreach ($proxy_headers as $header) {
        if (isset($_SERVER[$header])) {
            return true;
        }
    }
    return false;
}

$is_vpn = is_vpn_or_proxy();

header('Content-Type: application/json');
echo json_encode([
    'vpn_detected' => $is_vpn,
    'message' => $is_vpn ? 'Proxy/VPN detected' : 'Clean connection'
]);
?>
