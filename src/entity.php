<?php
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
    $callback = fqn($entity['model'] . '_size');

    if (!empty($entity['attr']['project_id']) && !isset($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        return $callback($entity, $crit, $opts);
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
    $opts = array_replace($opts, ['one' => true, 'limit' => 1]);

    if (!empty($entity['attr']['project_id']) && !isset($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        if ($item = $callback($entity, $crit, $opts)) {
            $item = _load($entity, $item);
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
    unset($opts['one']);

    if (empty($opts['index']) || is_array($opts['index']) && (empty($opts['index'][0]) || empty($opts['index'][1]))) {
        $opts['index'] = 'id';
    }

    if (!empty($entity['attr']['project_id']) && !isset($crit['project_id'])) {
        $crit['project_id'] = project('id');
    }

    try {
        $result = $callback($entity, $crit, $opts);

        foreach ($result as $item) {
            $item = _load($entity, $item);

            if (is_array($opts['index'])) {
                $data[$item[$opts['index'][0]]][$item[$opts['index'][1]]] = $item;
            } else {
                $data[$item[$opts['index']]] = $item;
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
    $skeleton = skeleton($eId);
    $editable = skeleton($eId, null, true);
    $success = [];
    $error = [];

    foreach ($data as $id => $item) {
        $item['_id'] = $id;
        $base = empty($original[$id]) ? $skeleton : $original[$id];
        $item = array_replace($base, $editable, $item);
        $data[$id] = $item;
        $callback = fqn($item['_entity']['model'] . '_' . (empty($original[$id]) ? 'create' : 'save'));
        $item['modifier'] = user('id');

        if (empty($original[$id])) {
            $item['creator'] = $item['modifier'];
            $item['project_id'] = project('id');
        }

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_entity'], $item['_old']['_old']);
        }

        if (!validate($item)) {
            if (!empty($item['_error'])) {
                $data[$id]['_error'] = $item['_error'];
            }

            $error[] = $item['name'];
            continue;
        }

        foreach (array_keys($item) as $uid) {
            if (!isset($item['_entity']['attr'][$uid])) {
                continue;
            }

            if (!saver($item['_entity']['attr'][$uid], $item)) {
                if (!empty($item['_error'])) {
                    $data[$id]['_error'] = $item['_error'];
                }

                $error[] = $item['name'];
                continue 2;
            }
        }

        $trans = trans(
            function () use ($eId, & $item, $callback) {
                event(['entity.preSave', 'model.preSave.' . $item['_entity']['model'], 'entity.preSave.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Could not save %s', $item['_id']));
                }

                event(['entity.postSave', 'model.postSave.' . $item['_entity']['model'], 'entity.postSave.' . $eId], $item);
            }
        );

        if (!$trans) {
            $error[] = $item['name'];
        } else {
            $success[] = $item['name'];
            unset($data[$id]);
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

    if (!$data = all($eId, $crit, $opts)) {
        return false;
    }

    foreach ($data as $id => $item) {
        if (empty($opts['system']) && !empty($item['system'])) {
            message(_('You must not delete system items! Therefore skipped Id %s', $id));
            unset($data[$id]);
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

        $trans = trans(
            function () use ($eId, & $item, $callback, $entity) {
                event(['entity.preDelete', 'model.preDelete.' . $entity['model'], 'entity.preDelete.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Could not delete %s', $item['_id']));
                }

                event(['entity.postDelete', 'model.postDelete.' . $entity['model'], 'entity.postDelete.' . $eId], $item);
            }
        );

        if (!$trans) {
            $error[] = $item['name'];
        } else {
            $success[] = $item['name'];
        }

        $data[$id] = $item;
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
function _load(array $entity, array $item): array
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
