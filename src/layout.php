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
function render(string $id): string
{
    $block = & layout($id);

    // Skip empty, inactive or invalid blocks
    if (!$block
        || empty($block['is_active'])
        || empty($block['type'])
        || !($type = data('block', $block['type']))
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
 * Set block variables
 *
 * @param string $id
 * @param array $vars
 *
 * @return void
 */
function vars(string $id, array $vars)
{
    $block = & layout($id);

    foreach ($vars as $var => $value) {
        $block['vars'][$var] = $value;
    }
}

/**
 * Layout
 *
 * @param string $id
 * @param array $block
 *
 * @return mixed
 */
function & layout(string $id, array $block = null)
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
 * Layout handles
 *
 * @return array
 */
function layout_handles(): array
{
    static $data;

    if ($data === null) {
        $data = ['layout-base'];
        $data[] = registered() ? 'account-registered' : 'account-anonymous';

        if (admin()) {
            $data[] = 'role-admin';
        }

        if (request('base') === request('url')) {
            $data[] = 'http-base';
        }

        $meta = data('meta', request('entity'));

        if ($meta && meta_action(request('action'), $meta)) {
            $data[] = 'action-' . request('action');
        }

        $data[] = request('entity');
        $data[] = request('id');
    }

    return $data;
}

/**
 * Load layout by handles
 *
 * @return void
 */
function layout_load()
{
    $layout = data('layout');

    foreach (layout_handles() as $handle) {
        foreach (data_filter($layout, ['handle' => $handle]) as $block) {
            layout_add($block);
        }
    }
}

/**
 * Add block to layout
 *
 * @param array $block
 *
 * @return void
 *
 * @throws InvalidArgumentException
 */
function layout_add(array $block)
{
    if (empty($block['id'])) {
        throw new InvalidArgumentException('No block ID given');
    }

    $oldBlock = & layout($block['id']);

    // New blocks
    if ($oldBlock === null) {
        $oldBlock = data('skeleton', 'block');

        if (empty($block['type']) || !data('block', $block['type'])) {
            throw new InvalidArgumentException('No or invalid block type given for block with ID ' . $block['id']);
        }
    }

    if ($block['id'] === 'root') {
        $block['parent'] = '';
    } elseif (!empty($block['parent']) && $block['parent'] !== $oldBlock['parent']) {
        layout_parent($block, $oldBlock['parent']);
    }

    // Add or update block
    $oldBlock = array_replace($oldBlock, $block);
}

/**
 * Sets or updates parent block
 *
 * @param array $block
 * @param string $oldId
 *
 * @return void
 */
function layout_parent(array $block, string $oldId)
{
    $oldParent = layout($oldId);
    $parent = layout($block['parent']);

    // Pemove block from old parent block if it exists
    if ($oldParent) {
        $oldParent = & layout($oldId);
        unset($oldParent['children'][$block['id']]);
    }

    // Add block to new parent block if it exists
    if ($parent) {
        $parent = & layout($block['parent']);
        $parent['children'][$block['id']] = isset($block['sort_order']) ? (int) $block['sort_order'] : 0;
    }
}
