<?php
declare(strict_types=1);

namespace block;

use app;

/**
 * Template
 */
function tpl(array $block): string
{
    return $block['tpl'] ? app\tpl($block['tpl']) : '';
}
