<?php
declare(strict_types=1);

namespace block\container;

use app;
use layout;

function render(array $block): string
{
    if (($html = layout\render_children($block['id'])) && $block['cfg']['tag']) {
        return app\html($block['cfg']['tag'], $block['cfg']['id'] ? ['id' => $block['id']] : [], $html);
    }

    return $html;
}
