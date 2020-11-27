<?php
declare(strict_types=1);

namespace block;

use app;
use layout;

/**
 * HTML
 */
function html(): string
{
    $app = app\data('app');
    $a = [
        'lang' => APP['lang'],
        'data-parent' => $app['parent_id'],
        'data-entity' => $app['entity_id'],
        'data-action' => $app['action'],
        'data-url' => app\data('request', 'url')
    ];

    return "<!doctype html>\n" . app\html('html', $a, layout\block('head') . layout\block('body'));
}
