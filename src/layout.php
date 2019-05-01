<?php
declare(strict_types = 1);

namespace layout;

use app;
use arr;
use cfg;
use entity;
use request;
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

    $type = cfg\data('block', $data['entity_id']) ?: cfg\data('block', 'content');

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

    if (preg_match_all(sprintf($pattern, '([a-z_]+)-([0-9]+)'), $html, $match)) {
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
    $block = arr\replace(APP['layout'], cfg\data('block', 'db'), ['id' => uniqid('block-')]);
    [$block['cfg']['entity_id'], $block['cfg']['id']] = explode('-', $id);

    return render($block);
}

/**
 * Data listener
 *
 * @throws DomainException
 */
function listener_data(array $data): array
{
    $cfg = cfg\data('layout');
    $type = cfg\data('block');
    $url = request\data('url');
    $keys = ['_all_', app\data('area')];

    if (app\data('invalid')) {
        $keys[] = '_invalid_';
    } else {
        $entityId = app\data('entity_id');
        $action = app\data('action');
        $keys[] = $action;

        if ($parentId = app\data('parent_id')) {
            $keys[] = $parentId . '/' . $action;
        }

        $keys[] = $entityId . '/' . $action;
        $keys[] = $url;

        if (($page = app\data('page')) && ($dbLayout = entity\all('layout_page', [['page_id', $page['id']]]))) {
            $dbBlocks = [];

            foreach (arr\group($dbLayout, 'entity_id', 'block_id') as $eId => $ids) {
                foreach (entity\all($eId, [['id', $ids]]) as $item) {
                    $dbBlocks[$item['id']] = $item;
                }
            }

            foreach ($dbLayout as $id => $item) {
                $c = ['parent_id' => $item['parent_id'], 'sort' => $item['sort']];
                $cfg[$url][db_id($item)] = db($dbBlocks[$item['block_id']]) + $c;
            }
        }
    }

    foreach ($keys as $key) {
        if (!empty($cfg[$key])) {
            foreach ($cfg[$key] as $id => $block) {
                $data[$id] = empty($data[$id]) ? $block : cfg\load_block($data[$id], $block);
            }
        }
    }

    foreach ($data as $id => $block) {
        if (empty($block['type']) || empty($type[$block['type']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        unset($block['call']);
        $data[$id] = arr\replace(APP['layout'], $type[$block['type']], $block, ['id' => $id]);
    }

    return $data;
}

/**
 * Postrender root listener
 */
function listener_postrender_root(array $data): array
{
    $data['html'] = db_replace($data['html']);

    return $data;
}
