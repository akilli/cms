<?php
declare(strict_types=1);

namespace menu;

use app;

/**
 * Filters empty parent menu items and not allowed menu items
 */
function filter(array $data): array
{
    $empty = [];

    foreach ($data as $id => $item) {
        if (!$item['active'] || $item['privilege'] && !app\allowed($item['privilege'])) {
            unset($data[$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent_id']) {
            unset($empty[$item['parent_id']]);
            $data[$item['parent_id']]['children'] = true;
        }
    }

    return array_diff_key($data, $empty);
}
