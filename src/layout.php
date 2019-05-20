<?php
declare(strict_types = 1);

namespace layout;

use app;
use arr;
use entity;
use DomainException;

/**
 * Renders block
 */
function render(array $block): string
{
    if (!$block['active'] || $block['priv'] && !app\allowed($block['priv'])) {
        return '';
    }

    $block = event('prerender', $block);
    $block['html'] = $block['call']($block);
    $block = event('postrender', $block);

    return $block['html'];
}

/**
 * Dispatches multiple layout events
 */
function event(string $name, array $data): array
{
    return app\event(['layout.' . $name, 'layout.' . $name . '.type.' . $data['type'], 'layout.' . $name . '.id.' . $data['id']], $data);
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

/**
 * Generates layout ID for DB block
 */
function db_id(array $data): string
{
    return 'layout-' . $data['parent_id'] .'-' . $data['name'];
}

/**
 * Replaces all DB placeholder tags, i.e. `<block id="entity_id:id" />`, with actual blocks
 */
function db_replace(string $html): string
{
    $pattern = '#<block id="%s"(?:[^>]*)>#s';

    if (preg_match_all(sprintf($pattern, '([a-z_]+)-(\d+)'), $html, $match)) {
        $data = [];

        foreach ($match[1] as $key => $entityId) {
            $data[$entityId][] = $match[2][$key];
        }

        foreach ($data as $entityId => $ids) {
            foreach (entity\all($entityId, [['id', $ids]]) as $item) {
                $block = arr\replace(APP['layout'], db($item), ['id' => uniqid('block-')]);
                $html = preg_replace(sprintf($pattern, $entityId . '-' . $item['id']), render($block), $html);
            }
        }
    }

    return preg_replace('#<block(?:[^>]*)>#s', '', $html);
}

/**
 * Renders DB placeholder tag with given ID (`entity_id:id`)
 */
function db_render(string $id): string
{
    $block = arr\replace(APP['layout'], app\cfg('block', 'db'), ['id' => uniqid('block-')]);
    [$block['cfg']['entity_id'], $block['cfg']['id']] = explode('-', $id);

    return render($block);
}
