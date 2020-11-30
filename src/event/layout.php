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
    $keys = ['_all_', $app['area']];

    if ($app['invalid']) {
        $keys[] = '_invalid_';
    } else {
        ['entity_id' => $entityId, 'action' => $action] = $app;
        $keys[] = $action;

        if ($parentId = $app['parent_id']) {
            $keys[] = $parentId . ':' . $action;
        }

        $keys[] = $entityId . ':' . $action;

        if ($action === 'view' && $app['id']) {
            $pageKey = 'page:view:' . $app['id'];
            $keys[] = $pageKey;

            foreach (entity\all('layout', [['page_id', $app['id']]]) as $item) {
                $cfg[$pageKey]['layout-' . $item['parent_id'] .'-' . $item['name']] = [
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
    }

    foreach ($keys as $key) {
        if (!empty($cfg[$key])) {
            foreach ($cfg[$key] as $id => $block) {
                $block['id'] = $id;
                $data[$id] = empty($data[$id]) ? $block : arr\extend($data[$id], $block);
            }
        }
    }

    return array_map('layout\cfg', $data);
}
