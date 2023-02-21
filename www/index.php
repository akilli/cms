<?php
declare(strict_types=1);

/**
 * Register functions
 */
set_error_handler(function (int $severity, string $msg, string $file, int $line): void {
    app\log(new ErrorException($msg, 0, $severity, $file, $line));
});
set_exception_handler(function (Throwable $e): void {
    app\log($e);
});
register_shutdown_function(function (): void {
    if ($msg = app\msg()) {
        session\save('msg', $msg);
    }
});

/**
 * Bootstrap
 */
require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * Run application
 */
echo app\run();
