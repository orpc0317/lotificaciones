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

// Interactive: prompt for fields definition
echo "\nEnter fields for the module as name:type comma-separated (e.g. codigo:string,nombres:string,fecha_nacimiento:date).\n";
echo "Leave empty for default example fields.\n";
echo "Fields: ";
$line = trim(fgets(STDIN));
if ($line === '') {
    $line = 'codigo:string,nombres:string,apellidos:string,fecha_nacimiento:date,edad:number';
}
$pairs = array_map('trim', explode(',', $line));
$fields = [];
foreach ($pairs as $p) {
    if ($p === '') continue;
    $parts = explode(':', $p);
    $fname = trim($parts[0]);
    $ftype = isset($parts[1]) ? trim($parts[1]) : 'string';
    $fields[] = ['name' => $fname, 'type' => $ftype];
}

// Build template snippets
$allowed = array_map(function($f){ return "'".$f['name']."'"; }, $fields);
$allowed_fields_php = '[' . implode(', ', $allowed) . ']';

$columns_php_parts = [];
foreach ($fields as $f) {
    $label = ucwords(str_replace(['_','-'], ' ', $f['name']));
    $columns_php_parts[] = "array('data' => '".$f['name']."', 'title' => '".$label."')";
}
$columns_php = 'array(' . implode(', ', $columns_php_parts) . ')';

$datatable_js_cols = [];
foreach ($fields as $f) {
    $label = ucwords(str_replace(['_','-'], ' ', $f['name']));
    $datatable_js_cols[] = "{ data: '".$f['name']."', title: '".$label."' }";
}
$datatable_js = '[' . implode(', ', $datatable_js_cols) . ']';

$form_inputs = '';
foreach ($fields as $f) {
    $label = ucwords(str_replace(['_','-'], ' ', $f['name']));
    $form_inputs .= "<div class=\"mb-3\">\n";
    $form_inputs .= "  <label for=\"edit_".$f['name']."\" class=\"form-label\">".$label."</label>\n";
    $form_inputs .= "  <input type=\"text\" class=\"form-control\" id=\"edit_".$f['name']."\" name=\"".$f['name']."\">\n";
    $form_inputs .= "</div>\n";
}

// Ask about file upload
echo "\nInclude file upload field? (y/N): ";
$uploadAnswer = trim(fgets(STDIN));
$includeUpload = (strtolower($uploadAnswer) === 'y');
if ($includeUpload) {
    $form_inputs .= "<div class=\"mb-3\">\n  <label for=\"edit_file\" class=\"form-label\">File</label>\n  <input type=\"file\" class=\"form-control\" id=\"edit_file\" name=\"file\">\n</div>\n";
}

// Ask about validations
echo "\nAdd simple client-side validation? (y/N): ";
$valAnswer = trim(fgets(STDIN));
$includeValidation = (strtolower($valAnswer) === 'y');

$mapping['{{include_upload}}'] = $includeUpload ? 'true' : 'false';
$mapping['{{include_validation}}'] = $includeValidation ? 'true' : 'false';

$mapping = [
    '{{Name}}' => $Name,
    '{{name}}' => $name_snake,
    '{{NAME}}' => $NAME,
    '{{name_kebab}}' => $name_kebab,
    '{{storage}}' => $storage,
    '{{allowed_fields}}' => $allowed_fields_php,
    '{{columns_php}}' => $columns_php,
    '{{datatable_columns_js}}' => $datatable_js,
    '{{form_inputs}}' => $form_inputs,
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
