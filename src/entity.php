<?php
declare(strict_types = 1);

namespace entity;

use arr;
use attr;
use app;
use sql;
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

    $aIds = [];

    foreach (array_intersect_key($tmp, $tmp['_entity']['attr']) as $aId => $val) {
        if (($val === null || $val === '') && attr\ignorable($tmp['_entity']['attr'][$aId], $tmp)) {
            unset($data[$aId], $tmp[$aId]);
        } else {
            $aIds[] = $aId;
        }
    }

    if (!$aIds) {
        app\msg('No changes');
        return false;
    }

    $tmp = event('prefilter', $tmp);

    foreach ($aIds as $aId) {
        try {
            $tmp[$aId] = attr\filter($tmp['_entity']['attr'][$aId], $tmp);
        } catch (Throwable $e) {
            $tmp['_error'][$aId] = $e->getMessage();
        }
    }

    $tmp = event('postfilter', $tmp);

    if (!empty($tmp['_error'])) {
        $data['_error'] = $tmp['_error'];
        app\msg('Could not save data');
        return false;
    }

    foreach ($aIds as $key => $aId) {
        if (array_key_exists($aId, $tmp['_old']) && $tmp[$aId] === $tmp['_old'][$aId]) {
            unset($aIds[$key]);
        }
    }

    if (!$aIds) {
        app\msg('No changes');
        return false;
    }

    try {
        sql\trans(
            function () use (& $tmp): void {
                $tmp = event('presave', $tmp);
                $tmp = ($tmp['_entity']['type'] . '\save')($tmp);
                $tmp = event('postsave', $tmp);
            }
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
    if (!$all = all($entityId, $crit, $opt)) {
        app\msg('Nothing to delete');
        return false;
    }

    try {
        sql\trans(
            function () use ($all): void {
                foreach ($all as $data) {
                    $data = event('predelete', $data);
                    ($data['_entity']['type'] . '\delete')($data);
                    event('postdelete', $data);
                }
            }
        );
        app\msg('Successfully deleted data');

        return true;
    } catch (Throwable $e) {
        app\msg('Could not delete data');
    }

    return false;
}

/**
 * Retrieve empty entity
 */
function item(array $entity): array
{
    return array_fill_keys(array_keys($entity['attr']), null) + ['_old' => [], '_entity' => $entity];
}

/**
 * Load entity
 */
function load(array $entity, array $data): array
{
    foreach (array_intersect_key($data, $entity['attr']) as $aId => $val) {
        $data[$aId] = attr\cast($entity['attr'][$aId], $val);
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
    $ev = ['entity.' . $name, 'entity.type.' . $name . '.' . $entity['type']];

    if ($entity['parent']) {
        $ev[] = 'entity.' . $name . '.' . $entity['parent'];
    }

    $ev[] = 'entity.' . $name . '.' . $entity['id'];

    return app\event($ev, $data);
}
