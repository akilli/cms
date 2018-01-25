<?php
declare(strict_types = 1);

namespace app;

/**
 * Boot application
 */
require_once dirname(__DIR__) . '/boot.php';

/**
 * Run application
 */
run();

/**
 * Render response
 */
echo §('root');
