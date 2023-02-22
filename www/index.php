<?php
declare(strict_types=1);

/**
 * Register functions
 */
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
