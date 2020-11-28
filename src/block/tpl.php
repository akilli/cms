<?php
declare(strict_types=1);

namespace block\tpl;

use app;

function render(array $block): string
{
    return $block['tpl'] ? app\tpl($block['tpl']) : '';
}
