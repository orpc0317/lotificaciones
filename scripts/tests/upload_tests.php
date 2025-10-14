<?php
// scripts/tests/upload_tests.php
// Simple smoke test: try to upload a non-image file and expect the server to reject it.

$base = 'http://127.0.0.1:8000';
$endpoint = $base . '/empleados/create';

// Create a temporary text file to upload
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'not_image.txt';
file_put_contents($tmp, "This is not an image\n");

$boundary = '----WebKitFormBoundary' . substr(md5(microtime()), 0, 16);
$eol = "\r\n";
$data = '';
$data .= '--' . $boundary . $eol;
$data .= 'Content-Disposition: form-data; name="nombres"' . $eol . $eol;
$data .= 'UploadTest' . $eol;
$data .= '--' . $boundary . $eol;
$data .= 'Content-Disposition: form-data; name="apellidos"' . $eol . $eol;
$data .= 'Tester' . $eol;
$data .= '--' . $boundary . $eol;
$data .= 'Content-Disposition: form-data; name="foto"; filename="not_image.txt"' . $eol;
$data .= 'Content-Type: text/plain' . $eol . $eol;
$data .= file_get_contents($tmp) . $eol;
$data .= '--' . $boundary . '--' . $eol;

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n",
        'content' => $data,
        'ignore_errors' => true,
        'timeout' => 10,
    ]
];

$context = stream_context_create($opts);
$result = @file_get_contents($endpoint, false, $context);
if ($result === false) {
    echo "Request failed or timed out\n";
    exit(2);
}

$json = json_decode($result, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Server did not return JSON. Response:\n";
    echo $result . "\n";
    exit(3);
}

if (isset($json['error'])) {
    echo "OK: server rejected invalid image upload with error: " . $json['error'] . "\n";
    exit(0);
}

if (isset($json['success']) && $json['success'] == true) {
    echo "FAIL: server accepted invalid image upload (unexpected)\n";
    exit(1);
}

echo "Unexpected server response:\n" . $result . "\n";
exit(4);
