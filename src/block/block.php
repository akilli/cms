<?php
declare(strict_types=1);

namespace block\block;

use layout;

function render(array $block): string
{
    $block['type'] = 'view';

    return layout\render(layout\cfg($block));
}
