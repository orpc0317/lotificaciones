<?php
/**
 * Migration script to add email, telefono, direccion, ciudad fields to empleados table
 * Run this once: php scripts/migrate_add_fields.php
 */

// Load environment variables
$envFile = __DIR__ . '/../config/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'lotificaciones';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting migration...\n";
    
    // Check if columns already exist
    $stmt = $db->query("SHOW COLUMNS FROM empleados LIKE 'email'");
    if ($stmt->rowCount() > 0) {
        echo "❌ Column 'email' already exists. Migration may have already run.\n";
        echo "Skipping migration to avoid errors.\n";
        exit(0);
    }
    
    // Add new columns
    echo "Adding new columns to empleados table...\n";
    $sql = "ALTER TABLE `empleados`
            ADD COLUMN `email` VARCHAR(255) DEFAULT NULL AFTER `genero`,
            ADD COLUMN `telefono` VARCHAR(50) DEFAULT NULL AFTER `email`,
            ADD COLUMN `direccion` TEXT DEFAULT NULL AFTER `telefono`,
            ADD COLUMN `ciudad` VARCHAR(100) DEFAULT NULL AFTER `direccion`";
    
    $db->exec($sql);
    echo "✓ Columns added successfully!\n";
    
    // Update existing records with sample data
    echo "Updating existing records with sample data...\n";
    $updateSql = "UPDATE `empleados` 
                  SET 
                    `email` = CONCAT(LOWER(SUBSTRING_INDEX(nombres, ' ', 1)), '.', LOWER(SUBSTRING_INDEX(apellidos, ' ', 1)), '@example.com'),
                    `telefono` = CONCAT('555-', LPAD(id * 1000, 4, '0')),
                    `direccion` = CONCAT('Calle ', id, ', Zona ', FLOOR(id / 2) + 1),
                    `ciudad` = CASE 
                      WHEN id % 3 = 0 THEN 'Guatemala'
                      WHEN id % 3 = 1 THEN 'Antigua'
                      ELSE 'Quetzaltenango'
                    END
                  WHERE id IS NOT NULL";
    
    $db->exec($updateSql);
    echo "✓ Sample data updated successfully!\n";
    
    // Verify the changes
    echo "\nVerifying new columns:\n";
    $stmt = $db->query("SHOW COLUMNS FROM empleados WHERE Field IN ('email', 'telefono', 'direccion', 'ciudad')");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
