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

    // Attributes
    $sortOrder = 0;

    foreach ($data['attributes'] as $id => $attr) {
        $attr['id'] = $id;

        // Replace placeholders
        if (strpos($attr['type'], ':') === 0
            && ($code = substr($attr['type'], 1))
            && !empty($data['attributes'][$code]['type'])
        ) {
            $attr['type'] = $data['attributes'][$code]['type'];
        }

        if (!empty($attr['options_entity']) && $attr['options_entity'] === ':entity_id') {
            $attr['options_entity'] = $data['id'];
        }

        $attr = meta_attribute($attr);

        if (!is_numeric($attr['sort_order'])) {
            $attr['sort_order'] = $sortOrder;
            $sortOrder += 100;
        }

        $data['attributes'][$id] = $attr;
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
    $type = data('attribute', $data['type']);

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

    foreach ($meta['attributes'] as $code => $attr) {
        if (meta_action('edit', $attr)) {
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
