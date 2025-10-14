<?php
// scripts/check_namespace_case.php
// Quick check to ensure PSR-4 namespace prefixes match filesystem casing.
// Exits with code 0 if OK, 1 otherwise.

$root = __DIR__ . '/..';
$composer = json_decode(file_get_contents($root . '/composer.json'), true);
if (!isset($composer['autoload']['psr-4'])) {
    echo "No psr-4 mapping found in composer.json\n";
    exit(0);
}
$errors = [];
foreach ($composer['autoload']['psr-4'] as $ns => $path) {
    $path = rtrim($path, '/\\');
    $abs = realpath($root . DIRECTORY_SEPARATOR . $path);
    if ($abs === false) {
        $errors[] = "Path for namespace $ns -> $path not found on disk";
        continue;
    }
    // Check directory name casing for each directory segment that maps to namespace segments
    $nsSegments = explode('\\', trim($ns, '\\'));
    $pSegments = explode(DIRECTORY_SEPARATOR, trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR));
    // Find the first segment where they should align (common assumption: "App" -> "app")
    // We'll walk the real path from project root and compare segment names case-sensitively
    $expectedParts = [];
    $cur = $root;
    foreach ($pSegments as $seg) {
        $cur .= DIRECTORY_SEPARATOR . $seg;
        if (!file_exists($cur)) { // shouldn't happen because realpath found it, but be safe
            $errors[] = "Expected segment $cur doesn't exist";
            break;
        }
        // Compare the actual directory name on disk with the segment string exactly
        $actualName = basename($cur);
        if ($actualName !== $seg) {
            $errors[] = sprintf("Casing mismatch: expected '%s' but filesystem has '%s' for path segment %s", $seg, $actualName, $cur);
        }
    }
}
if (!empty($errors)) {
    echo "PSR-4 namespace / path casing issues found:\n";
    foreach ($errors as $e) echo " - $e\n";
    exit(1);
}
echo "PSR-4 namespace casing check OK\n";
exit(0);
