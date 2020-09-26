<?php
declare(strict_types = 1);

/**
 * Register functions
 */
set_error_handler(fn(int $severity, string $msg, string $file, int $line) => app\log(new ErrorException($msg, 0, $severity, $file, $line)));
set_exception_handler(fn(Throwable $e) => app\log($e));
register_shutdown_function(function (): void {
    if ($data = app\registry('msg')) {
        session\set('msg', $data);
    }
});

/**
 * Application constants
 */
require_once dirname(__DIR__) . '/const.php';

define('CFG', file\load(APP['path']['tmp'] . '/cfg.php'));

/**
 * Run application
 */
echo app\run();
