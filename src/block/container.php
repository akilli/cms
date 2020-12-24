<?php
declare(strict_types=1);

namespace block\container;

use html;
use layout;

function render(array $block): string
{
    if (($html = layout\render_children($block['id'])) && $block['cfg']['tag']) {
        return html\element($block['cfg']['tag'], $block['cfg']['id'] ? ['id' => $block['id']] : [], $html);
    }

    return $html;
}
