<?php
// Simple PHP module scaffolder
// Usage: php scaffold_module.php ModuleName [--storage=api|db]

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$argvCopy = $argv;
array_shift($argvCopy); // script name
if (count($argvCopy) < 1) {
    echo "Usage: php scaffold_module.php ModuleName [--storage=api|db]\n";
    exit(1);
}

$name = $argvCopy[0];
$storage = 'api'; // default: api (DB-independent)
foreach ($argvCopy as $a) {
    if (strpos($a, '--storage=') === 0) {
        $storage = substr($a, strlen('--storage='));
    }
}

function pascal($s) { return preg_replace('/[^A-Za-z0-9]/','',ucwords(str_replace(['-','_'], ' ', $s))); }
function snake($s) { return strtolower(preg_replace('/[^A-Za-z0-9]+/','_', $s)); }
function kebab($s) { return strtolower(preg_replace('/[^A-Za-z0-9]+/','-', $s)); }

$Name = pascal($name);
$name_snake = snake($name);
$name_kebab = kebab($name);
$NAME = strtoupper($name_snake);

$base = __DIR__;
$templates = $base . '/templates/module';
if (!is_dir($templates)) {
    echo "Templates directory missing: $templates\n";
    exit(1);
}

$mapping = [
    '{{Name}}' => $Name,
    '{{name}}' => $name_snake,
    '{{NAME}}' => $NAME,
    '{{name_kebab}}' => $name_kebab,
    '{{storage}}' => $storage,
];

$targets = [
    // source => destination (relative to project root)
    'controller.tpl.php' => 'app/Controllers/' . $Name . 'Controller.php',
    'model.tpl.php' => 'app/Models/' . $Name . 'Model.php',
    'view.tpl.php' => 'app/views/' . $name_snake . '.php',
    'js.tpl.js' => 'public/assets/js/' . $name_snake . '.js',
];

foreach ($targets as $src => $destRel) {
    $srcPath = $templates . '/' . $src;
    if (!file_exists($srcPath)) { echo "Template missing: $srcPath\n"; continue; }
    $content = file_get_contents($srcPath);
    foreach ($mapping as $token => $val) $content = str_replace($token, $val, $content);
    $destPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $destRel;
    $destDir = dirname($destPath);
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    if (file_exists($destPath)) {
        echo "Skipping existing file: $destRel\n";
    } else {
        file_put_contents($destPath, $content);
        echo "Created: $destRel\n";
    }
}

// Print a quick reminder to add routes and test
echo "\nScaffold complete for module: $Name (storage=$storage)\n";
echo " - Verify routes/web.php and add a route for the new controller (e.g. \"$name_snake\").\n";
echo " - Edit the generated files to adjust endpoints and validation as needed.\n";
echo " - The model generated is storage-agnostic depending on the template used.\n";

exit(0);
