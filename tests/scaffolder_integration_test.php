<?php
// Simple integration-style test for the scaffolder. Run with: php tests/scaffolder_integration_test.php
// This test will run the scaffolder with a unique module name and verify files are created
$projectRoot = dirname(__DIR__); // project root
$scaffold = $projectRoot . '/scripts/scaffold_module.php';
$samples = $projectRoot . '/scripts/samples/example_fields.json';

// use a unique module name to avoid collisions
$module = 'TestModCI' . uniqid();

// Run scaffolder
$cmd = escapeshellcmd("php \"$scaffold\" $module --fields-file=\"$samples\" --yes");
exec($cmd, $out, $rc);

$filesCreated = [
    $projectRoot . "/app/Controllers/{$module}Controller.php",
    $projectRoot . "/app/Models/{$module}Model.php",
    $projectRoot . "/app/views/" . strtolower($module) . ".php",
    $projectRoot . "/public/assets/js/" . strtolower($module) . ".js",
];

$missing = [];
foreach ($filesCreated as $f) { if (!file_exists($f)) $missing[] = $f; }
if (count($missing) > 0) {
    echo "FAIL: missing files:\n" . implode("\n", $missing) . "\n";
    exit(1);
}

// Cleanup generated files to keep workspace clean
foreach ($filesCreated as $f) { @unlink($f); }

echo "PASS: scaffolder created expected files.\n";
exit(0);
