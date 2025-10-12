<?php
// tests/api_tests.php
// Ejecutar: php api_tests.php

function base_url() {
    // Si se define la variable de entorno BASE_URL se usa; sino un valor por defecto
    $env = getenv('BASE_URL');
    if ($env && strlen($env) > 0) return rtrim($env, '/') . '/';
    // Valor por defecto para servidor PHP integrado (ver instrucciones abajo)
    return 'http://127.0.0.1:8000/';
}

function fetch_json($url) {
    $opts = ['http' => ['method' => 'GET', 'timeout' => 5]];
    $context = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) return null;
    $json = json_decode($raw, true);
    return $json;
}

echo "Running quick API tests...\n";

// Test 1: /empleados/ajax
$url = base_url() . 'empleados/ajax';
$j = fetch_json($url);
if (!is_array($j) || !isset($j['data'])) {
    echo "[FAIL] /empleados/ajax did not return JSON with 'data' key\n";
    exit(1);
}
echo "[OK] /empleados/ajax returned 'data' (count=" . count($j['data']) . ")\n";

// Test 2: /empleados/get?id= first id if exists
if (count($j['data']) > 0) {
    $first = $j['data'][0]['id'];
    $url2 = base_url() . 'empleados/get?id=' . urlencode($first);
    $j2 = fetch_json($url2);
    if (!is_array($j2) || !isset($j2['data']) || (int)$j2['data']['id'] !== (int)$first) {
        echo "[FAIL] /empleados/get?id={$first} failed to return correct empleado\n";
        exit(1);
    }
    echo "[OK] /empleados/get?id={$first} returned empleado record\n";
} else {
    echo "[SKIP] No hay empleados para probar /empleados/get\n";
}

// Test 3: DB smoke test - update and rollback
$config = parse_ini_file(__DIR__ . '/../../config/.env');
$dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    $stmt = $pdo->query("SELECT id FROM empleados LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['id'])) {
        $id = $row['id'];
        $old = $pdo->query("SELECT comentarios FROM empleados WHERE id = " . (int)$id)->fetchColumn();
        $pdo->exec("UPDATE empleados SET comentarios = 'TEST-ROLLBACK' WHERE id = " . (int)$id);
        $new = $pdo->query("SELECT comentarios FROM empleados WHERE id = " . (int)$id)->fetchColumn();
        if ($new !== 'TEST-ROLLBACK') {
            $pdo->rollBack();
            echo "[FAIL] DB update did not persist in transaction\n";
            exit(1);
        }
        $pdo->rollBack();
        echo "[OK] DB update/rollback smoke test passed on id={$id}\n";
    } else {
        echo "[SKIP] No hay filas en empleados para DB smoke test\n";
    }
} catch (Exception $e) {
    echo "[FAIL] DB smoke test error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "All done.\n";
exit(0);
