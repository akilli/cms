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
 * Application constants
 */
require_once dirname(__DIR__) . '/const.php';

if (file_exists(APP['path']['ext'] . '/const.php')) {
    require_once APP['path']['ext'] . '/const.php';
}

/**
 * Restore pregenerated configuration
 */
cfg\restore();

/**
 * I18n
 */
setlocale(LC_ALL, APP['locale']);

/**
 * Run application
 */
echo app\run();
