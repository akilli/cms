<?php
declare(strict_types=1);

namespace block\toolbar;

use app;
use layout;

function render(array $block): string
{
    $data = app\cfg('toolbar');
    $empty = [];

    foreach ($data as $id => $item) {
        if (!$item['active'] || $item['priv'] && !app\allowed($item['priv'])) {
            unset($data[$id]);
        } elseif (!$item['url']) {
            $empty[$id] = true;
        } elseif ($item['parent_id']) {
            unset($empty[$item['parent_id']]);
        }
    }

    $block['type'] = 'nav';
    $block['cfg'] = ['data' => array_diff_key($data, $empty), 'toggle' => true];

    return layout\render(layout\cfg($block));
}
