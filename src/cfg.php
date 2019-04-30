<?php
declare(strict_types = 1);

namespace cfg;

use app;
use arr;
use DomainException;

/**
 * Loads configuration data with or without overwrites
 */
function load(string $id): array
{
    $file = app\path('cfg', $id . '.php');
    $data = is_readable($file) ? include $file : [];
    $extFile = app\path('ext.cfg', $id . '.php');

    if (!is_readable($extFile) || !($ext = include $extFile)) {
        return $data;
    }

    if (in_array($id, ['attr', 'block', 'entity'])) {
        return $data + $ext;
    }

    if ($id === 'layout') {
        return load_layout($data, $ext);
    }

    return array_replace_recursive($data, $ext);
}

/**
 * Load layout configuration
 */
function load_layout(array $data, array $ext = []): array
{
    foreach ($ext as $key => $cfg) {
        foreach ($cfg as $id => $block) {
            $data[$key][$id] = empty($data[$key][$id]) ? $block : load_block($data[$key][$id], $block);
        }
    }

    return $data;
}

/**
 * Load block configuration
 */
function load_block(array $data, array $ext = []): array
{
    if (!empty($ext['cfg'])) {
        $data['cfg'] = empty($data['cfg']) ? $ext['cfg'] : array_replace($data['cfg'], $ext['cfg']);
    }

    unset($ext['cfg']);

    return array_replace($data, $ext);
}

/**
 * Block config listener
 *
 * @throws DomainException
 */
function listener_block(array $data): array
{
    foreach ($data as $id => $type) {
        $data[$id] = arr\replace(APP['block'], $type, ['id' => $id]);

        if (!is_callable($type['call'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }
    }

    return $data;
}

/**
 * Entity config listener
 *
 * @throws DomainException
 */
function listener_entity(array $data): array
{
    $cfg = app\cfg('attr');

    // Entities
    foreach ($data as $entityId => $entity) {
        $entity = arr\replace(APP['entity'], $entity, ['id' => $entityId]);

        if (!$entity['name']
            || !$entity['db']
            || !$entity['type'] && !($entity['type'] = app\cfg('db', $entity['db'])['type'] ?? null)
            || $entity['parent_id'] && (empty($data[$entity['parent_id']]) || !empty($data[$entity['parent_id']]['parent_id']))
            || !$entity['parent_id'] && !arr\has($entity['attr'], ['id', 'name'], true)
        ) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent_id']) {
            $entity['attr'] = array_replace_recursive($data[$entity['parent_id']]['attr'], $entity['attr']);
        }

        $data[$entityId] = $entity;
    }

    // Attributes
    foreach ($data as $entityId => $entity) {
        foreach ($entity['attr'] as $attrId => $attr) {
            if (empty($attr['name'])
                || empty($attr['type'])
                || empty($cfg[$attr['type']])
                || in_array($attr['type'], ['entity', 'multientity']) && empty($attr['ref'])
                || !empty($attr['ref']) && (empty($data[$attr['ref']]['attr']['id']['type']) || empty($cfg[$data[$attr['ref']]['attr']['id']['type']]))
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            // Auto-determine type from reference ID attribute
            if (in_array($attr['type'], ['entity', 'multientity'])) {
                $attr['backend'] = $cfg[$data[$attr['ref']]['attr']['id']['type']]['backend'];
            }

            $attr = arr\replace(APP['attr'], $cfg[$attr['type']], $attr, ['id' => $attrId, 'name' => app\i18n($attr['name'])]);

            if (!in_array($attr['backend'], APP['backend'])
                || !$attr['frontend']
                || !is_callable($attr['frontend'])
                || $attr['filter'] && !is_callable($attr['filter'])
                || $attr['min'] > 0 && $attr['max'] > 0 && $attr['min'] > $attr['max']
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
            }

            $attr['filter'] = $attr['filter'] ?: $attr['frontend'];
            $attr['opt.frontend'] = $attr['opt.frontend'] ?: $attr['opt'];
            $attr['opt.filter'] = $attr['opt.filter'] ?: $attr['opt'];
            $attr['opt.validator'] = $attr['opt.validator'] ?: $attr['opt'];
            $attr['opt.viewer'] = $attr['opt.viewer'] ?: $attr['opt'];
            $entity['attr'][$attrId] = $attr;
        }

        $entity['name'] = app\i18n($entity['name']);
        $data[$entityId] = $entity;
    }

    return $data;
}

/**
 * I18n config listener
 */
function listener_i18n(array $data): array
{
    return $data + load('i18n/' . app\data('lang'));
}

/**
 * Option config listener
 */
function listener_opt(array $data): array
{
    foreach ($data as $key => $opt) {
        $data[$key] = array_map('app\i18n', $opt);
    }

    return $data;
}

/**
 * Privilege config listener
 */
function listener_priv(array $data): array
{
    foreach ($data as $id => $item) {
        $item = arr\replace(APP['priv'], $item);
        $item['name'] = $item['name'] ? app\i18n($item['name']) : '';
        $data[$id] = $item;
    }

    foreach (app\cfg('entity') as $entity) {
        if (in_array('edit', $entity['action']) && in_array('page', [$entity['id'], $entity['parent_id']])) {
            $id = $entity['id'] . '-publish';
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n('Publish');
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }

        foreach ($entity['action'] as $action) {
            $id = $entity['id'] . '/' . $action;
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n(ucfirst($action));
            $data[$id] = arr\replace(APP['priv'], $data[$id]);
        }
    }

    return $data;
}

/**
 * Toolbar config listener
 *
 * @throws DomainException
 */
function listener_toolbar(array $data): array
{
    foreach ($data as $id => $item) {
        if (empty($item['name']) || !empty($item['parent_id']) && empty($data[$item['parent_id']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }

        $item = arr\replace(APP['toolbar'], $item, ['id' => $id, 'name' => app\i18n($item['name']), 'level' => 1]);
        $item['url'] = $item['action'] ? app\url($item['action']) : $item['url'];
        $item['sort'] = str_pad((string) $item['sort'], 5, '0', STR_PAD_LEFT) . '-' . $id;

        if ($item['parent_id']) {
            $item['level'] = $data[$item['parent_id']]['level'] + 1;
            $item['sort'] = $data[$item['parent_id']]['sort'] . '/' . $item['sort'];
        }

        $data[$id] = $item;
    }

    return arr\order($data, ['sort' => 'asc']);
}
