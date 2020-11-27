<?php
declare(strict_types=1);

namespace block;

use app;
use layout;

/**
 * Container
 */
function container(array $block): string
{
    if (($html = layout\children($block['id'])) && $block['cfg']['tag']) {
        return app\html($block['cfg']['tag'], $block['cfg']['id'] ? ['id' => $block['id']] : [], $html);
    }

    return $html;
}
