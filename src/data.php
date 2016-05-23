<?php
namespace qnd;

use RuntimeException;

/**
 * Data
 *
 * @param string $section
 * @param string $id
 *
 * @return mixed
 */
function data(string $section, string $id = null)
{
    $data = & registry('data.' . $section);

    if ($data === null) {
        $data = [];
        $data = data_load(path('data', $section . '.php'));

        if ($section !== 'listener') {
            event('data.load.' . $section, $data);
        }
    }

    if ($id === null) {
        return $data;
    }

    return $data[$id] ?? null;
}

/**
 * Load file data
 *
 * @param string $file
 *
 * @return array
 */
function data_load(string $file): array
{
    if (!is_readable($file)) {
        return [];
    }

    $data = include $file;

    return is_array($data) ? $data : [];
}

/**
 * Filter data by given criteria
 *
 * @param array $data
 * @param array $criteria
 * @param bool $search
 *
 * @return array
 */
function data_filter(array $data, array $criteria = null, bool $search = false): array
{
    if (!$criteria) {
        return $data;
    }

    foreach ($data as $id => $item) {
        foreach ($criteria as $key => $value) {
            $value = (array) $value;

            if (!array_key_exists($key, $item)
                || !$search && !in_array($item[$key], $value)
                || $search && !data_filter_match($item[$key], $value)
            ) {
                unset($data[$id]);
            }
        }
    }

    return $data;
}

/**
 * Checks wheter string matches with one of the given search patterns
 *
 * @param string $str
 * @param array $patterns
 *
 * @return bool
 */
function data_filter_match(string $str, array $patterns): bool
{
    foreach ($patterns as $pattern) {
        if (strpos((string) $str, (string) $pattern) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Sort order
 *
 * @param array $data
 * @param string|array $order
 *
 * @return array
 */
function data_order(array $data, $order = null): array
{
    if (!$order) {
        return $data;
    } elseif (!is_array($order)) {
        $order = [$order => 'asc'];
    }

    uasort(
        $data,
        function (array $item1, array $item2) use ($order) {
            return data_order_compare($order, $item1, $item2);
        }
    );

    return $data;
}

/**
 * Sort order compare
 *
 * @param array $order
 * @param array $item1
 * @param array $item2
 *
 * @return int
 */
function data_order_compare(array $order, array $item1, array $item2): int
{
    foreach ($order as $key => $direction) {
        $factor = $direction === 'desc' ? -1 : 1;
        $result = ($item1[$key] ?? null) <=> ($item2[$key] ?? null);

        if ($result) {
            return $result * $factor;
        }
    }

    return 0;
}

/**
 * Limit
 *
 * @param array $data
 * @param int|array $limit
 *
 * @return array
 */
function data_limit(array $data, $limit = null): array
{
    $isArray = is_array($limit);
    $offset = $isArray && !empty($limit[1]) ? (int) $limit[1] : 0;
    $limit = $isArray && !empty($limit[0]) ? (int) $limit[0] : (int) $limit;

    return $limit > 0 ? array_slice($data, $offset, $limit, true) : $data;
}

/**
 * Entity data
 *
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function data_entity(array $data): array
{
    // Check minimum requirements
    if (empty($data['id']) || empty($data['name']) || empty($data['attr'])) {
        throw new RuntimeException(_('Entity data does not meet the minimum requirements'));
    }

    // Clean up
    foreach (array_keys($data) as $key) {
        if (strpos($key, '_') === 0) {
            unset($data[$key]);
        }
    }

    $data = array_replace_recursive(data('skeleton', 'entity'), $data);
     // Set table name from Id if it is not set already
    $data['tab'] = $data['tab'] ?: $data['id'];
    // Attributes
    $sort = 0;

    foreach ($data['attr'] as $id => $attr) {
        $attr['id'] = $id;
        $attr['entity_id'] = $data['id'];
        $attr = data_attr($attr);

        if (!is_numeric($attr['sort'])) {
            $attr['sort'] = $sort;
            $sort += 100;
        }

        $data['attr'][$id] = $attr;
    }

    return $data;
}

/**
 * Attribute data
 *
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function data_attr(array $data): array
{
    // Check minimum requirements
    if (empty($data['id']) || empty($data['name']) || empty($data['type'])) {
        throw new RuntimeException(_('Attribute data does not meet the minimum requirements'));
    }

    // Clean up
    foreach (array_keys($data) as $key) {
        if (strpos($key, '_') === 0) {
            unset($data[$key]);
        }
    }

    // Type, Backend, Frontend
    $type = data('attr', $data['type']);

    if (!$type || empty($type['backend']) || empty($type['frontend'])) {
        throw new RuntimeException(_('Invalid type %s configured for attribute %s', $data['type'], $data['id']));
    }

    $data = array_replace(data('skeleton', 'attr'), $type, $data);

    // Column
    if (!empty($data['virtual'])) {
        $data['col'] = null;
    } elseif (empty($data['col'])) {
        $data['col'] = $data['id'];
    }

    // Correct invalid values
    $data['required'] = empty($data['nullable']) && $data['required'];
    $data['uniq'] = !in_array($data['backend'], ['bool', 'text']) && $data['uniq'];
    $data['multiple'] = in_array($data['type'], ['multicheckbox', 'multiselect']);

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
function data_action($action, array $data): bool
{
    if (empty($data['actions']) || !is_array($data['actions']) && !($data['actions'] = json_decode($data['actions'], true))) {
        // No actions supported
        return false;
    } elseif (in_array('all', $data['actions'])) {
        // All actions supported
        return true;
    }

    foreach ((array) $action as $key) {
        if (in_array($key, $data['actions'])) {
            return true;
        }
    }

    return false;
}

/**
 * Retrieve empty entity
 *
 * @param string $eId
 * @param int $number
 * @param bool $bare
 *
 * @return array
 */
function skeleton(string $eId, int $number = null, bool $bare = false): array
{
    $entity = data('entity', $eId);
    $item = [];

    foreach ($entity['attr'] as $code => $attr) {
        if (data_action('edit', $attr)) {
            $item[$code] = null;
        }
    }

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
