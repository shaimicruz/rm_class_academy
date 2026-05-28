<?php
// One-off cleanup helper: fixes common UTF-8 mojibake sequences in PHP source files.
// Safe to keep in repo; does nothing unless executed manually.

$replacements = [
    // Punctuation
    "\xC3\x82\xC2\xBF" => "\xC2\xBF", // Â¿ -> ¿
    "\xC3\x83\xC2\x82\xC2\xBF" => "\xC2\xBF", // Ã‚¿ -> ¿
    "\xC3\x82\xC2\xA1" => "\xC2\xA1", // Â¡ -> ¡
    "\xC3\x83\xC2\x82\xC2\xA1" => "\xC2\xA1", // Ã‚¡ -> ¡

    // Lowercase vowels + ñ
    "\xC3\x83\xC2\xA1" => "\xC3\xA1", // Ã¡ -> á
    "\xC3\x83\xC2\xA9" => "\xC3\xA9", // Ã© -> é
    "\xC3\x83\xC2\xAD" => "\xC3\xAD", // Ã­ -> í
    "\xC3\x83\xC2\xB3" => "\xC3\xB3", // Ã³ -> ó
    "\xC3\x83\xC2\xBA" => "\xC3\xBA", // Ãº -> ú
    "\xC3\x83\xC2\xB1" => "\xC3\xB1", // Ã± -> ñ
    "\xC3\x83\xC2\xBC" => "\xC3\xBC", // Ã¼ -> ü

    // Uppercase vowels + Ñ/Ü
    "\xC3\x83\xC2\x81" => "\xC3\x81", // Ã -> Á
    "\xC3\x83\xC2\x89" => "\xC3\x89", // Ã‰ -> É
    "\xC3\x83\xC2\x8D" => "\xC3\x8D", // Ã -> Í
    "\xC3\x83\xC2\x93" => "\xC3\x93", // Ã“ -> Ó
    "\xC3\x83\xC2\x9A" => "\xC3\x9A", // Ãš -> Ú
    "\xC3\x83\xC2\x91" => "\xC3\x91", // Ã‘ -> Ñ
    "\xC3\x83\xC2\x9C" => "\xC3\x9C", // Ãœ -> Ü
];

$root = __DIR__;
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$changed = 0;
$scanned = 0;

foreach ($it as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) continue;
    if (strtolower($file->getExtension()) !== "php") continue;
    $path = $file->getPathname();
    if (basename($path) === basename(__FILE__)) continue;

    $src = file_get_contents($path);
    if ($src === false) continue;
    $scanned++;

    $out = $src;
    foreach ($replacements as $from => $to) {
        $out = str_replace($from, $to, $out);
    }

    if ($out !== $src) {
        file_put_contents($path, $out);
        $changed++;
    }
}

echo "Scanned: {$scanned}\nChanged: {$changed}\n";

