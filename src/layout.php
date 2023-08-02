<?php
declare(strict_types=1);

namespace layout;

use app;
use arr;
use DomainException;
use str;

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
function render_id(string $id): string
{
    return ($block = app\data('layout', $id)) ? render($block) : '';
}

/**
 * Renders child blocks
 */
function render_children(string $id): string
{
    $html = '';

    foreach (arr\order(arr\filter(app\data('layout'), 'parent_id', $id), ['sort' => 'asc']) as $child) {
        $html .= render($child);
    }

    return $html;
}

/**
 * Initializes and renders block
 */
function render_init(array $block): string
{
    return render(init($block));
}

/**
 * Initializes block
 *
 * @throws DomainException
 */
function init(array $block): array
{
    if (empty($block['type']) || !($type = app\cfg('block', $block['type']))) {
        throw new DomainException(app\i18n('Invalid block'));
    }

    unset($block['call']);
    $block['cfg'] = $type['cfg'] ? arr\replace($type['cfg'], $block['cfg'] ?? []) : [];

    return arr\replace(APP['block'], $type, $block);
}

/**
 * Dispatches multiple layout events
 */
function event(string $name, array $data): array
{
    $pre = app\id('layout', $name);

    return app\event([$pre, app\id($pre, 'type', $data['type']), app\id($pre, 'id', $data['id'])], $data);
}
