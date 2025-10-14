<?php
// scripts/setup_db.php
// Lee config/.env y ejecuta scripts/sample_data.sql usando mysqli multi_query
$env = parse_ini_file(__DIR__ . '/../config/.env');
$host = $env['DB_HOST'] ?? '127.0.0.1';
$db = $env['DB_NAME'] ?? 'lotificaciones';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$port = $env['DB_PORT'] ?? 3306;

$mysqli = new mysqli($host, $user, $pass, '', (int)$port);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connect error: {$mysqli->connect_error}\n");
    exit(1);
}
// Ensure database exists
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . $mysqli->real_escape_string($db) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    fwrite(STDERR, "Could not create database: " . $mysqli->error . "\n");
    exit(1);
}
// Use database
if (!$mysqli->select_db($db)) {
    fwrite(STDERR, "Could not select database: " . $mysqli->error . "\n");
    exit(1);
}

$sqlFile = __DIR__ . '/sample_data.sql';
if (!is_readable($sqlFile)) {
    fwrite(STDERR, "SQL file not found: $sqlFile\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Failed to read SQL file\n");
    exit(1);
}

// Execute multiple queries
if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "Error executing SQL: " . $mysqli->error . "\n");
    exit(1);
}
// flush results
do {
    if ($res = $mysqli->store_result()) {
        $res->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

fwrite(STDOUT, "SQL import completed.\n");
$mysqli->close();
return 0;
