<?php
namespace akilli;

use InvalidArgumentException;

/**
 * Render block
 *
 * @param string $id
 *
 * @return string
 */
function view(string $id): string
{
    $block = & view_layout($id);

    // Skip empty, inactive or invalid blocks
    if (!$block
        || empty($block['is_active'])
        || empty($block['type'])
        || !($type = view_type($block['type']))
        || !empty($block['privilege']) && !allowed($block['privilege'])
    ) {
        return '';
    }

    // Block Render Events
    event(
        [
            'block.type.' . $block['type'],
            'block.render.' . $id
        ],
        $block
    );

    return $type['callback']($block);
}

/**
 * Layout
 *
 * @param string $id
 * @param array $block
 *
 * @return mixed
 */
function & view_layout(string $id, array $block = null)
{
    $data = & registry('layout');

    if ($data === null) {
        $data = [];
    }

    if ($block !== null || !array_key_exists($id, $data)) {
        $data[$id] = $block;
    }

    return $data[$id];
}

/**
 * Block Type
 *
 * @param string $key
 *
 * @return array
 */
function view_type(string $key = null): array
{
    static $data;

    if ($data === null) {
        foreach (data('block') as $type => $config) {
            if (!empty($config['callback']) && is_callable($config['callback'])) {
                $data[$type] = $config;
            }
        }
    }

    if ($key === null) {
        return $data;
    }

    return isset($data[$key]) ? $data[$key] : null;
}

/**
 * Handles
 *
 * @param array $handles
 *
 * @return array
 */
function view_handles(array $handles = null): array
{
    static $data;

    if ($data === null || $handles !== null) {
        $data = [];
        $meta = data('metadata', request('entity'));

        if ($handles === null) {
            $handles[] = 'view-base';
        }

        $handles[] = registered() ? 'account-registered' : 'account-anonymous';

        if (admin()) {
            $handles[] = 'role-admin';
        }

        if (request('base') === request('url')) {
            $handles[] = 'http-base';
        }

        if ($meta && metadata_action(request('action'), $meta)) {
            $handles[] = 'action-' . request('action');
        }

        $handles[] = request('entity');
        $handles[] = request('id');
        $data = array_unique($handles);
    }

    return $data;
}

/**
 * Load view by handles
 *
 * @param array $handles
 *
 * @return void
 */
function view_load(array $handles = null)
{
    $handles = view_handles($handles);
    $layout = data('layout');

    foreach ($handles as $handle) {
        foreach (data_filter($layout, ['handle' => $handle]) as $block) {
            view_add($block);
        }
    }
}

/**
 * Add block
 *
 * @param array $block
 *
 * @return void
 *
 * @throws InvalidArgumentException
 */
function view_add(array $block)
{
    if (empty($block['id'])) {
        throw new InvalidArgumentException('No block ID given');
    }

    $oldBlock = & view_layout($block['id']);

    // New blocks
    if ($oldBlock === null) {
        $oldBlock = data('skeleton', 'block');

        if (empty($block['type']) || !view_type($block['type'])) {
            throw new InvalidArgumentException('No or invalid block type given for block with ID ' . $block['id']);
        }
    }

    if ($block['id'] === 'root') {
        $block['parent'] = '';
    } elseif (!empty($block['parent']) && $block['parent'] !== $oldBlock['parent']) {
        view_parent($block, $oldBlock['parent']);
    }

    // Add or update block
    $oldBlock = array_replace($oldBlock, $block);
}

/**
 * Set block variables
 *
 * @param string $id
 * @param array $vars
 *
 * @return void
 */
function view_vars(string $id, array $vars)
{
    $block = & view_layout($id);

    foreach ($vars as $var => $value) {
        $block['vars'][$var] = $value;
    }
}

/**
 * Sets or updates parent block
 *
 * @param array $block
 * @param string $oldId
 *
 * @return void
 */
function view_parent(array $block, string $oldId)
{
    $oldParent = view_layout($oldId);
    $parent = view_layout($block['parent']);

    // Pemove block from old parent block if it exists
    if ($oldParent) {
        $oldParent = & view_layout($oldId);
        unset($oldParent['children'][$block['id']]);
    }

    // Add block to new parent block if it exists
    if ($parent) {
        $parent = & view_layout($block['parent']);
        $parent['children'][$block['id']] = isset($block['sort_order']) ? (int) $block['sort_order'] : 0;
    }
}
