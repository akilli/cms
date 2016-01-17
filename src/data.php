<?php
namespace data;

/**
 * Load file data
 *
 * @param string $file
 *
 * @return array
 */
function load($file)
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
function filter(array $data, array $criteria = null, $search = false)
{
    if (!$criteria) {
        return $data;
    }

    foreach ($data as $id => $item) {
        foreach ($criteria as $key => $value) {
            $value = (array) $value;

            if (!array_key_exists($key, $item)
                || !$search && !in_array($item[$key], $value)
                || $search && !filter_match($item[$key], $value)
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
function filter_match($str, array $patterns)
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
function order(array $data, $order = null)
{
    if (!$order) {
        return $data;
    } elseif (!is_array($order)) {
        $order = [$order => 'asc'];
    }

    uasort(
        $data,
        function ($item1, $item2) use ($order) {
            return order_compare($order, $item1, $item2);
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
function order_compare($order, $item1, $item2)
{
    foreach ($order as $key => $direction) {
        $factor = $direction === 'desc' ? -1 : 1;

        if (!array_key_exists($key, $item1)) {
            $item1[$key] = null;
        }

        if (!array_key_exists($key, $item2)) {
            $item2[$key] = null;
        }

        if ($item1[$key] > $item2[$key]) {
            return $factor;
        } elseif ($item1[$key] < $item2[$key]) {
            return -$factor;
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
function limit(array $data, $limit = null)
{
    $isArray = is_array($limit);
    $offset = $isArray && !empty($limit[1]) ? (int) $limit[1] : 0;
    $limit = $isArray && !empty($limit[0]) ? (int) $limit[0] : (int) $limit;

    return $limit > 0 ? array_slice($data, $offset, $limit, true) : $data;
}
