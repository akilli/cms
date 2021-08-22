<?php
declare(strict_types=1);

/**
 * Application constants
 */
require_once __DIR__ . '/const.php';

if (file_exists(APP['path']['ext'] . '/const.php')) {
    require_once APP['path']['ext'] . '/const.php';
}

/**
 * Recursively require base and extension source files
 */
$scan = function (string $path) use (&$scan): void {
    array_map(fn(string $file): int|bool => include_once $file, glob($path . '/*.php'));
    array_map($scan, glob($path . '/*', GLOB_ONLYDIR));
};
array_map($scan, [APP['path']['app.src'], APP['path']['ext.src']]);
unset($scan);

/**
 * Back up pregenerated configuration
 */
cfg\backup();
