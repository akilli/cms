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
    // No metadata provided
    if (empty($item['_metadata'])) {
        return false;
    }

    foreach ($item['_metadata']['attributes'] as $attribute) {
        // Validate attribute
        if (!$attribute['validate']($attribute, $item)) {
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
    $metadata = data('metadata', $entity);
    $callback = __NAMESPACE__ . '\\' . $metadata['model'] . '_size';

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
    $metadata = data('metadata', $entity);
    $callback =  __NAMESPACE__ . '\\' . $metadata['model'] . '_load';
    $single = $index === false;
    $data = [];

    // Result
    try {
        $result = $callback($entity, $criteria, $index, $order, $limit);

        if (!$index
            || $index === 'search'
            || !is_array($index) && empty($metadata['attributes'][$index]) && $index !== 'unique'
        ) {
            $index = 'id';
        }

        foreach ($result as $item) {
            // Attribute load callback
            foreach ($item as $code => $value) {
                if (isset($metadata['attributes'][$code])) {
                    $item[$code] = $metadata['attributes'][$code]['load']($metadata['attributes'][$code], $item);
                }
            }

            $item['name'] = !isset($item['name']) ? $item['id'] : $item['name'];
            $item['_original'] = $item;
            $item['_metadata'] = empty($item['_metadata']) ? $metadata : $item['_metadata'];
            $item['_id'] = $item['id'];

            // Entity load events
            event(
                [
                    'model.load',
                    'model.load.' . $metadata['model'],
                    'entity.load.' . $entity
                ],
                $item
            );

            // Single result
            if ($single) {
                return $item;
            }

            if ($index === 'unique') {
                // Index unique
                foreach ($item as $code => $value) {
                    if (!empty($metadata['attributes'][$code]['is_unique'])) {
                        $data[$code][$item['id']] = $value;
                    }
                }
            } elseif (is_array($index)
                && !empty($index[0])
                && !empty($index[1])
                && !empty($item[$index[0]])
                && !empty($item[$index[1]])
            ) {
                // Array index
                $data[$item[$index[0]]][$item[$index[1]]] = $item;
            } else {
                // Default index
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
    $metadata = data('metadata', $entity);
    $original = model_load($entity, ['id' => array_keys($data)]);

    foreach ($data as $id => $item) {
        $item['_id'] = $id;
        $item = array_replace(empty($original[$id]) ? metadata_skeleton($entity) : $original[$id], $item);
        $data[$id] = $item;
        $callback =  __NAMESPACE__ . '\\' . $metadata['model'] . '_' . (empty($original[$id]) ? 'create' : 'save');
        $item['modified'] = date_format(date_create('now'), 'Y-m-d H:i:s');
        $item['modifier'] = account('id');

        if (empty($original[$id])) {
            $item['created'] = $item['modified'];
            $item['creator'] = $item['modifier'];
        }

        // Validate
        if (!model_validate($item)) {
            if (!empty($item['__error'])) {
                $data[$id]['__error'] = $item['__error'];
            }

            $error = true;
            continue;
        }

        // Attributes
        foreach (array_keys($item) as $code) {
            if (!isset($metadata['attributes'][$code])) {
                continue;
            }

            // Attribute save callback
            if (!$metadata['attributes'][$code]['save']($metadata['attributes'][$code], $item)) {
                if (!empty($item['__error'])) {
                    $data[$id]['__error'] = $item['__error'];
                }

                $error = true;
                continue 2;
            }

            // Ignored attributes
            if (attribute_ignore($metadata['attributes'][$code], $item)) {
                unset($item[$code]);
            }
        }

        // Transaction
        $success = db_transaction(
            $metadata['db'],
            function () use ($entity, & $item, $callback, $metadata) {
                // Entity before save events
                event(
                    [
                        'model.save_before',
                        'model.save_before.' . $metadata['model'],
                        'entity.save_before.' . $entity
                    ],
                    $item
                );

                // Execute
                if (!$callback($item)) {
                    throw new RuntimeException('Save call failed');
                }

                // Entity after save events
                event(
                    [
                        'model.save_after',
                        'model.save_after.' . $metadata['model'],
                        'entity.save_after.' . $entity
                    ],
                    $item
                );
            }
        );

        // Unset item
        if (!$success) {
            $error = true;
        } else {
            unset($data[$id]);
        }
    }

    // Message
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
    $metadata = data('metadata', $entity);
    $callback =  __NAMESPACE__ . '\\' . $metadata['model'] . '_delete';

    // Check if anything is there to delete
    if (!$data = model_load($entity, $criteria, $index)) {
        return false;
    }

    // Check if single result
    if ($index === false) {
        $data = [$data['id'] => $data];
    }

    foreach ($data as $id => $item) {
        // Filter system items
        if (!$system && !empty($item['is_system'])) {
            message(_('You must not delete system items! Therefore skipped ID %s', $id));
            unset($data[$id]);
            continue;
        }

        // Attribute delete callback
        foreach (array_keys($item) as $code) {
            if (isset($metadata['attributes'][$code])
                && !$metadata['attributes'][$code]['delete']($metadata['attributes'][$code], $item)
            ) {
                if (!empty($item['__error'][$code])) {
                    message($item['__error'][$code]);
                }

                $error = true;
                continue 2;
            }
        }

        // Transaction
        $success = db_transaction(
            $metadata['db'],
            function () use ($entity, & $item, $callback, $metadata) {
                // Entity before delete events
                event(
                    [
                        'model.delete_before',
                        'model.delete_before.' . $metadata['model'],
                        'entity.delete_before.' . $entity
                    ],
                    $item
                );

                // Execute
                if (!$callback($item)) {
                    throw new RuntimeException('Delete call failed');
                }

                // Entity after delete events
                event(
                    [
                        'model.delete_after',
                        'model.delete_after.' . $metadata['model'],
                        'entity.delete_after.' . $entity
                    ],
                    $item
                );
            }
        );

        if (!$success) {
            $error = true;
        }

        $data[$id] = $item;
    }

    // Message
    message(_(empty($error) ? 'Data successfully deleted' : 'Data could not be deleted'));

    return empty($error);
}
