<?php
declare(strict_types=1);

namespace block\tag;

use app;

function render(array $block): string
{
    return $block['cfg']['tag'] ? app\html($block['cfg']['tag'], $block['cfg']['attr'], $block['cfg']['val']) : '';
}
