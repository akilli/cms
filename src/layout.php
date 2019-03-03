<?php
declare(strict_types = 1);

namespace layout;

use app;
use arr;
use entity;
use DomainException;

/**
 * Gets registered layout block(s)
 */
function get(string $id = null): ?array
{
    if (($data = & app\registry('layout')) === null) {
        $data = [];
        $data = app\event(['layout'], $data);
    }

    if ($id === null) {
        return $data;
    }

    return $data[$id] ?? null;
}

/**
 * Renders block
 */
function render(array $block): string
{
    if (!$block['active'] || $block['priv'] && !app\allowed($block['priv'])) {
        return '';
    }

    $block = app\event(['layout.prerender.type.' . $block['type'], 'layout.prerender.id.' . $block['id']], $block);
    $data = ['html' => $block['call']($block)];
    $data = app\event(['layout.postrender.type.' . $block['type'], 'layout.postrender.id.' . $block['id']], $data);

    return $data['html'];
}

/**
 * Renders block with given ID
 */
function block(string $id): string
{
    return ($block = get($id)) ? render($block) : '';
}

/**
 * Renders child blocks
 */
function children(string $id): string
{
    $html = '';

    foreach (arr\order(arr\filter(get(), 'parent_id', $id), ['sort' => 'asc']) as $child) {
        $html .= render($child);
    }

    return $html;
}

/**
 * Returns block config from database item
 *
 * @throws DomainException
 */
function db(array $data): array
{
    static $base;

    if (empty($data['entity_id']) || ($data['_entity']['parent_id'] ?? null) !== 'block' || !($type = app\cfg('block', type($data['entity_id'])))) {
        throw new DomainException(app\i18n('Invalid data'));
    } elseif ($base === null) {
        $base = entity\item('block');
    }

    return ['type' => $type['id'], 'call' => $type['call'], 'cfg' => array_diff_key($data, $base)];
}

/**
 * Returns block type from block entity
 */
function type(string $entityId): string
{
    return preg_replace('#^block_#', '', $entityId);
}
