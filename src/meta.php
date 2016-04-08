<?php
namespace akilli;

use RuntimeException;

/**
 * Entity
 *
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function meta_entity(array $data): array
{
    // Check minimum requirements
    if (empty($data['id']) || empty($data['name']) || empty($data['table']) || empty($data['attributes'])) {
        throw new RuntimeException(_('Entity metadata does not meet the minimum requirements'));
    }

    // Clean up
    foreach (array_keys($data) as $key) {
        if (strpos($key, '_') === 0) {
            unset($data[$key]);
        }
    }

    // Model
    $skeleton = data('skeleton', 'entity');
    $model = !empty($data['model']) ? $data['model'] : $skeleton['model'];
    $data = array_replace_recursive($skeleton, (array) data('skeleton', 'entity.' . $model), $data);

    // Actions
    if (!is_array($data['actions'])) {
        $data['actions'] = [];
    }

    // Attributes
    $sortOrder = 0;

    foreach ($data['attributes'] as $id => $attribute) {
        $attribute['id'] = $id;
        $attribute['entity_id'] = $data['id'];

        // Replace placeholders
        if (strpos($attribute['type'], ':') === 0
            && ($code = substr($attribute['type'], 1))
            && !empty($data['attributes'][$code]['type'])
        ) {
            $attribute['type'] = $data['attributes'][$code]['type'];
        }

        if (!empty($attribute['foreign_entity_id']) && $attribute['foreign_entity_id'] === ':entity_id') {
            $attribute['foreign_entity_id'] = $data['id'];
        }

        $attribute = meta_attribute($attribute);

        if (!is_numeric($attribute['sort_order'])) {
            $attribute['sort_order'] = $sortOrder;
            $sortOrder += 100;
        }

        $data['attributes'][$id] = $attribute;
    }

    return $data;
}

/**
 * Attribute
 *
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function meta_attribute(array $data): array
{
    // Check minimum requirements
    if (empty($data['id']) || empty($data['name']) || empty($data['type'])) {
        throw new RuntimeException(_('Attribute metadata does not meet the minimum requirements'));
    }

    // Clean up
    foreach (array_keys($data) as $key) {
        if (strpos($key, '_') === 0) {
            unset($data[$key]);
        }
    }

    // Type, Backend, Frontend
    $type = data('type', $data['type']);

    if (!$type || empty($type['backend']) || empty($type['frontend'])) {
        throw new RuntimeException(_('Invalid type %s configured for attribute %s', $data['type'], $data['id']));
    }

    $data['backend'] = $type['backend'];
    $data['frontend'] = $type['frontend'];
    $backend = data('backend', $data['backend']);
    $frontend = data('frontend', $data['frontend']);

    // Model
    $data = array_replace(
        data('skeleton', 'attribute'),
        !empty($backend['default']) ? $backend['default'] : [],
        !empty($frontend['default']) ? $frontend['default'] : [],
        !empty($type['default']) ? $type['default'] : [],
        $data
    );

    // Actions
    if (!is_array($data['actions'])) {
        $data['actions'] = [];
    }

    // Correct invalid values
    $data['is_required'] = empty($data['null']) && $data['is_required'];
    $data['is_unique'] = !in_array($data['backend'], ['bool', 'text']) && $data['is_unique'];
    $data['is_multiple'] = in_array($data['type'], ['multicheckbox', 'multiselect']);

    return $data;
}

/**
 * Check wheter entity or attribute supports at least one of provided actions
 *
 * @param string|array $action
 * @param array $data
 *
 * @return bool
 */
function meta_action($action, array $data): bool
{
    if (!isset($data['actions'])
        || !is_array($data['actions']) && !($data['actions'] = json_decode($data['actions'], true))
        || empty($data['actions'])
    ) {
        // No actions supported
        return false;
    } elseif (in_array('all', $data['actions']) && ($action !== 'edit' || empty($data['auto']))) {
        // All actions supported
        return true;
    }

    foreach ((array) $action as $key) {
        if (in_array($key, $data['actions']) && ($key !== 'edit' || empty($data['auto']))) {
            return true;
        }
    }

    return false;
}

/**
 * Retrieve empty entity
 *
 * @param string $entity
 * @param int $number
 *
 * @return array
 */
function meta_skeleton(string $entity, int $number = null): array
{
    $meta = data('meta', $entity);
    $item = ['_meta' => $meta, '_old' => null, '_id' => null, 'id' => null, 'name' => null];

    foreach ($meta['attributes'] as $code => $attribute) {
        if (meta_action('edit', $attribute)) {
            $item[$code] = null;
        }
    }

    if ($number === null) {
        return $item;
    }

    $data = array_fill_keys(range(-1, -1 * max(1, (int) $number)), $item);

    foreach ($data as $key => $value) {
        $data[$key]['_id'] = $key;
    }

    return $data;
}
