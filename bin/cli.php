<?php
declare(strict_types=1);

namespace app;

/**
 * Bootstrap
 */
require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * Run application
 */
if (empty($argv[1]) || !preg_match('#^([a-z][a-z_\.]+):([a-z]+)$#', $argv[1], $match)) {
    echo i18n('Invalid command');
    exit(1);
}

$events = [id('cli', $match[1], $match[2])];
$data = ['command' => $match[0], 'entity' => $match[1], 'action' => $match[2]];
event($events, $data);
exit(0);
