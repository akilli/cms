<?php
declare(strict_types=1);

/**
 * Application constants
 */
require_once __DIR__ . '/const.php';

/**
 * Recursively require base and extension source files
 */
$scan = function (string $path) use (&$scan, &$scanDir): void {
    array_map(fn(string $file): int|bool => include_once $file, glob($path . '/*' . APP['php.ext']));
    array_map($scanDir, glob($path . '/*', GLOB_ONLYDIR));
};
$scanDir = function (string $dir) use (&$scan): void {
    $scan($dir);
};
array_map($scanDir, [APP['path']['src'], APP['path']['ext.src']]);
unset($scan, $scanDir);

/**
 * Back up pregenerated configuration
 */
cfg\backup();
