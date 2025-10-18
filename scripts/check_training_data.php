<?php
/**
 * Check existing training data
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=lotificaciones', 'root', 'Clave01*');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TRAINING DATA IN DATABASE ===\n\n";
    
    $stmt = $pdo->query('
        SELECT ec.*, e.nombres, e.apellidos 
        FROM empleado_capacitacion ec 
        JOIN empleados e ON ec.empleado_id = e.id 
        ORDER BY ec.empleado_id, ec.fecha_aprobado
    ');
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        echo "No training data found.\n";
    } else {
        echo "Found " . count($rows) . " training records:\n\n";
        
        foreach ($rows as $row) {
            echo "Employee: {$row['nombres']} {$row['apellidos']} (ID: {$row['empleado_id']})\n";
            echo "  Course: {$row['nombre_curso']}\n";
            echo "  Date Approved: {$row['fecha_aprobado']}\n";
            echo "  Resources: $" . number_format($row['recursos_aprobados'], 2) . "\n";
            echo "  Comments: " . ($row['comentarios'] ?: '(none)') . "\n";
            echo "\n";
        }
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
