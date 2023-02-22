<?php
declare(strict_types=1);

/**
 * Error handling
 */
set_error_handler(function (int $severity, string $msg, string $file, int $line): void {
    app\log(new ErrorException($msg, 0, $severity, $file, $line));
});
set_exception_handler(function (Throwable $e): void {
    app\log($e);
});

/**
 * Application constants
 */
require_once __DIR__ . '/const.php';

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
