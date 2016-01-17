<?php
namespace view;

use account;
use app;
use data;
use http;
use InvalidArgumentException;
use metadata;
use role;

/**
 * Block Type
 *
 * @param string $key
 *
 * @return array
 */
function type($key = null)
{
    static $data;

    if ($data === null) {
        foreach (app\data('block') as $type => $config) {
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
function handles(array $handles = null)
{
    static $data;

    if ($data === null || $handles !== null) {
        $data = [];
        $metadata = app\data('metadata', http\request('entity'));

        if ($handles === null) {
            $handles[] = 'view-base';
        }

        $handles[] = account\registered() ? 'account-registered' : 'account-anonymous';

        if (role\admin()) {
            $handles[] = 'role-admin';
        }

        if (http\request('base') === http\request('url')) {
            $handles[] = 'http-base';
        }

        if ($metadata && metadata\action(http\request('action'), $metadata)) {
            $handles[] = 'action-' . http\request('action');
        }

        $handles[] = http\request('entity');
        $handles[] = http\request('id');
        $data = array_unique($handles);
    }

    return $data;
}

/**
 * Render block
 *
 * @param string $id
 *
 * @return string
 */
function render($id)
{
    $block = & layout($id);

    // Skip empty, inactive or invalid blocks
    if (!$block
        || empty($block['is_active'])
        || empty($block['type'])
        || !($type = type($block['type']))
        || !empty($block['privilege']) && !role\allowed($block['privilege'])
    ) {
        return '';
    }

    // Block Render Events
    app\event(
        ['block.type.' . $block['type'], 'block.render.' . $id],
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
function & layout($id, array $block = null)
{
    $data = & app\registry('layout');

    if ($data === null) {
        $data = [];
    }

    if ($block !== null || !array_key_exists($id, $data)) {
        $data[$id] = $block;
    }

    return $data[$id];
}

/**
 * Load view by handles
 *
 * @param array $handles
 *
 * @return void
 */
function load(array $handles = null)
{
    $handles = handles($handles);
    $layout = app\data('layout');

    foreach ($handles as $handle) {
        foreach (data\filter($layout, ['handle' => $handle]) as $block) {
            add($block);
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
function add(array $block)
{
    if (empty($block['id'])) {
        throw new InvalidArgumentException('No block ID given');
    }

    $oldBlock = & layout($block['id']);

    // New blocks
    if ($oldBlock === null) {
        $oldBlock = app\data('skeleton', 'block');

        if (empty($block['type']) || !type($block['type'])) {
            throw new InvalidArgumentException('No or invalid block type given for block with ID ' . $block['id']);
        }
    }

    if ($block['id'] === 'root') {
        $block['parent'] = '';
    } elseif (!empty($block['parent']) && $block['parent'] !== $oldBlock['parent']) {
        parent($block, $oldBlock['parent']);
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
function vars($id, array $vars)
{
    $block = & layout($id);

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
function parent(array $block, $oldId)
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
