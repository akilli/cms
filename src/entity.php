<?php
declare(strict_types = 1);

namespace entity;

use arr;
use attr;
use app;
use DomainException;
use Throwable;

/**
 * Size entity
 */
function size(string $entityId, array $crit = []): int
{
    $entity = app\cfg('entity', $entityId);
    $opt = arr\replace(APP['entity.opt'], ['mode' => 'size']);

    try {
        return ($entity['type'] . '\load')($entity, $crit, $opt)[0];
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not load data');
    }

    return 0;
}

/**
 * Load one entity
 */
function one(string $entityId, array $crit = [], array $opt = []): array
{
    $entity = app\cfg('entity', $entityId);
    $data = [];
    $opt = arr\replace(APP['entity.opt'], $opt, ['mode' => 'one', 'limit' => 1]);

    try {
        if ($data = ($entity['type'] . '\load')($entity, $crit, $opt)) {
            $data = load($entity, $data);
        }
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not load data');
    }

    return $data;
}

/**
 * Load entity collection
 */
function all(string $entityId, array $crit = [], array $opt = []): array
{
    $entity = app\cfg('entity', $entityId);
    $opt = arr\replace(APP['entity.opt'], $opt, ['mode' => 'all']);

    if ($opt['select']) {
        foreach (array_unique(['id', $opt['index']]) as $k) {
            if (!in_array($k, $opt['select'])) {
                $opt['select'][] = $k;
            }
        }
    }

    try {
        $data = ($entity['type'] . '\load')($entity, $crit, $opt);

        foreach ($data as $key => $item) {
            $data[$key] = load($entity, $item);
        }

        return array_column($data, null, $opt['index']);
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not load data');
    }

    return [];
}

/**
 * Save entity
 */
function save(string $entityId, array & $data): bool
{
    $id = $data['id'] ?? null;
    $tmp = $data;

    if ($id && ($old = one($entityId, [['id', $id]]))) {
        $tmp['_old'] = $old;
        $tmp['_entity'] = $old['_entity'];
        unset($tmp['_old']['_entity'], $tmp['_old']['_old']);
    } else {
        $tmp['_old'] = [];
        $tmp['_entity'] = app\cfg('entity', $entityId);
    }

    $attrIds = [];

    foreach (array_intersect_key($tmp, $tmp['_entity']['attr']) as $attrId => $val) {
        if (($val === null || $val === '') && attr\ignorable($tmp, $tmp['_entity']['attr'][$attrId])) {
            unset($data[$attrId], $tmp[$attrId]);
        } else {
            $attrIds[] = $attrId;
        }
    }

    if (!$attrIds) {
        app\msg('No changes');
        return false;
    }

    $tmp = event('prefilter', $tmp);

    foreach ($attrIds as $attrId) {
        try {
            $tmp[$attrId] = attr\filter($tmp, $tmp['_entity']['attr'][$attrId]);
        } catch (DomainException $e) {
            $tmp['_error'][$attrId] = $e->getMessage();
        } catch (Throwable $e) {
            $tmp['_error'][$attrId] = app\i18n('Could not validate value');
        }
    }

    $tmp = event('postfilter', $tmp);

    if (!empty($tmp['_error'])) {
        $data['_error'] = $tmp['_error'];
        app\msg('Could not save data');
        return false;
    }

    foreach ($attrIds as $key => $attrId) {
        if (array_key_exists($attrId, $tmp['_old']) && $tmp[$attrId] === $tmp['_old'][$attrId]) {
            unset($attrIds[$key]);
        }
    }

    if (!$attrIds) {
        app\msg('No changes');
        return false;
    }

    try {
        ($tmp['_entity']['type'] . '\trans')(
            function () use (& $tmp): void {
                $tmp = event('presave', $tmp);
                $tmp = ($tmp['_entity']['type'] . '\save')($tmp);
                $tmp = event('postsave', $tmp);
            },
            $tmp['_entity']['db']
        );
        app\msg('Successfully saved data');
        $data = $tmp;

        return true;
    } catch (Throwable $e) {
        app\msg('Could not save data');
    }

    return false;
}

/**
 * Delete entity
 */
function delete(string $entityId, array $crit = [], array $opt = []): bool
{
    if (!($all = all($entityId, $crit, $opt)) || !($cur = current($all))) {
        app\msg('Nothing to delete');
        return false;
    }

    try {
        ($cur['_entity']['type'] . '\trans')(
            function () use ($all): void {
                foreach ($all as $data) {
                    $data = event('predelete', $data);
                    ($data['_entity']['type'] . '\delete')($data);
                    event('postdelete', $data);
                }
            },
            $cur['_entity']['db']
        );
        app\msg('Successfully deleted data');

        return true;
    } catch (DomainException $e) {
        app\msg($e->getMessage());
    } catch (Throwable $e) {
        app\msg('Could not delete data');
    }

    return false;
}

/**
 * Retrieve empty entity
 *
 * @throws DomainException
 */
function item(string $entityId): array
{
    if (!$entity = app\cfg('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    return array_fill_keys(array_keys($entity['attr']), null) + ['_old' => [], '_entity' => $entity, '_error' => null];
}

/**
 * Load entity
 */
function load(array $entity, array $data): array
{
    foreach (array_intersect_key($data, $entity['attr']) as $attrId => $val) {
        $data[$attrId] = attr\cast($val, $entity['attr'][$attrId]);
    }

    $data += ['_old' => $data, '_entity' => $entity];

    return event('load', $data);
}

/**
 * Dispatches multiple entity events
 */
function event(string $name, array $data): array
{
    $entity = $data['_entity'];
    $ev = ['entity.' . $name, 'entity.' . $name . '.type.' . $entity['type'], 'entity.' . $name . '.db.' . $entity['db']];

    if ($entity['parent_id']) {
        $ev[] = 'entity.' . $name . '.id.' . $entity['parent_id'];
    }

    $ev[] = 'entity.' . $name . '.id.' . $entity['id'];

    return app\event($ev, $data);
}
