<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\EmpleadoModel;
use App\Helpers\PathHelper;

try {
    $model = new EmpleadoModel();
    $empleado = $model->getById(2);
    
    if (!$empleado) {
        die("Employee not found");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Test</title>
    <?= PathHelper::baseTag() ?>
</head>
<body>
    <h1>Debug Test - Employee Data</h1>
    <button onclick="testFunction()">Test Button</button>
    
    <script>
        console.log('=== SCRIPT BLOCK LOADED ===');
        
        // Employee data for JavaScript (with robust JSON encoding)
        const empleadoData = JSON.parse(<?= json_encode(json_encode($empleado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)) ?>);
        const empleadoId = <?= (int)$empleado['id'] ?>; // Numeric ID for routes
        const empleadoCodigo = <?= json_encode($empleado['codigo'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>; // Code for display
        
        console.log('empleadoData:', empleadoData);
        console.log('empleadoId:', empleadoId);
        console.log('empleadoCodigo:', empleadoCodigo);
        
        function api(path) {
            try {
                const baseEl = document.querySelector('base');
                const base = baseEl ? baseEl.getAttribute('href') : '/';
                return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
            } catch(e) {
                return '/' + path.replace(/^\/+/, '');
            }
        }
        
        function testFunction() {
            console.log('Button clicked!');
            const url = api(`empleados/edit/${empleadoId}`);
            console.log('Generated URL:', url);
            alert('URL would be: ' + url);
        }
        
        console.log('testFunction defined');
    </script>
</body>
</html>
