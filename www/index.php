<?php
declare(strict_types = 1);

namespace app;

use session;
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
 * Register functions
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
register_shutdown_function(
    function () {
        if ($data = reg('msg')) {
            session\set('msg', $data);
        }
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
