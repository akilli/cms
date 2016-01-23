<?php
namespace toolbar;

use app;
use data;
use metadata;
use url;

/**
 * Toolbar
 *
 * @return array
 */
function data(): array
{
    $data = app\data('toolbar');

    foreach (app\data('metadata') as $entity => $meta) {
        if (metadata\action('index', $meta) && !empty($meta['toolbar']) && !empty($data[$meta['toolbar']])) {
            $data[$meta['toolbar']]['children'][$entity]['name'] = $meta['name'];
            $data[$meta['toolbar']]['children'][$entity]['description'] = $meta['description'];
            $data[$meta['toolbar']]['children'][$entity]['url'] = $entity . '/index';
            $data[$meta['toolbar']]['children'][$entity]['sort_order'] = (int) $meta['sort_order'];
        }
    }

    return prepare($data);
}

/**
 * Prepare toolbar items
 *
 * @param array $data
 *
 * @return array
 */
function prepare(array & $data): array
{
    foreach ($data as $key => $item) {
        if (empty($item['name'])) {
            unset($data[$key]);
            continue;
        }

        if (isset($item['url'])) {
            $item['url'] = url\path($item['url']);
        }

        if (!empty($item['children'])) {
            prepare($item['children']);
        }

        $item['url'] = $item['url'] ?? null;
        $item['description'] = $item['description'] ?? null;
        $data[$key] = $item;
    }

    return data\order($data, 'sort_order');
}
