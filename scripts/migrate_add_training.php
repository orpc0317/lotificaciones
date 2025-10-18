<?php
/**
 * Migration: Add Training Table
 * Run this script once to add the training table to the database
 */

try {
    // Load environment variables
    $envFile = __DIR__ . '/../config/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'lotificaciones';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connected to database: $dbname\n\n";

    // Create training table
    echo "Creating empleado_capacitacion table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS empleado_capacitacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empleado_id INT NOT NULL,
        nombre_curso VARCHAR(200) NOT NULL,
        fecha_aprobado DATE NOT NULL,
        recursos_aprobados DECIMAL(10,2) DEFAULT 0.00,
        comentarios TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
        INDEX idx_empleado (empleado_id),
        INDEX idx_fecha (fecha_aprobado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Table created successfully!\n\n";

    // Add sample data
    echo "Adding sample training data...\n";
    $sql = "INSERT INTO empleado_capacitacion (empleado_id, nombre_curso, fecha_aprobado, recursos_aprobados, comentarios) VALUES
        (1, 'Seguridad Industrial Básica', '2024-01-15', 250.00, 'Curso obligatorio completado satisfactoriamente'),
        (1, 'Primeros Auxilios', '2024-03-10', 180.00, 'Certificación vigente por 2 años'),
        (2, 'Manejo de Equipos Pesados', '2024-02-20', 500.00, 'Incluye certificación oficial'),
        (2, 'Prevención de Riesgos Laborales', '2024-04-05', 150.00, NULL)";

    try {
        $pdo->exec($sql);
        echo "✓ Sample data added successfully!\n\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "⚠ Sample data already exists (skipped)\n\n";
        } else {
            throw $e;
        }
    }

    echo "✅ Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
