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
if (empty($argv[1]) || !($cfg = cfg('cli', $argv[1]))) {
    echo i18n('Invalid command');
    exit(1);
}

$pre = id('cli', $argv[1]);
$data = event([id($pre, 'preexecute')], ['command' => $argv[1]]);
$data = $cfg['call']($data);
event([id($pre, 'postexecute')], $data);
exit(0);
