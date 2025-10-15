<?php
// Simple HTTP client test for the ajax endpoint
$url = 'http://127.0.0.1:8000/empleados/ajax?lang=es';

echo "Testing URL: $url\n";
echo "=================================\n\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json',
        'timeout' => 5
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "ERROR: Failed to fetch data\n";
    $error = error_get_last();
    if ($error) {
        echo "Error message: " . $error['message'] . "\n";
    }
} else {
    echo "Response received (" . strlen($response) . " bytes)\n";
    echo "First 500 chars:\n";
    echo substr($response, 0, 500) . "\n\n";
    
    // Try to parse as JSON
    $json = json_decode($response, true);
    if ($json === null) {
        echo "ERROR: Response is not valid JSON!\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
        echo "\nFull response:\n";
        echo $response . "\n";
    } else {
        echo "SUCCESS: Response is valid JSON\n";
        echo "Columns: " . (isset($json['columns']) ? count($json['columns']) : 0) . "\n";
        echo "Data rows: " . (isset($json['data']) ? count($json['data']) : 0) . "\n";
        
        if (isset($json['data']) && count($json['data']) > 0) {
            echo "\nFirst employee:\n";
            print_r($json['data'][0]);
        }
    }
}
