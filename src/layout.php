<?php
declare(strict_types=1);

namespace layout;

use app;
use arr;
use str;
use DomainException;

/**
 * Renders block
 */
function render(array $block): string
{
    if (!$block['active'] || $block['privilege'] && !app\allowed($block['privilege'])) {
        return '';
    }

    $block['id'] = $block['id'] ?: str\uniq('block-');
    $block = event('prerender', $block);
    $block['html'] = $block['call']($block);
    $block = event('postrender', $block);

    return $block['html'];
}

/**
 * Renders block with given ID
 */
function block(string $id): string
{
    return ($block = app\data('layout', $id)) ? render($block) : '';
}

/**
 * Renders child blocks
 */
function children(string $id): string
{
    $html = '';

    foreach (arr\order(arr\filter(app\data('layout'), 'parent_id', $id), ['sort' => 'asc']) as $child) {
        $html .= render($child);
    }

    return $html;
}

/**
 * Returns full block configuration
 *
 * @throws DomainException
 */
function cfg(array $block): array
{
    if (empty($block['type']) || !($type = app\cfg('block', $block['type']))) {
        throw new DomainException(app\i18n('Invalid configuration'));
    }

    unset($block['call'], $type['id']);
    $block['cfg'] = $type['cfg'] ? arr\replace($type['cfg'], $block['cfg'] ?? []) : [];

    return arr\replace(APP['data']['layout'], $type, $block);
}

/**
 * Returns DB blockwith given data
 *
 * @throws DomainException
 */
function db_render_data(array $data): string
{
    return render(cfg(['type' => 'db', 'cfg' => ['data' => $data]]));
}

/**
 * Renders DB block with given ID (format: `{entity_id}-{id}`)
 */
function db_render_id(string $id): string
{
    [$entityId, $blockId] = explode('-', $id);

    return render(cfg(['type' => 'db', 'cfg' => ['entity_id' => $entityId, 'id' => $blockId]]));
}

/**
 * Dispatches multiple layout events
 */
function event(string $name, array $data): array
{
    $pre = 'layout:' . $name;

    return app\event([$pre, $pre . ':type:' . $data['type'], $pre . ':id:' . $data['id']], $data);
}
