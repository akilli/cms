<?php
namespace qnd;

use Exception;
use RuntimeException;

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
        return $callback($eId, $crit, $opts);
    } catch (Exception $e) {
        error($e);
        message(_('Data could not be loaded'));
    }

    return 0;
}

/**
 * Load entity
 *
 * By default it will load a collection, unless $index is explicitly set to (bool) false.
 *
 * @param string $eId
 * @param array $crit
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function load(string $eId, array $crit = [], $index = null, array $order = [], array $limit = []): array
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['model'] . '_load');
    $single = $index === false;
    $data = [];

    try {
        $result = $callback($eId, $crit, $index, $order, $limit);

        if (!$index
            || $index === 'search'
            || !is_array($index) && empty($entity['attr'][$index]) && $index !== 'uniq'
        ) {
            $index = 'id';
        }

        foreach ($result as $item) {
            foreach ($item as $code => $value) {
                if (isset($entity['attr'][$code])) {
                    $item[$code] = loader($entity['attr'][$code], $item);
                }
            }

            $item['name'] = !isset($item['name']) ? $item['id'] : $item['name'];
            $item['_old'] = $item;
            $item['_entity'] = empty($item['_entity']) ? $entity : $item['_entity'];
            $item['_id'] = $item['id'];

            event(['entity.load', 'entity.' . $entity['model'] . '.load', 'entity.load.' . $eId], $item);

            if ($single) {
                return $item;
            }

            if ($index === 'uniq') {
                foreach ($item as $code => $value) {
                    if (!empty($entity['attr'][$code]['uniq'])) {
                        $data[$code][$item['id']] = $value;
                    }
                }
            } elseif (is_array($index)
                && !empty($index[0])
                && !empty($index[1])
                && !empty($item[$index[0]])
                && !empty($item[$index[1]])
            ) {
                $data[$item[$index[0]]][$item[$index[1]]] = $item;
            } else {
                $data[$item[$index]] = $item;
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
    $original = load($eId, ['id' => array_keys($data)]);
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
 * @param mixed $index
 * @param bool $system
 *
 * @return bool
 */
function delete(string $eId, array $crit = [], $index = null, bool $system = false): bool
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['model'] . '_delete');

    if (!$data = load($eId, $crit, $index)) {
        return false;
    }

    if ($index === false) {
        $data = [$data['id'] => $data];
    }

    foreach ($data as $id => $item) {
        if (!$system && !empty($item['system'])) {
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
