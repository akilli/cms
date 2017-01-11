<?php
namespace qnd;

use Exception;
use RuntimeException;

/**
 * Entity load options
 *
 * @internal
 *
 * @var string
 */
const ENTITY_LOAD = [
    'mode' => 'all',
    'index' => ['id'],
    'search' => [],
    'order' => [],
    'limit' => 0,
    'offset' => 0
];

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
    $callback = fqn($entity['model'] . '_load');
    unset($opts['order'], $opts['limit'], $opts['offset']);
    $opts = array_replace(entity_opts($opts), ['mode' => 'size']);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        return $callback($entity, $crit, $opts)[0];
    } catch (Exception $e) {
        error($e);
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
    $callback = fqn($entity['model'] . '_load');
    $item = [];
    $opts = array_replace(entity_opts($opts), ['mode' => 'one', 'limit' => 1]);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        if ($item = $callback($entity, $crit, $opts)) {
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
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return array
 */
function all(string $eId, array $crit = [], array $opts = []): array
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['model'] . '_load');
    $data = [];
    $opts = array_replace(entity_opts($opts), ['mode' => 'all']);
    $multi = !empty($opts['index'][1]);

    if (!empty($entity['attr']['project_id']) && empty($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        $result = $callback($entity, $crit, $opts);

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
    $success = [];
    $error = [];

    foreach ($data as $id => & $item) {
        $item['_id'] = $id;
        $base = empty($original[$id]) ? $default : $original[$id];
        $item = array_replace($base, $editable, $item);
        $callback = fqn($item['_entity']['model'] . '_save');

        if (empty($original[$id])) {
            $item['project_id'] = project('id');
        }

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_entity'], $item['_old']['_old']);
        }

        if (!validate($item)) {
            $error[] = $item['name'];
            continue;
        }

        foreach (array_keys(array_intersect_key($item, $item['_entity']['attr'])) as $uid) {
            if (!saver($item['_entity']['attr'][$uid], $item)) {
                $error[] = $item['name'];
                continue 2;
            }
        }

        $trans = db_trans(
            function () use ($eId, & $item, $callback) {
                event(['entity.preSave', 'model.preSave.' . $item['_entity']['model'], 'entity.preSave.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Could not save %s', $item['_id']));
                }

                event(['entity.postSave', 'model.postSave.' . $item['_entity']['model'], 'entity.postSave.' . $eId], $item);
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
 * @param string $eId
 * @param array $crit
 * @param array $opts
 *
 * @return bool
 */
function delete(string $eId, array $crit = [], array $opts = []): bool
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['model'] . '_delete');
    $success = [];
    $error = [];

    foreach (all($eId, $crit, $opts) as $id => $item) {
        if (empty($opts['system']) && !empty($item['system'])) {
            message(_('You must not delete system items! Therefore skipped Id %s', $id));
            continue;
        }

        foreach (array_keys($item) as $uid) {
            if (isset($entity['attr'][$uid]) && !deleter($entity['attr'][$uid], $item)) {
                if (!empty($item['_error'][$uid])) {
                    message($item['_error'][$uid]);
                }

                $error[] = $item['name'];
                continue 2;
            }
        }

        $trans = db_trans(
            function () use ($eId, & $item, $callback, $entity) {
                event(['entity.preDelete', 'model.preDelete.' . $entity['model'], 'entity.preDelete.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Could not delete %s', $item['_id']));
                }

                event(['entity.postDelete', 'model.postDelete.' . $entity['model'], 'entity.postDelete.' . $eId], $item);
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
 * Validate entity
 *
 * @param array $item
 *
 * @return bool
 */
function validate(array & $item): bool
{
    if (empty($item['_entity'])) {
        return false;
    }

    foreach ($item['_entity']['attr'] as $attr) {
        if (!validator($attr, $item)) {
            $error = true;
        }
    }

    return empty($error);
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

    event(['entity.load', 'model.load.' . $entity['model'], 'entity.load.' . $entity['id']], $item);

    return $item;
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

    $data = array_fill_keys(range(-1, -1 * max(1, (int) $number)), $item);

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
    foreach (ENTITY_LOAD as $key => $val) {
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

    return array_replace(ENTITY_LOAD, array_intersect_key($opts, ENTITY_LOAD));
}
