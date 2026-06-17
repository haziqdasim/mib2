<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$url = 'https://worldcup26.ir/get/games';

// Initialize cURL session
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 12);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

// Disable strict SSL verification checks to prevent handshake dropping
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Execute the request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// Fallback error if request completely fails or returns a bad server status code
if ($response === false || $http_code !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch data from upstream API']);
    exit;
}

echo $response;