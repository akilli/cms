<?php
declare(strict_types = 1);

namespace qnd;

use Exception;
use RuntimeException;

/**
 * Size entity
 *
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function size(string $eId, array $crit = [], array $opts = []): int
{
    $entity = data('entity', $eId);
    $call = fqn($entity['model'] . '_load');
    unset($opts['order'], $opts['limit'], $opts['offset']);
    $opts = array_replace(entity_opts($opts), ['mode' => 'size']);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        return $call($entity, $crit, $opts)[0];
    } catch (Exception $e) {
        error((string) $e);
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
    $call = fqn($entity['model'] . '_load');
    $data = [];
    $opts = array_replace(entity_opts($opts), ['mode' => 'one', 'limit' => 1]);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        if ($data = $call($entity, $crit, $opts)) {
            $data = entity_load($entity, $data);
        }
    } catch (Exception $e) {
        error((string) $e);
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
    $call = fqn($entity['model'] . '_load');
    $data = [];
    $opts = array_replace(entity_opts($opts), ['mode' => 'all']);
    $multi = !empty($opts['index'][1]);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        $result = $call($entity, $crit, $opts);

        foreach ($result as $item) {
            $item = entity_load($entity, $item);

            if ($multi) {
                $data[$item[$opts['index'][0]]][$item[$opts['index'][1]]] = $item;
            } else {
                $data[$item[$opts['index'][0]]] = $item;
            }
        }
    } catch (Exception $e) {
        error((string) $e);
        message(_('Could not load data'));
    }

    return $data;
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
    if (empty($data['id']) || !($base = one($eId, ['id' => $data['id']]))) {
        $base = entity($eId);
    } elseif (empty($data['_old'])) {
        $data['_old'] = $base;
        unset($data['_old']['_entity'], $data['_old']['_old']);
    }

    $editable = entity($eId, true);
    $data = array_replace($base, $editable, $data);
    $attrs = $data['_entity']['attr'];
    $aIds = array_keys(array_intersect_key($editable, $attrs));
    $data['project_id'] = $data['project_id'] ?? project('id');

    foreach ($aIds as $aId) {
        try {
            $data = validator($attrs[$aId], $data);
        } catch (Exception $e) {
            $data['_error'][$aId] = $e->getMessage();
        }
    }

    if (!empty($data['_error'])) {
        message(_('Could not save %s', $data['name']));
        return false;
    }

    foreach ($aIds as $aId) {
        try {
            $data = saver($attrs[$aId], $data);
        } catch (Exception $e) {
            $data['_error'][$aId] = $e->getMessage();
            message(_('Could not save %s', $data['name']));
            return false;
        }
    }

    $temp = $data;
    $trans = db_trans(
        function () use (& $temp) {
            $temp = event('entity.presave', $temp);
            $temp = event('model.presave.' . $temp['_entity']['model'], $temp);
            $temp = event('entity.presave.' . $temp['_entity']['id'], $temp);
            $call = fqn($temp['_entity']['model'] . '_save');
            $temp = $call($temp);
            event('entity.postsave', $temp);
            event('model.postsave.' . $temp['_entity']['model'], $temp);
            event('entity.postsave.' . $temp['_entity']['id'], $temp);
        }
    );

    if ($trans) {
        $data = $temp;
        message(_('Successfully saved %s', $data['name']));
    } else {
        message(_('Could not save %s', $data['name']));
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

    foreach (all($eId, $crit, $opts) as $id => $item) {
        if (!empty($item['system'])) {
            message(_('System items must not be deleted! Therefore skipped Id %s', (string) $id));
            continue;
        }

        $trans = db_trans(
            function () use ($item) {
                $item = event('entity.predelete', $item);
                $item = event('model.predelete.' . $item['_entity']['model'], $item);
                $item = event('entity.predelete.' . $item['_entity']['id'], $item);
                $call = fqn($item['_entity']['model'] . '_delete');
                $call($item);
                event('entity.postdelete', $item);
                event('model.postdelete.' . $item['_entity']['model'], $item);
                event('entity.postdelete.' . $item['_entity']['id'], $item);
            }
        );

        if ($trans) {
            $success[] = $item['name'];
        } else {
            $error[] = $item['name'];
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

    $item = array_fill_keys(array_keys(entity_attr($eId, 'edit')), null);
    $item += $bare ? [] : ['_old' => null, '_entity' => $entity];

    return $item;
}

/**
 * Retrieve entity attributes filtered by given action
 *
 * @param string $eId
 * @param string $action
 *
 * @return array
 *
 * @throws RuntimeException
 */
function entity_attr(string $eId, string $action): array
{
    if (!$entity = data('entity', $eId)) {
        throw new RuntimeException(_('Invalid entity %s', $eId));
    }

    return array_filter(
        $entity['attr'],
        function ($attr) use ($action) {
            return in_array($action, $attr['actions']);
        }
    );
}

/**
 * Filter load options
 *
 * @param array $opts
 *
 * @return array
 */
function entity_opts(array $opts): array
{
    $default = data('default', 'entity.opts');

    foreach ($default as $key => $val) {
        if (array_key_exists($key, $opts) && gettype($val) !== gettype($opts[$key])) {
            unset($opts[$key]);
        }
    }

    if (array_key_exists('mode', $opts) && !in_array($opts['mode'], ['all', 'one', 'size'])) {
        unset($opts['mode']);
    }

    if (empty($opts['index'][0])) {
        unset($opts['index']);
    }

    return array_replace($default, array_intersect_key($opts, $default));
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
