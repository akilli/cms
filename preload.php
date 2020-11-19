<?php
declare(strict_types=1);

/**
 * Application constants
 */
require_once __DIR__ . '/const.php';

/**
 * Recursively require base and extension source files
 */
$scan = function (string $path) use (& $scan): void {
    array_map(fn(string $file) => include_once $file, glob($path . '/*' . APP['php.ext']));
    array_map(fn(string $dir) => $scan($dir), glob($path . '/*', GLOB_ONLYDIR));
};
array_map(fn(string $dir) => $scan($dir), array_filter([APP['path']['src'], APP['path']['ext.src']], 'is_dir'));
unset($scan);

/**
 * Back up pregenerated configuration
 */
cfg\backup();
