<?php
declare(strict_types=1);

namespace block\title;

use app;
use str;

function render(array $block): string
{
    $app = app\data('app');
    $text = match (true) {
        !!$block['cfg']['text'] => app\i18n($block['cfg']['text']),
        $app['area'] === '_public_' => $app['page']['title'] ?? $app['page']['name'] ?? '',
        default => $app['entity']['name'] ?? '',
    };

    return $text ? app\html('h1', [], str\enc($text)) : '';
}
