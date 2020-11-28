<?php
declare(strict_types=1);

namespace event\layout;

use app;
use arr;
use entity;
use function layout\db_cfg;

function data(array $data): array
{
    $cfg = app\cfg('layout');
    $app = app\data('app');
    $keys = ['_all_', $app['area']];

    if ($app['invalid']) {
        $keys[] = '_invalid_';
    } else {
        $entityId = $app['entity_id'];
        $action = $app['action'];
        $keys[] = $action;

        if ($parentId = $app['parent_id']) {
            $keys[] = $parentId . ':' . $action;
        }

        $keys[] = $entityId . ':' . $action;

        if ($action === 'view' && $app['id']) {
            $pageKey = 'page:view:' . $app['id'];
            $keys[] = $pageKey;

            if ($dbLayout = entity\all('layout', [['page_id', $app['id']]])) {
                $dbBlocks = [];

                foreach (arr\group($dbLayout, 'entity_id', 'block_id') as $eId => $ids) {
                    foreach (entity\all($eId, [['id', $ids]]) as $item) {
                        $dbBlocks[$item['id']] = $item;
                    }
                }

                foreach ($dbLayout as $id => $item) {
                    $cfg[$pageKey]['layout-' . $item['parent_id'] .'-' . $item['name']] = db_cfg(
                        $dbBlocks[$item['block_id']],
                        ['parent_id' => $item['parent_id'], 'sort' => $item['sort']]
                    );
                }
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
