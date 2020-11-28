<?php
declare(strict_types=1);

namespace block\view;

use app;
use arr;
use attr;
use entity;

function render(array $block): string
{
    if (!$block['cfg']['attr_id'] || ($data = $block['cfg']['data']) && empty($data['_entity'])) {
        return '';
    }

    if (!$data && $block['cfg']['entity_id'] && $block['cfg']['id']) {
        $data = entity\one($block['cfg']['entity_id'], [['id', $block['cfg']['id']]]);
    } elseif (!$data && ($app = app\data('app')) && $app['entity_id'] && $app['id']) {
        $data = entity\one($app['entity_id'], [['id', $app['id']]]);
    }

    if (!($entity = $data['_entity'] ?? null) || !($attrs = arr\extract($entity['attr'], $block['cfg']['attr_id']))) {
        return '';
    }

    if (in_array('page', [$entity['id'], $entity['parent_id']]) && empty($attrs['title']) && $data['title']) {
        $data['name'] = $data['title'];
    }

    $html = '';

    foreach ($attrs as $attrId => $attr) {
        $html .= attr\viewer($data, $attr, ['wrap' => true]);
    }

    return $html;
}
