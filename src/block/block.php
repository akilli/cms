<?php
declare(strict_types=1);

namespace block\block;

use layout;

function render(array $block): string
{
    return layout\render(layout\cfg(['type' => 'view'] + $block));
}