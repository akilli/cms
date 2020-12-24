<?php
declare(strict_types=1);

namespace block\tag;

use html;

function render(array $block): string
{
    return $block['cfg']['tag'] ? html\element($block['cfg']['tag'], $block['cfg']['attr'], $block['cfg']['val']) : '';
}
