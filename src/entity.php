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
function entity_validate(array & $item): bool
{
    if (empty($item['_entity'])) {
        return false;
    }

    foreach ($item['_entity']['attributes'] as $attr) {
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
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function entity_size(string $eId, array $criteria = [], array $options = []): int
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['type'] . '_size');

    try {
        return $callback($eId, $criteria, $options);
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
 * @param array $criteria
 * @param mixed $index
 * @param string[] $order
 * @param int[] $limit
 *
 * @return array
 */
function entity_load(string $eId, array $criteria = [], $index = null, array $order = [], array $limit = []): array
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['type'] . '_load');
    $single = $index === false;
    $data = [];

    try {
        $result = $callback($eId, $criteria, $index, $order, $limit);

        if (!$index
            || $index === 'search'
            || !is_array($index) && empty($entity['attributes'][$index]) && $index !== 'unambiguous'
        ) {
            $index = 'id';
        }

        foreach ($result as $item) {
            foreach ($item as $code => $value) {
                if (isset($entity['attributes'][$code])) {
                    $item[$code] = loader($entity['attributes'][$code], $item);
                }
            }

            $item['name'] = !isset($item['name']) ? $item['id'] : $item['name'];
            $item['_old'] = $item;
            $item['_entity'] = empty($item['_entity']) ? $entity : $item['_entity'];
            $item['_id'] = $item['id'];

            event(['entity.load', 'entity.' . $entity['type'] . '.load', 'entity.load.' . $eId], $item);

            if ($single) {
                return $item;
            }

            if ($index === 'unambiguous') {
                foreach ($item as $code => $value) {
                    if (!empty($entity['attributes'][$code]['unambiguous'])) {
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
function entity_save(string $eId, array & $data): bool
{
    $original = entity_load($eId, ['id' => array_keys($data)]);
    $skeleton = skeleton($eId);
    $editable = skeleton($eId, null, true);

    foreach ($data as $id => $item) {
        $item['_id'] = $id;
        $base = empty($original[$id]) ? $skeleton : $original[$id];
        $item = array_replace($base, $editable, $item);
        $data[$id] = $item;
        $callback = fqn($item['_entity']['type'] . '_' . (empty($original[$id]) ? 'create' : 'save'));
        $item['modifier'] = user('id');

        if (empty($original[$id])) {
            $item['creator'] = $item['modifier'];
        }

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_entity'], $item['_old']['_old']);
        }

        if (!entity_validate($item)) {
            if (!empty($item['_error'])) {
                $data[$id]['_error'] = $item['_error'];
            }

            $error = true;
            continue;
        }

        foreach (array_keys($item) as $code) {
            if (!isset($item['_entity']['attributes'][$code])) {
                continue;
            }

            if (!saver($item['_entity']['attributes'][$code], $item)) {
                if (!empty($item['_error'])) {
                    $data[$id]['_error'] = $item['_error'];
                }

                $error = true;
                continue 2;
            }
        }

        $success = trans(
            function () use ($eId, & $item, $callback) {
                event(['entity.preSave', 'entity.' . $item['_entity']['type'] . '.preSave', 'entity.preSave.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Data could not be saved'));
                }

                event(['entity.postSave', 'entity.' . $item['_entity']['type'] . '.postSave', 'entity.postSave.' . $eId], $item);
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
 * @param array $criteria
 * @param mixed $index
 * @param bool $system
 *
 * @return bool
 */
function entity_delete(string $eId, array $criteria = [], $index = null, bool $system = false): bool
{
    $entity = data('entity', $eId);
    $callback = fqn($entity['type'] . '_delete');

    if (!$data = entity_load($eId, $criteria, $index)) {
        return false;
    }

    if ($index === false) {
        $data = [$data['id'] => $data];
    }

    foreach ($data as $id => $item) {
        if (!$system && !empty($item['system'])) {
            message(_('You must not delete system items! Therefore skipped ID %s', $id));
            unset($data[$id]);
            continue;
        }

        foreach (array_keys($item) as $code) {
            if (isset($entity['attributes'][$code]) && !deleter($entity['attributes'][$code], $item)) {
                if (!empty($item['_error'][$code])) {
                    message($item['_error'][$code]);
                }

                $error = true;
                continue 2;
            }
        }

        $success = trans(
            function () use ($eId, & $item, $callback, $entity) {
                event(['entity.preDelete', 'entity.' . $entity['type'] . '.preDelete', 'entity.preDelete.' . $eId], $item);

                if (!$callback($item)) {
                    throw new RuntimeException(_('Data could not be deleted'));
                }

                event(['entity.postDelete', 'entity.' . $entity['type'] . '.postDelete', 'entity.postDelete.' . $eId], $item);
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
