<?php
declare(strict_types = 1);

namespace app;

/**
 * Preload source files
 *
 * @todo Use opcache.preload once it is available
 *
 * @see https://wiki.php.net/rfc/preload
 */
require dirname(__DIR__) . '/preload.php';

/**
 * Run application
 */
run();

/**
 * Render response
 */
echo block('root');
