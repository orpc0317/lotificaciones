<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\EmpleadoController;

// Simulate the ajax request
$_SERVER['REQUEST_URI'] = '/empleados/ajax';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['lang'] = 'es';

echo "Testing /empleados/ajax endpoint:\n";
echo "=================================\n\n";

try {
    ob_start();
    $controller = new EmpleadoController();
    $controller->ajaxList();
    $output = ob_get_clean();
    
    echo "Response:\n";
    echo $output . "\n\n";
    
    // Try to parse as JSON
    $json = json_decode($output, true);
    if ($json === null) {
        echo "ERROR: Response is not valid JSON!\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    } else {
        echo "SUCCESS: Response is valid JSON\n";
        echo "Columns: " . (isset($json['columns']) ? count($json['columns']) : 0) . "\n";
        echo "Data rows: " . (isset($json['data']) ? count($json['data']) : 0) . "\n";
    }
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
