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
 * @param array $crit
 * @param bool $search
 *
 * @return array
 */
function data_filter(array $data, array $crit, bool $search = false): array
{
    if (!$crit) {
        return $data;
    }

    foreach ($data as $id => $item) {
        foreach ($crit as $key => $value) {
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
 * @param array $order
 *
 * @return array
 */
function data_order(array $data, array $order): array
{
    if (!$order) {
        return $data;
    }

    uasort(
        $data,
        function (array $a, array $b) use ($order) {
            return data_order_compare($order, $a, $b);
        }
    );

    return $data;
}

/**
 * Sort order compare
 *
 * @param array $order
 * @param array $a
 * @param array $b
 *
 * @return int
 */
function data_order_compare(array $order, array $a, array $b): int
{
    foreach ($order as $key => $direction) {
        $factor = $direction === 'desc' ? -1 : 1;
        $result = ($a[$key] ?? null) <=> ($b[$key] ?? null);

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
 * @param int $limit
 * @param int $offset
 *
 * @return array
 */
function data_limit(array $data, int $limit, int $offset = 0): array
{
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

    $data = array_replace(data('default', 'entity'), $data);
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

    $data = array_replace(data('default', 'attr'), $type, $data);

    // Column
    if ($data['col'] === false) {
        $data['col'] = null;
    } elseif (!$data['col']) {
        $data['col'] = $data['id'];
    }

    // Options callback
    if (!empty($data['opt'][0]) && is_string($data['opt'][0])) {
        $data['opt'][0] = fqn($data['opt'][0]);
    }

    return $data;
}
