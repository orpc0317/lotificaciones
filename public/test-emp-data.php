<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\EmpleadoModel;

header('Content-Type: application/json');

try {
    $model = new EmpleadoModel();
    $emp = $model->getById(2);
    
    echo json_encode($emp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
