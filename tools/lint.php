<?php
// tools/lint.php — syntax-check every PHP file in the project.
// Usage:  php tools/lint.php
// Exits with code 1 if any file fails `php -l`.

$root = dirname(__DIR__);
$phpBin = PHP_BINARY ?: 'php';

$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $path = str_replace('\\', '/', $file->getPathname());
    if (strpos($path, '/.git/') !== false) continue;
    if (strtolower($file->getExtension()) !== 'php') continue;
    $files[] = $file->getPathname();
}
sort($files);

$failures = [];
foreach ($files as $f) {
    $rel = str_replace($root, '.', $f);
    exec(escapeshellarg($phpBin) . ' -l ' . escapeshellarg($f) . ' 2>&1', $out, $code);
    $line = implode("\n", $out);
    if ($code !== 0 || strpos($line, 'No syntax errors') === false) {
        $failures[] = "$rel\n  $line";
    }
    $out = [];
}

echo "Linted " . count($files) . " PHP file(s)\n";
if ($failures) {
    echo "FAIL — " . count($failures) . " file(s) with errors:\n\n";
    echo implode("\n\n", $failures) . "\n";
    exit(1);
}
echo "All files passed syntax check.\n";
exit(0);
