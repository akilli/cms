<?php
declare(strict_types=1);

namespace block\db;

use app;
use arr;
use attr;
use entity;

function render(array $block): string
{
    if (!($data = $block['cfg']['data']) && $block['cfg']['entity_id'] && $block['cfg']['id']) {
        $data = entity\one($block['cfg']['entity_id'], [['id', $block['cfg']['id']]]);
    }

    if (!$data
        || $data['_entity']['parent_id'] !== 'block'
        || !($attrs = arr\extract($data['_entity']['attr'], $block['cfg']['attr_id']))
    ) {
        return '';
    }

    $html = '';

    foreach ($attrs as $attr) {
        $html .= attr\viewer($data, $attr, ['wrap' => true]);
    }

    if ($html && $block['cfg']['tag']) {
        return app\html($block['cfg']['tag'], ['data-entity' => $block['cfg']['data']['entity_id']], $html);
    }

    return $html;
}
