<?php
declare(strict_types = 1);

namespace app;

use ErrorException;
use Throwable;

/**
 * Include base source files and extensions
 */
foreach (glob(dirname(__DIR__) . '/src/*.php') as $file) {
    include_once $file;
}

foreach (glob(path('ext.src', '*.php')) as $file) {
    include_once $file;
}

/**
 * Register error and exception handlers
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

/**
 * Render response
 */
echo §('root');
