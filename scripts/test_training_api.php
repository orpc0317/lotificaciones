<?php
/**
 * Test Training API Endpoints
 * Quick script to test all training CRUD operations
 */

$baseUrl = 'http://localhost:8080/lotificaciones-api/public/api';
$empleadoId = 13; // Mario Lopez

echo "=== TRAINING API TESTS ===\n\n";

// Test 1: GET training records
echo "1. GET /empleados/$empleadoId/training\n";
$response = file_get_contents("$baseUrl/empleados/$empleadoId/training");
echo $response . "\n\n";

// Test 2: Bulk UPDATE training records
echo "2. PUT /empleados/$empleadoId/training (bulk update)\n";
$trainingData = [
    [
        'nombre' => 'Seguridad Industrial',
        'fecha' => '2024-01-15',
        'recursos' => 1500,
        'comentarios' => 'Curso obligatorio completado'
    ],
    [
        'nombre' => 'Primeros Auxilios',
        'fecha' => '2024-03-20',
        'recursos' => 800,
        'comentarios' => 'Certificado vigente hasta 2026'
    ],
    [
        'nombre' => 'Manejo Defensivo',
        'fecha' => '2024-06-10',
        'recursos' => 1200,
        'comentarios' => 'Incluye práctica en pista'
    ]
];

$postData = json_encode(['training_data' => $trainingData]);

$opts = [
    'http' => [
        'method' => 'PUT',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
];

$context = stream_context_create($opts);
$response = file_get_contents("$baseUrl/empleados/$empleadoId/training", false, $context);
echo $response . "\n\n";

// Test 3: GET updated training records
echo "3. GET /empleados/$empleadoId/training (after update)\n";
$response = file_get_contents("$baseUrl/empleados/$empleadoId/training");
$data = json_decode($response, true);
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Count training records
echo "4. GET /empleados/$empleadoId/training/count\n";
$response = file_get_contents("$baseUrl/empleados/$empleadoId/training/count");
echo $response . "\n\n";

echo "=== TESTS COMPLETE ===\n";
