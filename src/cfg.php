<?php
declare(strict_types=1);

namespace cfg;

use app;
use arr;
use file;
use DomainException;

/**
 * Backs up configuration
 */
function backup(): void
{
    file\save(APP['path']['tmp'] . '/cfg.php', preload());
}

/**
 * Restores configuration
 */
function restore(): array
{
    return file\one(APP['path']['tmp'] . '/cfg.php');
}

/**
 * Preloads all configuration data
 */
function preload(): array
{
    $data = [];

    load('i18n');

    foreach (array_filter([APP['path']['cfg'], APP['path']['ext.cfg']], 'is_dir') as $path) {
        foreach (array_diff(scandir($path), ['.', '..']) as $name) {
            $id = basename($name, APP['php.ext']);
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
        $path = APP['path']['cfg'] . '/' . $id;
        $extPath = APP['path']['ext.cfg'] . '/' . $id;

        if (is_dir($path) || !is_file($path . APP['php.ext']) && is_dir($extPath)) {
            $data = file\all($path);
            $ext = file\all($extPath);
        } else {
            $data = file\one($path . APP['php.ext']);
            $ext = file\one($extPath . APP['php.ext']);
        }

        $cfg = match ($id) {
            'attr' => $data + $ext,
            'block' => block($data, $ext),
            'entity' => entity($data, $ext),
            'layout' => layout($data, $ext),
            'opt' => opt($data, $ext),
            'priv' => priv($data, $ext),
            'toolbar' => toolbar($data, $ext),
            'db', 'event' => arr\extend($data, $ext),
            default => array_replace($data, $ext),
        };
    }

    return $cfg;
}

/**
 * Loads block configuration
 *
 * @throws DomainException
 */
function block(array $data, array $ext): array
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
function entity(array $data, array $ext): array
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
            || $entity['unique'] !== array_filter($entity['unique'], 'is_array')
        ) {
            throw new DomainException(app\i18n('Invalid configuration'));
        } elseif ($entity['parent_id']) {
            $entity['unique'] = array_merge($data[$entity['parent_id']]['unique'], $entity['unique']);
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
                || in_array($attr['type'], ['entity', 'entity[]']) && empty($attr['ref'])
                || !empty($attr['ref']) && (empty($data[$attr['ref']]['attr']['id']['type']) || empty($cfg[$data[$attr['ref']]['attr']['id']['type']]))
            ) {
                throw new DomainException(app\i18n('Invalid configuration'));
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
function layout(array $data, array $ext): array
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
function opt(array $data, array $ext): array
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
function priv(array $data, array $ext): array
{
    $data = arr\extend($data, $ext);

    foreach ($data as $id => $item) {
        $item = arr\replace(APP['cfg']['priv'], $item);
        $item['name'] = $item['name'] ? app\i18n($item['name']) : '';
        $data[$id] = $item;
    }

    foreach (load('entity') as $entity) {
        foreach ($entity['action'] as $action) {
            $id = $entity['id'] . ':' . $action;
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
function toolbar(array $data, array $ext): array
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
