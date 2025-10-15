<?php
try {
    $env = parse_ini_file(__DIR__ . '/config/.env');
    $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection: OK\n";
    
    // List tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
    
    // Check empleados table
    if (in_array('empleados', $tables)) {
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM empleados');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Empleados count: " . $result['count'] . "\n";
        
        // Get first few records
        $stmt = $pdo->query('SELECT * FROM empleados LIMIT 3');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample records:\n";
        print_r($rows);
    } else {
        echo "WARNING: empleados table does not exist!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
