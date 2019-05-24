<?php
declare(strict_types = 1);

namespace cfg;

use app;
use arr;
use file;
use DomainException;

/**
 * Preloads all configuration data
 */
function preload(): array
{
    $data = [];

    load('i18n');

    foreach ([app\path('cfg'), app\path('ext.cfg')] as $path) {
        foreach (array_diff(scandir($path), ['.', '..']) as $name) {
            $id = basename($name, '.php');
            $file = $path . '/' . $name;

            if (!is_array($data[$id] ?? null) && (is_dir($file) || is_file($file) && $id !== $name)) {
                $data[$id] = load($id);
            }
        }
    }

    return $data;
}

/**
 * Loads configuration data with or without overwrites
 */
function load(string $id): array
{
    if (($cfg = & app\registry('cfg.' . $id)) === null) {
        if ($id === 'i18n') {
            $id = 'i18n/' . APP['lang'];
        }

        $data = file\load(app\path('cfg', $id . '.php'));
        $ext = file\load(app\path('ext.cfg', $id . '.php'));

        switch ($id) {
            case 'attr':
                $cfg = $data + $ext;
                break;
            case 'block':
                $cfg = load_block($data, $ext);
                break;
            case 'entity':
                $cfg = load_entity($data, $ext);
                break;
            case 'layout':
                $cfg = load_layout($data, $ext);
                break;
            case 'opt':
                $cfg = load_opt($data, $ext);
                break;
            case 'priv':
                $cfg = load_priv($data, $ext);
                break;
            case 'toolbar':
                $cfg = load_toolbar($data, $ext);
                break;
            case 'db':
            case 'event':
                $cfg = arr\extend($data, $ext);
                break;
            default:
                $cfg = array_replace($data, $ext);
        }
    }

    return $cfg;
}

/**
 * Loads block configuration
 *
 * @throws DomainException
 */
function load_block(array $data, array $ext): array
{
    $data += $ext;

    foreach ($data as $id => $type) {
        $data[$id] = arr\replace(APP['cfg']['block'], $type, ['id' => $id]);

        if (!is_callable($type['call'])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        }
    }

    return $data;
}

/**
 * Loads entity configurations
 *
 * @throws DomainException
 */
function load_entity(array $data, array $ext): array
{
    $data += $ext;
    $cfg = load('attr');
    $dbCfg = load('db');

    // Entities
    foreach ($data as $entityId => $entity) {
        $entity = arr\replace(APP['cfg']['entity'], $entity, ['id' => $entityId]);

        if (!$entity['name']
            || !$entity['db']
            || !$entity['type'] && !($entity['type'] = $dbCfg[$entity['db']]['type'] ?? null)
            || $entity['parent_id'] && (empty($data[$entity['parent_id']]) || !empty($data[$entity['parent_id']]['parent_id']))
            || !$entity['parent_id'] && !arr\has($entity['attr'], ['id', 'name'], true)
        ) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent_id']) {
            $entity['attr'] = arr\extend($data[$entity['parent_id']]['attr'], $entity['attr']);
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

            $attr = arr\replace(APP['cfg']['attr'], $cfg[$attr['type']], $attr, ['id' => $attrId, 'name' => app\i18n($attr['name'])]);

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
 * Load layout configuration
 */
function load_layout(array $data, array $ext): array
{
    foreach ($ext as $key => $cfg) {
        foreach ($cfg as $id => $block) {
            $data[$key][$id] = empty($data[$key][$id]) ? $block : arr\extend($data[$key][$id], $block);
        }
    }

    return $data;
}

/**
 * Loads option configuration
 */
function load_opt(array $data, array $ext): array
{
    $data = array_replace($data, $ext);

    foreach ($data as $key => $opt) {
        $data[$key] = array_map('app\i18n', $opt);
    }

    return $data;
}

/**
 * Loads privilege configuration
 */
function load_priv(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['priv'], $item);
        $item['name'] = $item['name'] ? app\i18n($item['name']) : '';
        $data[$id] = $item;
    }

    foreach (load('entity') as $entity) {
        if (in_array('edit', $entity['action']) && in_array('page', [$entity['id'], $entity['parent_id']])) {
            $id = $entity['id'] . '-publish';
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n('Publish');
            $data[$id] = arr\replace(APP['cfg']['priv'], $data[$id]);
        }

        foreach ($entity['action'] as $action) {
            $id = $entity['id'] . '/' . $action;
            $data[$id]['name'] = $entity['name'] . ' ' . app\i18n(ucfirst($action));
            $data[$id] = arr\replace(APP['cfg']['priv'], $data[$id]);
        }
    }

    return $data;
}

/**
 * Loads toolbar configuration
 *
 * @throws DomainException
 */
function load_toolbar(array $data, array $ext): array
{
    $data = arr\order(arr\extend($data, $ext), ['parent_id' => 'asc', 'sort' => 'asc', 'id' => 'asc']);
    $parentId = null;
    $sort = 0;

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['toolbar'], $item, ['id' => $id]);

        if (!$item['name'] || $item['parent_id'] && empty($data[$item['parent_id']])) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($parentId !== $item['parent_id']) {
            $sort = 0;
        }

        $item['name'] = app\i18n($item['name']);
        $item['url'] = $item['action'] ? app\url($item['action']) : $item['url'];
        $item['sort'] = ++$sort;
        $data[$id] = $item;
        $parentId = $item['parent_id'];
    }

    foreach ($data as $id => $item) {
        $item['pos'] = ($item['parent_id'] ? $data[$item['parent_id']]['pos'] . '.' : '') . str_pad((string) $item['sort'], 5, '0', STR_PAD_LEFT);
        $item['level'] = $item['parent_id'] ? $data[$item['parent_id']]['level'] + 1 : 1;
        $data[$id] = $item;
    }

    return arr\order($data, ['pos' => 'asc']);
}
