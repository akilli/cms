<?php
declare(strict_types=1);

namespace block\html;

use app;
use layout;

function render(): string
{
    $app = app\data('app');
    $a = [
        'lang' => APP['lang'],
        'data-parent' => $app['parent_id'],
        'data-entity' => $app['entity_id'],
        'data-action' => $app['action'],
        'data-url' => app\data('request', 'url')
    ];

    return "<!doctype html>\n" . app\html('html', $a, layout\render_id('head') . layout\render_id('body'));
}
