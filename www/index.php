<?php
declare(strict_types = 1);

namespace app;

use function layout\§;
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
    function (int $severity, string $msg, string $file, int $line): void {
        throw new ErrorException($msg, 0, $severity, $file, $line);
    }
);
set_exception_handler(
    function (Throwable $e): void {
        logger((string) $e);
    }
);
register_shutdown_function(
    function (): void {
        $error = error_get_last();
        $errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING];

        if ($error && in_array($error['type'], $errors)) {
            logger(print_r($error, true));
        }
    }
);

/**
 * Run application
 */
run();
echo §('root');
