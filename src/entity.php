<?php
declare(strict_types = 1);

namespace cms;

use Exception;
use RuntimeException;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $crit
 *
 * @return int
 */
function size(string $eId, array $crit = []): int
{
    $entity = data('entity', $eId);
    $opts = ['mode' => 'size'] + OPTS;

    if (!empty($entity['attr']['project_id']) && !in_array('project_id', array_column($crit, 0))) {
        $crit[] = ['project_id', project('id')];
    }

    try {
        return ('cms\\' . $entity['model'] . '_load')($entity, $crit, $opts)[0];
    } catch (Exception $e) {
        logger((string) $e);
        message(_('Could not load data'));
    }

    return 0;
}

/**
 * Load entity
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function one(string $eId, array $crit = [], array $opts = []): array
{
    $entity = data('entity', $eId);
    $data = [];
    $opts = array_replace(OPTS, array_intersect_key($opts, OPTS), ['mode' => 'one', 'limit' => 1]);

    if (!empty($entity['attr']['project_id']) && !in_array('project_id', array_column($crit, 0))) {
        $crit[] = ['project_id', project('id')];
    }

    try {
        if ($data = ('cms\\' . $entity['model'] . '_load')($entity, $crit, $opts)) {
            $data = entity_load($entity, $data);
        }
    } catch (Exception $e) {
        logger((string) $e);
        message(_('Could not load data'));
    }

    return $data;
}

/**
 * Load entity collection
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function all(string $eId, array $crit = [], array $opts = []): array
{
    $entity = data('entity', $eId);
    $opts = array_replace(OPTS, array_intersect_key($opts, OPTS), ['mode' => 'all']);

    if ($opts['select']) {
        foreach (array_unique(['id', $opts['index']]) as $k) {
            if (!in_array($k, $opts['select'])) {
                $opts['select'][] = $k;
            }
        }
    }

    if (!empty($entity['attr']['project_id']) && !in_array('project_id', array_column($crit, 0))) {
        $crit[] = ['project_id', project('id')];
    }

    try {
        $data = ('cms\\' . $entity['model'] . '_load')($entity, $crit, $opts);

        foreach ($data as $id => $item) {
            $data[$id] = entity_load($entity, $item);
        }

        return array_column($data, null, $opts['index']);
    } catch (Exception $e) {
        logger((string) $e);
        message(_('Could not load data'));
    }

    return [];
}

/**
 * Save entity
 *
 * @param string $eId
 * @param array $data
 *
 * @return bool
 */
function save(string $eId, array & $data): bool
{
    $temp = $data;

    if (empty($temp['id']) || !($base = one($eId, [['id', $temp['id']]]))) {
        $base = entity($eId);
    } elseif (empty($temp['_old'])) {
        $temp['_old'] = $base;
        unset($temp['_old']['_entity'], $temp['_old']['_old']);
    }

    $editable = entity($eId, true);
    $temp = array_replace($base, $editable, $temp);
    $attrs = $temp['_entity']['attr'];
    $aIds = array_keys(array_intersect_key($editable, $attrs));
    $temp['project_id'] = $temp['project_id'] ?? project('id');

    foreach ($aIds as $aId) {
        try {
            $temp = validator($attrs[$aId], $temp);
        } catch (Exception $e) {
            $data['_error'][$aId] = $e->getMessage();
        }
    }

    if (!empty($data['_error'])) {
        message(_('Could not save %s', $temp['name']));
        return false;
    }

    foreach ($aIds as $aId) {
        try {
            $temp = saver($attrs[$aId], $temp);
        } catch (Exception $e) {
            $data['_error'][$aId] = $e->getMessage();
            message(_('Could not save %s', $temp['name']));
            return false;
        }
    }

    $trans = db_trans(
        function () use (& $temp): void {
            $temp = event('entity.presave', $temp);
            $temp = event('model.presave.' . $temp['_entity']['model'], $temp);
            $temp = event('entity.presave.' . $temp['_entity']['id'], $temp);
            $temp = ('cms\\' . $temp['_entity']['model'] . '_save')($temp);
            event('entity.postsave', $temp);
            event('model.postsave.' . $temp['_entity']['model'], $temp);
            event('entity.postsave.' . $temp['_entity']['id'], $temp);
        }
    );

    if ($trans) {
        message(_('Successfully saved %s', $temp['name']));
        $data = $temp;
    } else {
        message(_('Could not save %s', $temp['name']));
    }

    return $trans;
}

/**
 * Delete entity
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return bool
 */
function delete(string $eId, array $crit = [], array $opts = []): bool
{
    $success = [];
    $error = [];

    foreach (all($eId, $crit, $opts) as $id => $data) {
        if (!empty($data['system'])) {
            message(_('System items must not be deleted! Therefore skipped Id %s', (string) $id));
            continue;
        }

        $trans = db_trans(
            function () use ($data): void {
                $data = event('entity.predelete', $data);
                $data = event('model.predelete.' . $data['_entity']['model'], $data);
                $data = event('entity.predelete.' . $data['_entity']['id'], $data);
                ('cms\\' . $data['_entity']['model'] . '_delete')($data);
                event('entity.postdelete', $data);
                event('model.postdelete.' . $data['_entity']['model'], $data);
                event('entity.postdelete.' . $data['_entity']['id'], $data);
            }
        );

        if ($trans) {
            $success[] = $data['name'];
        } else {
            $error[] = $data['name'];
        }
    }

    if ($success) {
        message(_('Successfully deleted %s', implode(', ', $success)));
    }

    if ($error) {
        message(_('Could not delete %s', implode(', ', $error)));
    }

    return !$error;
}

/**
 * Retrieve empty entity
 *
 * @param string $eId
 * @param bool $bare
 *
 * @return array
 *
 * @throws RuntimeException
 */
function entity(string $eId, bool $bare = false): array
{
    if (!$entity = data('entity', $eId)) {
        throw new RuntimeException(_('Invalid entity %s', $eId));
    }

    $item = array_fill_keys(array_keys(entity_attr($entity, 'edit')), null);
    $item += $bare ? [] : ['_old' => null, '_entity' => $entity];

    return $item;
}

/**
 * Retrieve entity attributes filtered by given action
 *
 * @param array $entity
 * @param string $act
 *
 * @return array
 */
function entity_attr(array $entity, string $act): array
{
    foreach ($entity['attr'] as $aId => $attr) {
        if (!in_array($act, $attr['actions'])) {
            unset($entity['attr'][$aId]);
        }
    }

    return $entity['attr'];
}

/**
 * Internal entity loader
 *
 * @param array $entity
 * @param array $data
 *
 * @return array
 */
function entity_load(array $entity, array $data): array
{
    foreach ($data as $aId => $val) {
        if (isset($entity['attr'][$aId])) {
            $data[$aId] = loader($entity['attr'][$aId], $data);
        }
    }

    $data['_old'] = $data;
    $data['_entity'] = $entity;
    $data = event('entity.load', $data);
    $data = event('model.load.' . $entity['model'], $data);
    $data = event('entity.load.' . $entity['id'], $data);

    return $data;
}
