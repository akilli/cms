<?php
namespace qnd;

use Exception;
use RuntimeException;

/**
 * Size entity
 *
 * @param string $eUid
 * @param array $crit
 * @param array $opts
 *
 * @return int
 */
function size(string $eUid, array $crit = [], array $opts = []): int
{
    $entity = data('entity', $eUid);
    $call = fqn($entity['model'] . '_load');
    unset($opts['order'], $opts['limit'], $opts['offset']);
    $opts = array_replace(entity_opts($opts), ['mode' => 'size']);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        return $call($entity, $crit, $opts)[0];
    } catch (Exception $e) {
        error($e);
        message(_('Data could not be loaded'));
    }

    return 0;
}

/**
 * Load entity
 *
 * @param string $eUid
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function one(string $eUid, array $crit = [], array $opts = []): array
{
    $entity = data('entity', $eUid);
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
        error($e);
        message(_('Data could not be loaded'));
    }

    return $item;
}

/**
 * Load entity collection
 *
 * @param string $eUid
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function all(string $eUid, array $crit = [], array $opts = []): array
{
    $entity = data('entity', $eUid);
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
        error($e);
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
    foreach ($item as $uid => $value) {
        if (isset($entity['attr'][$uid])) {
            $item[$uid] = loader($entity['attr'][$uid], $item);
        }
    }

    $item['_old'] = $item;
    $item['_entity'] = $entity;
    $item['_id'] = $item['id'];

    $item = event('entity.load', $item);
    $item = event('model.load.' . $entity['model'], $item);
    $item = event('entity.load.' . $entity['uid'], $item);

    return $item;
}

/**
 * Save entity
 *
 * @param string $eUid
 * @param array $data
 *
 * @return bool
 */
function save(string $eUid, array & $data): bool
{
    $original = all($eUid, ['id' => array_keys($data)]);
    $default = entity($eUid);
    $editable = entity($eUid, null, true);
    $success = [];
    $error = [];

    foreach ($data as $id => & $item) {
        $item['_id'] = $id;
        $base = empty($original[$id]) ? $default : $original[$id];
        $item = array_replace($base, $editable, $item);

        if (empty($original[$id])) {
            $item['project_id'] = project('id');
        }

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_entity'], $item['_old']['_old']);
        }

        foreach ($item['_entity']['attr'] as $attr) {
            if (!validator($attr, $item)) {
                $error[] = $item['name'];
                continue 2;
            }
        }

        foreach (array_keys(array_intersect_key($item, $item['_entity']['attr'])) as $uid) {
            try {
                $item = saver($item['_entity']['attr'][$uid], $item);
            } catch (Exception $e) {
                $item['_error'][$uid] = $e->getMessage();
                $error[] = $item['name'];
                continue 2;
            }
        }

        $trans = db_trans(
            function () use (& $item) {
                $item = event('entity.preSave', $item);
                $item = event('model.preSave.' . $item['_entity']['model'], $item);
                $item = event('entity.preSave.' . $item['_entity']['uid'], $item);
                $call = fqn($item['_entity']['model'] . '_save');
                $item = $call($item);
                event('entity.postSave', $item);
                event('model.postSave.' . $item['_entity']['model'], $item);
                event('entity.postSave.' . $item['_entity']['uid'], $item);
            }
        );

        $item['_success'] = $trans;

        if ($trans) {
            $success[] = $item['name'];
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
 * @param string $eUid
 * @param array $crit
 * @param array $opts
 *
 * @return bool
 */
function delete(string $eUid, array $crit = [], array $opts = []): bool
{
    $success = [];
    $error = [];

    foreach (all($eUid, $crit, $opts) as $id => $item) {
        if (empty($opts['system']) && !empty($item['system'])) {
            message(_('You must not delete system items! Therefore skipped Id %s', $id));
            continue;
        }

        foreach ($item['_entity']['attr'] as $uid => $attr) {
            try {
                $item = deleter($attr, $item);
            } catch (Exception $e) {
                message($e->getMessage());
                $error[] = $item['name'];
                continue 2;
            }
        }

        $trans = db_trans(
            function () use ($item) {
                $item = event('entity.preDelete', $item);
                $item = event('model.preDelete.' . $item['_entity']['model'], $item);
                $item = event('entity.preDelete.' . $item['_entity']['uid'], $item);
                $call = fqn($item['_entity']['model'] . '_delete');
                $call($item);
                event('entity.postDelete', $item);
                event('model.postDelete.' . $item['_entity']['model'], $item);
                event('entity.postDelete.' . $item['_entity']['uid'], $item);
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
 * @param string $eUid
 * @param int $number
 * @param bool $bare
 *
 * @return array
 *
 * @throws RuntimeException
 */
function entity(string $eUid, int $number = null, bool $bare = false): array
{
    if (!$entity = data('entity', $eUid)) {
        throw new RuntimeException(_('Invalid entity %s', $eUid));
    }

    $item = array_fill_keys(array_keys(entity_attr($eUid, 'edit')), null);
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
 * @param string $eUid
 * @param string $action
 *
 * @return array
 *
 * @throws RuntimeException
 */
function entity_attr(string $eUid, string $action): array
{
    if (!$entity = data('entity', $eUid)) {
        throw new RuntimeException(_('Invalid entity %s', $eUid));
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

    // Currently only for internal use
    unset($opts['select']);

    return array_replace($default, array_intersect_key($opts, $default));
}
