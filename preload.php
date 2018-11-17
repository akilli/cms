<?php
declare(strict_types = 1);

namespace app;

/**
 * Include base source files
 */
foreach (glob(__DIR__ . '/src/*.php') as $file) {
    include_once $file;
}

/**
 * Include extension source files
 */
foreach (glob(path('ext.src', '*.php')) as $file) {
    include_once $file;
}
