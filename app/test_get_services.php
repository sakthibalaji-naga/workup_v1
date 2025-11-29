<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/index.php/api/v1/get_services?application_id=1');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: a82d13597a3d58522cef27faadf1f8fe',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo 'HTTP Code: ' . $httpcode . PHP_EOL;
echo 'CURL Error: ' . $error . PHP_EOL;
echo 'Response: ' . $response . PHP_EOL;

curl_close($ch);
?>
