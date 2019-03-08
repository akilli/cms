<?php
declare(strict_types = 1);

namespace layout;

use app;
use arr;
use DomainException;

/**
 * Gets registered layout block(s)
 */
function data(string $id = null): ?array
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
    return ($block = data($id)) ? render($block) : '';
}

/**
 * Renders child blocks
 */
function children(string $id): string
{
    $html = '';

    foreach (arr\order(arr\filter(data(), 'parent_id', $id), ['sort' => 'asc']) as $child) {
        $html .= render($child);
    }

    return $html;
}

/**
 * Returns block config for DB block and falls back to content block if no custom type is configured
 *
 * @throws DomainException
 */
function db(array $data): array
{
    if (empty($data['entity_id']) || ($data['_entity']['parent_id'] ?? null) !== 'block') {
        throw new DomainException(app\i18n('Invalid data'));
    }

    $type = app\cfg('block', $data['entity_id']) ?: app\cfg('block', 'content');

    return ['type' => $type['id'], 'call' => $type['call'], 'cfg' => ['data' => $data]];
}
