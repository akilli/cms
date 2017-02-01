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
        $data = data_load(path('data', $section . '.php'));

        if ($section !== 'listener') {
            $data = event('data.preLoad.' . $section, $data);
            $data = event('data.postLoad.' . $section, $data);
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
 * @param array $opts
 *
 * @return array
 */
function data_filter(array $data, array $crit, array $opts = []): array
{
    if (!$crit) {
        return $data;
    }

    $search = !empty($opts['search']) && is_array($opts['search']) ? $opts['search'] : [];

    foreach ($data as $id => $item) {
        foreach ($crit as $key => $value) {
            $value = (array) $value;

            if (!array_key_exists($key, $item)
                || !in_array($key, $search) && !in_array($item[$key], $value)
                || in_array($key, $search) && !data_filter_match($item[$key], $value)
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
    foreach ($order as $key => $dir) {
        $factor = $dir === 'desc' ? -1 : 1;
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
 * @param array $entity
 *
 * @return array
 *
 * @throws RuntimeException
 */
function data_entity(array $entity): array
{
    if (empty($entity['uid']) || empty($entity['name']) || empty($entity['attr'])) {
        throw new RuntimeException(_('Invalid entity configuration'));
    }

    $entity = array_replace(data('default', 'entity'), $entity);
    $entity['tab'] = $entity['tab'] ?: $entity['uid'];
    $sort = 0;
    $default = data('default', 'attr');

    foreach ($entity['attr'] as $id => $attr) {
        if (empty($attr['name']) || empty($attr['type']) || !($type = data('attr', $attr['type']))) {
            throw new RuntimeException(_('Invalid attribute configuration'));
        }

        $backend = data('backend', $attr['backend'] ?? $type['backend']);
        $frontend = data('frontend', $attr['frontend'] ?? $type['frontend']);
        $attr = array_replace($default, $backend, $frontend, $type, $attr);
        $attr['uid'] = $id;
        $attr['entity'] = $entity['uid'];

        if ($attr['col'] === false) {
            $attr['col'] = null;
        } elseif (!$attr['col']) {
            $attr['col'] = $attr['uid'];
        }

        if (!is_numeric($attr['sort'])) {
            $attr['sort'] = $sort;
            $sort += 100;
        }

        $entity['attr'][$id] = $attr;
    }

    return $entity;
}
