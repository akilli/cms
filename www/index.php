<?php
declare(strict_types = 1);

namespace app;

use ErrorException;
use Throwable;

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
set_error_handler(
    function (int $severity, string $msg, string $file, int $line): void {
        log(new ErrorException($msg, 0, $severity, $file, $line));
    }
);
set_exception_handler(
    function (Throwable $e): void {
        log($e);
    }
);

/**
 * Run application
 */
run();
echo §('root');
