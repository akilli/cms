<?php
declare(strict_types=1);

namespace entity;

use attr;
use app;
use DomainException;
use Throwable;

/**
 * Size entity
 *
 * @throws DomainException
 */
function size(string $entityId, array $crit = []): int
{
    $entity = app\cfg('entity', $entityId) ?: throw new DomainException(app\i18n('Invalid entity %s', $entityId));

    try {
        return ($entity['type'] . '\size')($entity, $crit);
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
    }

    return 0;
}

/**
 * Load one entity
 *
 * @throws DomainException
 */
function one(string $entityId, array $crit = [], array $select = [], array $order = [], int $offset = 0): ?array
{
    $entity = app\cfg('entity', $entityId) ?: throw new DomainException(app\i18n('Invalid entity %s', $entityId));
    $data = [];

    try {
        if ($data = ($entity['type'] . '\one')($entity, $crit, $select, $order, $offset)) {
            $data = load($entity, $data);
        }
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
    }

    return $data;
}

/**
 * Load entity collection
 *
 * @throws DomainException
 */
function all(
    string $entityId,
    array $crit = [],
    array $select = [],
    array $order = [],
    int $limit = 0,
    int $offset = 0,
    string $index = 'id'
): array
{
    $entity = app\cfg('entity', $entityId) ?: throw new DomainException(app\i18n('Invalid entity %s', $entityId));

    if ($select && ($keys = array_diff(array_unique(['id', $index]), $select))) {
        array_unshift($select, ...$keys);
    }

    try {
        $data = ($entity['type'] . '\all')($entity, $crit, $select, $order, $limit, $offset);
        $data = array_map(fn($item) => load($entity, $item), $data);
        return array_column($data, null, $index);
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not load data'));
    }

    return [];
}

/**
 * Save entity
 *
 * @throws DomainException
 */
function save(string $entityId, array &$data): bool
{
    $entity = app\cfg('entity', $entityId) ?: throw new DomainException(app\i18n('Invalid entity %s', $entityId));

    if ($entity['readonly']) {
        throw new DomainException(app\i18n('Entity %s is readonly', $entity['id']));
    }

    $id = $data['id'] ?? null;
    $tmp = init($entity, $data);

    if ($id && ($old = one($entity['id'], crit: [['id', $id]]))) {
        $tmp['_old'] = uninit($old);
        unset($tmp['entity_id']);
    } elseif ($entity['parent_id']) {
        $tmp['entity_id'] = $entity['id'];
    }

    $attrIds = [];

    foreach (array_intersect(array_keys($tmp), array_keys($entity['attr'])) as $attrId) {
        $attr = $entity['attr'][$attrId];
        $tmp[$attrId] = attr\cast($tmp[$attrId], $attr);
        $ignorable = ($tmp[$attrId] === null || $tmp[$attrId] === '')
            && $attr['required']
            && attr\ignorable($tmp, $attr);
        $unchanged = array_key_exists($attrId, $tmp['_old']) && $tmp[$attrId] === $tmp['_old'][$attrId];

        if ($ignorable || $unchanged) {
            unset($data[$attrId], $tmp[$attrId]);
        } else {
            $attrIds[] = $attrId;
        }
    }

    if (!$attrIds) {
        app\msg(app\i18n('No changes'));
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

    if ($tmp['_error']) {
        $data['_error'] = $tmp['_error'];
        app\msg(app\i18n('Could not save data'));
        return false;
    }

    foreach ($attrIds as $key => $attrId) {
        if (array_key_exists($attrId, $tmp['_old'])
            && $tmp[$attrId] === $tmp['_old'][$attrId]
            && !$entity['attr'][$attrId]['uploadable']
        ) {
            unset($data[$attrId], $tmp[$attrId], $attrIds[$key]);
        }
    }

    if (!$attrIds) {
        app\msg(app\i18n('No changes'));
        return false;
    }

    try {
        ($entity['type'] . '\transaction')(
            function () use (&$tmp): void {
                $tmp = event('presave', $tmp);
                $tmp = ($tmp['_entity']['type'] . '\save')($tmp);
                $tmp = event('postsave', $tmp);
            },
            $entity['db']
        );
        app\msg(app\i18n('Successfully saved data'));
        $data = $tmp;

        if (empty($data['id']) && !empty($tmp['_old']['id'])) {
            $data['id'] = $tmp['_old']['id'];
        }

        return true;
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not save data'));
    }

    return false;
}

/**
 * Delete entity
 *
 * @throws DomainException
 */
function delete(string $entityId, array $crit = []): bool
{
    $entity = app\cfg('entity', $entityId) ?: throw new DomainException(app\i18n('Invalid entity %s', $entityId));

    if ($entity['readonly']) {
        throw new DomainException(app\i18n('Entity %s is readonly', $entity['id']));
    }

    if (!$all = all($entity['id'], crit: $crit)) {
        app\msg(app\i18n('Nothing to delete'));
        return false;
    }

    try {
        ($entity['type'] . '\transaction')(
            function () use ($all): void {
                foreach ($all as $data) {
                    $data = event('predelete', $data);
                    ($data['_entity']['type'] . '\delete')($data);
                    event('postdelete', $data);
                }
            },
            $entity['db']
        );
        app\msg(app\i18n('Successfully deleted data'));

        return true;
    } catch (DomainException $e) {
        app\msg($e->getMessage());
    } catch (Throwable $e) {
        app\log($e);
        app\msg(app\i18n('Could not delete data'));
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
    $entity = app\cfg('entity', $entityId) ?: throw new DomainException(app\i18n('Invalid entity %s', $entityId));

    return init($entity, array_fill_keys(array_keys($entity['attr']), null));
}

/**
 * Load entity
 */
function load(array $entity, array $data): array
{
    foreach (array_intersect_key($data, $entity['attr']) as $attrId => $val) {
        $data[$attrId] = attr\cast($val, $entity['attr'][$attrId]);
    }

    return event('load', init($entity, $data, true));
}

/**
 * Init entity data
 */
function init(array $entity, array $data, bool $isOld = false): array
{
    return array_replace($data, ['_old' => $isOld ? $data : [], '_entity' => $entity, '_error' => []]);
}

/**
 * Uninit entity data
 */
function uninit(array $data): array
{
    unset($data['_old'], $data['_entity'], $data['_error']);

    return $data;
}

/**
 * Dispatches multiple entity events
 */
function event(string $name, array $data): array
{
    $entity = $data['_entity'];
    $pre = app\id('entity', $name);
    $events = [$pre, app\id($pre, 'type', $entity['type']), app\id($pre, 'db', $entity['db'])];

    if ($entity['parent_id']) {
        $events[] = app\id($pre, 'id', $entity['parent_id']);
    }

    $events[] = app\id($pre, 'id', $entity['id']);

    return app\event($events, $data);
}
