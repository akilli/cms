<?php
declare(strict_types = 1);

namespace app;

/**
 * Preload source files
 *
 * @todo Use opcache.preload with PHP 7.4 and remove following `require`-statement
 *
 * @see https://wiki.php.net/rfc/preload
 */
require dirname(__DIR__) . '/preload.php';

/**
 * Run application
 */
echo run();
