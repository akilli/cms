<?php
namespace akilli;

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

        // Load data from file
        $data = data_load(path('data', $section . '.php'));

        // Dispatch load event
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
