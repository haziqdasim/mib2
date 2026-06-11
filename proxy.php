<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$url = 'https://worldcup26.ir/get/games';

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'header'  => "User-Agent: Mozilla/5.0\r\n",
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch data from upstream API']);
    exit;
}

echo $response;
