<?php
namespace akilli;

use data;

/**
 * Toolbar
 *
 * @return array
 */
function toolbar(): array
{
    $data = data('toolbar');

    foreach (data('metadata') as $entity => $meta) {
        if (metadata_action('index', $meta) && !empty($meta['toolbar']) && !empty($data[$meta['toolbar']])) {
            $data[$meta['toolbar']]['children'][$entity]['name'] = $meta['name'];
            $data[$meta['toolbar']]['children'][$entity]['description'] = $meta['description'];
            $data[$meta['toolbar']]['children'][$entity]['url'] = $entity . '/index';
            $data[$meta['toolbar']]['children'][$entity]['sort_order'] = (int) $meta['sort_order'];
        }
    }

    return toolbar_prepare($data);
}

/**
 * Prepare toolbar items
 *
 * @param array $data
 *
 * @return array
 */
function toolbar_prepare(array & $data): array
{
    foreach ($data as $key => $item) {
        if (empty($item['name'])) {
            unset($data[$key]);
            continue;
        }

        if (isset($item['url'])) {
            $item['url'] = url($item['url']);
        }

        if (!empty($item['children'])) {
            toolbar_prepare($item['children']);
        }

        $item['url'] = $item['url'] ?? null;
        $item['description'] = $item['description'] ?? null;
        $data[$key] = $item;
    }

    return data\data_order($data, 'sort_order');
}
