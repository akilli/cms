<?php
declare(strict_types = 1);

namespace entity;

use arr;
use attr;
use app;
use cfg;
use DomainException;
use Throwable;

/**
 * Size entity
 *
 * @throws DomainException
 */
function size(string $entityId, array $crit = []): int
{
    if (!$entity = cfg\data('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    $opt = ['mode' => 'size'] + APP['entity.opt'];

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
 *
 * @throws DomainException
 */
function one(string $entityId, array $crit = [], array $opt = []): array
{
    if (!$entity = cfg\data('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    $data = [];
    $opt = ['mode' => 'one', 'limit' => 1] + arr\replace(APP['entity.opt'], $opt);

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
 *
 * @throws DomainException
 */
function all(string $entityId, array $crit = [], array $opt = []): array
{
    if (!$entity = cfg\data('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    $opt = ['mode' => 'all'] + arr\replace(APP['entity.opt'], $opt);

    if ($opt['select'] && ($keys = array_diff(array_unique(['id', $opt['index']]), $opt['select']))) {
        array_unshift($opt['select'], ...$keys);
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
 *
 * @throws DomainException
 */
function save(string $entityId, array & $data): bool
{
    if (!$entity = cfg\data('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    } elseif ($entity['readonly']) {
        throw new DomainException(app\i18n('Entity %s is readonly', $entity['id']));
    }

    $id = $data['id'] ?? null;
    $tmp = $data;
    $tmp['_old'] = [];
    $tmp['_entity'] = $entity;

    if ($id && ($old = one($entity['id'], [['id', $id]]))) {
        $tmp['_old'] = $old;
        unset($tmp['entity_id'], $tmp['_old']['_entity'], $tmp['_old']['_old']);
    } elseif ($entity['parent_id']) {
        $tmp['entity_id'] = $entity['id'];
    }

    $attrIds = [];

    foreach (array_intersect(array_keys($tmp), array_keys($entity['attr'])) as $attrId) {
        $attr = $entity['attr'][$attrId];
        $tmp[$attrId] = attr\cast($tmp[$attrId], $attr);
        $ignorable = ($tmp[$attrId] === null || $tmp[$attrId] === '') && $attr['required'] && attr\ignorable($tmp, $attr);
        $unchanged = array_key_exists($attrId, $tmp['_old']) && $tmp[$attrId] === $tmp['_old'][$attrId];

        if ($ignorable || $unchanged) {
            unset($data[$attrId], $tmp[$attrId]);
        } else {
            $attrIds[] = $attrId;
        }
    }

    if (!$attrIds) {
        app\msg('No changes');
        return false;
    }

    $tmp = event('prevalidate', $tmp);

    foreach ($attrIds as $attrId) {
        try {
            $tmp[$attrId] = attr\validator($tmp, $entity['attr'][$attrId]);
        } catch (DomainException $e) {
            $tmp['_error'][$attrId][] = $e->getMessage();
        } catch (Throwable $e) {
            app\log($e);
            $tmp['_error'][$attrId][] = app\i18n('Could not validate value');
        }
    }

    $tmp = event('postvalidate', $tmp);

    if (!empty($tmp['_error'])) {
        $data['_error'] = $tmp['_error'];
        app\msg('Could not save data');
        return false;
    }

    foreach ($attrIds as $key => $attrId) {
        if (array_key_exists($attrId, $tmp['_old']) && $tmp[$attrId] === $tmp['_old'][$attrId]) {
            unset($data[$attrId], $tmp[$attrId], $attrIds[$key]);
        }
    }

    if (!$attrIds) {
        app\msg('No changes');
        return false;
    }

    try {
        ($entity['type'] . '\trans')(
            function () use (& $tmp): void {
                $tmp = event('presave', $tmp);
                $tmp = ($tmp['_entity']['type'] . '\save')($tmp);
                $tmp = event('postsave', $tmp);
            },
            $entity['db']
        );
        app\msg('Successfully saved data');
        $data = $tmp;

        if (empty($data['id']) && !empty($tmp['_old']['id'])) {
            $data['id'] = $tmp['_old']['id'];
        }

        return true;
    } catch (Throwable $e) {
        app\log($e);
        app\msg('Could not save data');
    }

    return false;
}

/**
 * Delete entity
 *
 * @throws DomainException
 */
function delete(string $entityId, array $crit = [], array $opt = []): bool
{
    if (!$entity = cfg\data('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    } elseif ($entity['readonly']) {
        throw new DomainException(app\i18n('Entity %s is readonly', $entity['id']));
    }

    if (!$all = all($entity['id'], $crit, $opt)) {
        app\msg('Nothing to delete');
        return false;
    }

    try {
        ($entity['type'] . '\trans')(
            function () use ($all): void {
                foreach ($all as $data) {
                    $data = event('predelete', $data);
                    ($data['_entity']['type'] . '\delete')($data);
                    event('postdelete', $data);
                }
            },
            $entity['db']
        );
        app\msg('Successfully deleted data');

        return true;
    } catch (DomainException $e) {
        app\msg($e->getMessage());
    } catch (Throwable $e) {
        app\log($e);
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
    if (!$entity = cfg\data('entity', $entityId)) {
        throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    }

    return array_fill_keys(array_keys($entity['attr']), null) + ['_old' => [], '_entity' => $entity, '_error' => []];
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
