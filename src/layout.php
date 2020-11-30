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
 * Returns block config for DB block and falls back to dbblock if no custom block type is configured
 *
 * @throws DomainException
 */
function db_cfg(array $data, array $block = []): array
{
    if (empty($data['entity_id']) || ($data['_entity']['parent_id'] ?? null) !== 'block') {
        throw new DomainException(app\i18n('Invalid data'));
    }

    return cfg(['type' => app\cfg('block', $data['entity_id'])['id'] ?? 'dbblock', 'cfg' => ['data' => $data]] + $block);
}

/**
 * Renders DB placeholder tag with given ID (format: `{entity_id}-{id}`)
 */
function db_render(string $id): string
{
    $block = cfg(['type' => 'db']);
    [$block['cfg']['entity_id'], $block['cfg']['id']] = explode('-', $id);

    return render($block);
}

/**
 * Dispatches multiple layout events
 */
function event(string $name, array $data): array
{
    $pre = 'layout:' . $name;

    return app\event([$pre, $pre . ':type:' . $data['type'], $pre . ':id:' . $data['id']], $data);
}
