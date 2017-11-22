<?php
declare(strict_types = 1);

namespace app;

/**
 * Initialize application
 */
$inc = function (string $path): void {
    foreach (glob($path . '/*.php') as $file) {
        include_once $file;
    }
};
$inc(dirname(__DIR__) . '/src');
$inc(path('ext', 'src'));

/**
 * Error handler
 */
set_error_handler('app\error');
set_exception_handler('app\exception');

/**
 * Run application
 */
run();
echo §('root');
