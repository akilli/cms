<?php
declare(strict_types = 1);

namespace qnd;

use ErrorException;
use Throwable;

/**
 * Initialize application
 */
foreach (glob(__DIR__ . '/../src/*.php') as $file) {
    include_once $file;
}

/**
 * Error handler
 */
set_error_handler(
    function (int $severity, string $message, string $file, int $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);
set_exception_handler(
    function (Throwable $e) {
        critical((string) $e);
    }
);
register_shutdown_function(
    function () {
        $error = error_get_last();
        $errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING];

        if ($error && in_array($error['type'], $errors)) {
            critical(print_r($error, true));
        }
    }
);

/**
 * Run application
 */
app();
echo §('root');
