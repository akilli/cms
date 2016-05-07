<?php
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
    function ($severity, $message, $file, $line) {
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
            critical(print_r($error, 1));
        }
    }
);

/**
 * Run application
 */
app();

/**
 * Debug
 */
printf(
    '<pre>Request Time: %s | Memory Usage: %s | Peak Memory Usage: %s</pre>',
    number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4, ',', '.'),
    number_format(memory_get_usage(), 0, ',', '.'),
    number_format(memory_get_peak_usage(), 0, ',', '.')
);
