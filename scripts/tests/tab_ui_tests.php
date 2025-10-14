<?php
// Simple smoke test: ensure empleados view contains tab IDs and badge placeholders
$file = __DIR__ . '/../../app/views/empleados.php';
if (!file_exists($file)) {
    echo "FAIL: file not found: $file\n";
    exit(2);
}
$content = file_get_contents($file);
$checks = [
    'new-generals' => 'tab-pane',
    'new-puesto' => 'tab-pane',
    'new-others' => 'tab-pane',
    'edit-generals' => 'tab-pane',
    'edit-puesto' => 'tab-pane',
    'edit-others' => 'tab-pane',
    'ficha-generals' => 'tab-pane',
    'ficha-puesto' => 'tab-pane',
    'ficha-others' => 'tab-pane',
    'badge-tab' => 'badge-tab'
];
$failures = [];
foreach ($checks as $needle => $desc) {
    if (strpos($content, $needle) === false) $failures[] = $needle;
}
if (!empty($failures)) {
    echo "FAIL: missing expected tokens:\n" . implode("\n", $failures) . "\n";
    exit(1);
}
echo "OK: tab placeholders and ids present\n";
exit(0);
