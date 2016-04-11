<?php
namespace akilli;

use Exception;
use RuntimeException;

/**
 * Validate
 *
 * @param array $item
 *
 * @return bool
 */
function model_validate(array & $item): bool
{
    if (empty($item['_meta'])) {
        return false;
    }

    foreach ($item['_meta']['attributes'] as $attr) {
        if (!validator($attr, $item)) {
            $error = true;
        }
    }

    return empty($error);
}

/**
 * Size
 *
 * @param string $entity
 * @param array $criteria
 * @param array $options
 *
 * @return int
 */
function model_size(string $entity, array $criteria = null, array $options = []): int
{
    $meta = data('meta', $entity);
    $callback = __NAMESPACE__ . '\\' . $meta['model'] . '_size';

    try {
        return $callback($entity, $criteria, $options);
    } catch (Exception $e) {
        error($e);
        message(_('Data could not be loaded'));
    }

    return 0;
}

/**
 * Load data
 *
 * Combined entity and collection loader.
 * By default it will load a collection, unless $index is explicitly set to (bool) false
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param array $order
 * @param int[] $limit
 *
 * @return array
 */
function model_load(string $entity, array $criteria = null, $index = null, array $order = null, array $limit = null): array
{
    $meta = data('meta', $entity);
    $callback =  __NAMESPACE__ . '\\' . $meta['model'] . '_load';
    $single = $index === false;
    $data = [];

    try {
        $result = $callback($entity, $criteria, $index, $order, $limit);

        if (!$index
            || $index === 'search'
            || !is_array($index) && empty($meta['attributes'][$index]) && $index !== 'unique'
        ) {
            $index = 'id';
        }

        foreach ($result as $item) {
            foreach ($item as $code => $value) {
                if (isset($meta['attributes'][$code])) {
                    $item[$code] = loader($meta['attributes'][$code], $item);
                }
            }

            $item['name'] = !isset($item['name']) ? $item['id'] : $item['name'];
            $item['_old'] = $item;
            $item['_meta'] = empty($item['_meta']) ? $meta : $item['_meta'];
            $item['_id'] = $item['id'];

            event(['model.load', 'model.load.' . $meta['model'], 'entity.load.' . $entity], $item);

            if ($single) {
                return $item;
            }

            if ($index === 'unique') {
                foreach ($item as $code => $value) {
                    if (!empty($meta['attributes'][$code]['is_unique'])) {
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
 * Save data
 *
 * @param string $entity
 * @param array $data
 *
 * @return bool
 */
function model_save(string $entity, array & $data): bool
{
    $meta = data('meta', $entity);
    $original = model_load($entity);

    foreach ($data as $id => $item) {
        $item['_id'] = $id;
        $item = array_replace(empty($original[$id]) ? meta_skeleton($entity) : $original[$id], $item);
        $data[$id] = $item;
        $callback =  __NAMESPACE__ . '\\' . $meta['model'] . '_' . (empty($original[$id]) ? 'create' : 'save');
        $item['modified'] = date_format(date_create('now'), 'Y-m-d H:i:s');
        $item['modifier'] = account('id');

        if (empty($original[$id])) {
            $item['created'] = $item['modified'];
            $item['creator'] = $item['modifier'];
        }

        if (empty($item['_old']) && !empty($original[$id])) {
            $item['_old'] = $original[$id];
            unset($item['_old']['_id'], $item['_old']['_meta'], $item['_old']['_old']);
        }

        if (!model_validate($item)) {
            if (!empty($item['_error'])) {
                $data[$id]['_error'] = $item['_error'];
            }

            $error = true;
            continue;
        }

        foreach (array_keys($item) as $code) {
            if (!isset($meta['attributes'][$code])) {
                continue;
            }

            if (!saver($meta['attributes'][$code], $item)) {
                if (!empty($item['_error'])) {
                    $data[$id]['_error'] = $item['_error'];
                }

                $error = true;
                continue 2;
            }

            if (ignorable($meta['attributes'][$code], $item)) {
                unset($item[$code]);
            }
        }

        $success = trans(
            function () use ($entity, & $item, $callback, $meta) {
                event(
                    ['model.save_before', 'model.save_before.' . $meta['model'], 'entity.save_before.' . $entity],
                    $item
                );

                if (!$callback($item)) {
                    throw new RuntimeException('Save call failed');
                }

                event(
                    ['model.save_after', 'model.save_after.' . $meta['model'], 'entity.save_after.' . $entity],
                    $item
                );
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
 * Delete data
 *
 * @param string $entity
 * @param array $criteria
 * @param mixed $index
 * @param bool $system
 *
 * @return bool
 */
function model_delete(string $entity, array $criteria = null, $index = null, bool $system = false): bool
{
    $meta = data('meta', $entity);
    $callback =  __NAMESPACE__ . '\\' . $meta['model'] . '_delete';

    if (!$data = model_load($entity, $criteria, $index)) {
        return false;
    }

    if ($index === false) {
        $data = [$data['id'] => $data];
    }

    foreach ($data as $id => $item) {
        if (!$system && !empty($item['is_system'])) {
            message(_('You must not delete system items! Therefore skipped ID %s', $id));
            unset($data[$id]);
            continue;
        }

        foreach (array_keys($item) as $code) {
            if (isset($meta['attributes'][$code]) && !deleter($meta['attributes'][$code], $item)) {
                if (!empty($item['_error'][$code])) {
                    message($item['_error'][$code]);
                }

                $error = true;
                continue 2;
            }
        }

        $success = trans(
            function () use ($entity, & $item, $callback, $meta) {
                event(
                    ['model.delete_before', 'model.delete_before.' . $meta['model'], 'entity.delete_before.' . $entity],
                    $item
                );

                if (!$callback($item)) {
                    throw new RuntimeException('Delete call failed');
                }

                event(
                    ['model.delete_after', 'model.delete_after.' . $meta['model'], 'entity.delete_after.' . $entity],
                    $item
                );
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
