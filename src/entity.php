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

    try {
        $result = $callback($entity, $crit, $opts);

        foreach ($result as $item) {
            $item = _load($entity, $item);

            if ($opts['index'] === 'uniq') {
                foreach ($item as $code => $value) {
                    if (!empty($entity['attr'][$code]['uniq'])) {
                        $data[$code][$item['id']] = $value;
                    }
                }
            } elseif (is_array($opts['index'])) {
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

    foreach ($data as $id => $item) {
        $item['_id'] = $id;
        $base = empty($original[$id]) ? $skeleton : $original[$id];
        $item = array_replace($base, $editable, $item);
        $data[$id] = $item;
        $callback = fqn($item['_entity']['model'] . '_' . (empty($original[$id]) ? 'create' : 'save'));
        $item['modifier'] = user('id');

        if (empty($original[$id])) {
            $item['creator'] = $item['modifier'];
        }

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_entity'], $item['_old']['_old']);
        }

        if (!validate($item)) {
            if (!empty($item['_error'])) {
                $data[$id]['_error'] = $item['_error'];
            }

            $error = true;
            continue;
        }

        foreach (array_keys($item) as $code) {
            if (!isset($item['_entity']['attr'][$code])) {
                continue;
            }

            if (!saver($item['_entity']['attr'][$code], $item)) {
                if (!empty($item['_error'])) {
                    $data[$id]['_error'] = $item['_error'];
                }

                $error = true;
                continue 2;
            }
        }

        $success = trans(
            function () use ($eId, & $item, $callback) {
                event(['entity.preSave', 'entity.' . $item['_entity']['model'] . '.preSave', 'entity.preSave.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Data could not be saved'));
                }

                event(['entity.postSave', 'entity.' . $item['_entity']['model'] . '.postSave', 'entity.postSave.' . $eId], $item);
            }
        );

        if (!$success) {
            $error = true;
        } else {
            unset($data[$id]);
        }
    }

    message(_(empty($error) ? 'Data successfully saved' : 'Data could not be saved'));

    return empty($error);
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

    if (!$data = all($eId, $crit, $opts)) {
        return false;
    }

    foreach ($data as $id => $item) {
        if (empty($opts['system']) && !empty($item['system'])) {
            message(_('You must not delete system items! Therefore skipped Id %s', $id));
            unset($data[$id]);
            continue;
        }

        foreach (array_keys($item) as $code) {
            if (isset($entity['attr'][$code]) && !deleter($entity['attr'][$code], $item)) {
                if (!empty($item['_error'][$code])) {
                    message($item['_error'][$code]);
                }

                $error = true;
                continue 2;
            }
        }

        $success = trans(
            function () use ($eId, & $item, $callback, $entity) {
                event(['entity.preDelete', 'entity.' . $entity['model'] . '.preDelete', 'entity.preDelete.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Data could not be deleted'));
                }

                event(['entity.postDelete', 'entity.' . $entity['model'] . '.postDelete', 'entity.postDelete.' . $eId], $item);
            }
        );

        if (!$success) {
            $error = true;
        }

        $data[$id] = $item;
    }

    message(_(empty($error) ? 'Data successfully deleted' : 'Data could not be deleted'));

    return empty($error);
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
 * @internal
 *
 * @param array $entity
 * @param array $item
 *
 * @return array
 */
function _load(array $entity, array $item): array
{
    foreach ($item as $code => $value) {
        if (isset($entity['attr'][$code])) {
            $item[$code] = loader($entity['attr'][$code], $item);
        }
    }

    $item['_old'] = $item;
    $item['_entity'] = $entity;
    $item['_id'] = $item['id'];

    event(['entity.load', 'entity.' . $entity['model'] . '.load', 'entity.load.' . $entity['id']], $item);

    return $item;
}
