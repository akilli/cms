<?php
declare(strict_types = 1);

namespace app;

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
 * Run application
 */
run();

/**
 * Render response
 */
echo §('root');
