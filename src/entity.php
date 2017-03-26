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
        message(_('Data could not be loaded'));
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
    $item = [];
    $opts = array_replace(entity_opts($opts), ['mode' => 'one', 'limit' => 1]);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        if ($item = $call($entity, $crit, $opts)) {
            $item = load($entity, $item);
        }
    } catch (Exception $e) {
        error((string) $e);
        message(_('Data could not be loaded'));
    }

    return $item;
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
            $item = load($entity, $item);

            if ($multi) {
                $data[$item[$opts['index'][0]]][$item[$opts['index'][1]]] = $item;
            } else {
                $data[$item[$opts['index'][0]]] = $item;
            }
        }
    } catch (Exception $e) {
        error((string) $e);
        message(_('Data could not be loaded'));
    }

    return $data;
}

/**
 * Internal entity loader
 *
 * @param array $entity
 * @param array $item
 *
 * @return array
 */
function load(array $entity, array $item): array
{
    foreach ($item as $aId => $value) {
        if (isset($entity['attr'][$aId])) {
            $item[$aId] = loader($entity['attr'][$aId], $item);
        }
    }

    $item['_old'] = $item;
    $item['_entity'] = $entity;
    $item['_id'] = $item['id'];

    $item = event('entity.load', $item);
    $item = event('model.load.' . $entity['model'], $item);
    $item = event('entity.load.' . $entity['id'], $item);

    return $item;
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
    $original = all($eId, ['id' => array_keys($data)]);
    $default = entity($eId);
    $editable = entity($eId, null, true);
    $aIds = array_keys(array_intersect_key($editable, $default['_entity']['attr']));
    $success = [];
    $error = [];

    foreach ($data as $id => & $item) {
        $item['_id'] = $id;
        $base = empty($original[$id]) ? $default : $original[$id];
        $item = array_replace($base, $editable, $item);
        $item['project_id'] = $item['project_id'] ?? project('id');

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_entity'], $item['_old']['_old']);
        }

        foreach ($aIds as $aId) {
            try {
                $item = validator($item['_entity']['attr'][$aId], $item);
            } catch (Exception $e) {
                $item['_error'][$aId] = $e->getMessage();
            }
        }

        if (!empty($item['_error'])) {
            $error[] = $item['name'];
            continue;
        }

        foreach ($aIds as $aId) {
            try {
                $item = saver($item['_entity']['attr'][$aId], $item);
            } catch (Exception $e) {
                $item['_error'][$aId] = $e->getMessage();
                $error[] = $item['name'];
                continue 2;
            }
        }

        $temp = $item;
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

        $item['_success'] = $trans;

        if ($trans) {
            $success[] = $item['name'];
            $item = $temp;
        } else {
            $error[] = $item['name'];
        }
    }

    if ($success) {
        message(_('Successfully saved %s', implode(', ', $success)));
    }

    if ($error) {
        message(_('Could not save %s', implode(', ', $error)));
    }

    return !$error;
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
 * @param int $number
 * @param bool $bare
 *
 * @return array
 *
 * @throws RuntimeException
 */
function entity(string $eId, int $number = null, bool $bare = false): array
{
    if (!$entity = data('entity', $eId)) {
        throw new RuntimeException(_('Invalid entity %s', $eId));
    }

    $item = array_fill_keys(array_keys(entity_attr($eId, 'edit')), null);
    $item += $bare ? [] : ['_old' => null, '_entity' => $entity, '_id' => null];

    if ($number === null) {
        return $item;
    }

    $data = array_fill_keys(range(-1, -1 * max(1, $number)), $item);

    foreach ($data as $key => $value) {
        $data[$key]['_id'] = $key;
    }

    return $data;
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
