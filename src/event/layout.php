<?php
declare(strict_types=1);

namespace event\layout;

use app;
use arr;
use entity;

function data(array $data): array
{
    $cfg = app\cfg('layout');
    $app = app\data('app');

    if ($app['page']) {
        foreach (entity\all('layout', crit: [['page_id', $app['id']]]) as $item) {
            $cfg[app\id('page', 'view', $app['id'])]['layout-' . $item['parent_id'] .'-' . $item['name']] = [
                'type' => 'tag',
                'parent_id' => $item['parent_id'],
                'sort' => $item['sort'],
                'cfg' => [
                    'attr' => ['id' => $item['entity_id'] . '-' . $item['block_id']],
                    'tag' => 'app-block',
                ],
            ];
        }
    }

    foreach ($app['event'] as $event) {
        foreach (($cfg[$event] ?? []) as $id => $block) {
            $block['id'] = $id;
            $data[$id] = empty($data[$id]) ? $block : arr\extend($data[$id], $block);
        }
    }

    return array_map('layout\cfg', $data);
}
