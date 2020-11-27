<?php
declare(strict_types=1);

namespace block;

use app;

/**
 * Filter
 */
function filter(array $block): string
{
    return $block['cfg']['attr'] || $block['cfg']['search'] ? app\tpl($block['tpl'], $block['cfg']) : '';
}
