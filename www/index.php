<?php
declare(strict_types = 1);

namespace app;

use layout;

/**
 * Initialize application
 */
foreach (glob(__DIR__ . '/../src/*.php') as $file) {
    include_once $file;
}

/**
 * Error handler
 */
set_error_handler('app\error');
set_exception_handler('app\exception');

/**
 * Run application
 */
run();
echo layout\§('root');
