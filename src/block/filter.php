<?php
declare(strict_types=1);

namespace block\filter;

use app;

function render(array $block): string
{
    if (!$block['cfg']['attr'] && !$block['cfg']['search']) {
        return '';
    }

    return app\tpl($block['cfg']['tpl'], [
        'attr' => $block['cfg']['attr'],
        'data' => $block['cfg']['data'],
        'q' => $block['cfg']['q'],
        'search' => $block['cfg']['search']
    ]);
}
