<?php
declare(strict_types=1);

namespace block\filter;

use app;

function render(array $block): string
{
    return $block['cfg']['attr'] || $block['cfg']['search'] ? app\tpl($block['tpl'], $block['cfg']) : '';
}
