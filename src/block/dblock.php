<?php
declare(strict_types=1);

namespace block;

use app;
use arr;
use attr;

/**
 * Database Block
 */
function dblock(array $block): string
{
    $data = $block['cfg']['data'];

    if (!$data || !($attrs = arr\extract($data['_entity']['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    $html = '';

    foreach ($attrs as $attr) {
        $cfg = ['wrap' => true] + (in_array($attr['id'], ['file', 'title']) ? ['link' => $data['link']] : []);
        $html .= attr\viewer($data, $attr, $cfg);
    }

    if ($html) {
        return app\html('section', ['class' => str_replace('_', '-', $block['cfg']['data']['entity_id'])], $html);
    }

    return '';
}
