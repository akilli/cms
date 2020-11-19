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
    foreach (array_diff(scandir($path), ['.', '..']) as $name) {
        $file = $path . '/' . $name;

        if (is_file($file) && str_ends_with($file, APP['php.ext'])) {
            require_once $file;
        } elseif (is_dir($file)) {
            $scan($file);
        }
    }
};

foreach (array_filter([APP['path']['src'], APP['path']['ext.src']], 'is_dir') as $path) {
    $scan($path);
}

unset($path, $scan);

/**
 * Back up pregenerated configuration
 */
cfg\backup();
