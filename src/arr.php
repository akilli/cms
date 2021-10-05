<?php
declare(strict_types=1);

namespace arr;

/**
 * Filters a recordset-like multi-dimensional array by given column
 */
function filter(array $data, string $col, mixed $val, bool $not = false): array
{
    foreach ($data as $id => $item) {
        $equal = array_key_exists($col, $item) && $item[$col] === $val;

        if ($equal && $not || !$equal && !$not) {
            unset($data[$id]);
        }
    }

    return $data;
}

/**
 * Orders a recordset-like multi-dimensional array by given column
 */
function order(array $data, array $order): array
{
    uasort(
        $data,
        function (array $a, array $b) use ($order): int {
            foreach ($order as $key => $dir) {
                $factor = $dir === 'desc' ? -1 : 1;
                $result = ($a[$key] ?? null) <=> ($b[$key] ?? null);

                if ($result) {
                    return $result * $factor;
                }
            }

            return 0;
        }
    );

    return $data;
}

/**
 * Replaces elements from passed arrays into the base array without adding new keys
 */
function replace(array $base, array $data, array ...$add): array
{
    return array_intersect_key(array_replace($base, $data, ...$add), $base);
}

/**
 * Replaces elements from passed arrays into the first array with one level of recursion
 */
function extend(array $data, array $ext): array
{
    foreach ($data as $key => $val) {
        if (array_key_exists($key, $ext)) {
            $data[$key] = is_array($val) && is_array($ext[$key]) ? array_replace($val, $ext[$key]) : $ext[$key];
        }
    }

    return $data + $ext;
}

/**
 * Extracts given keys
 */
function extract(array $data, array $keys): array
{
    $result = [];

    foreach ($keys as $key) {
        if (array_key_exists($key, $data)) {
            $result[$key] = $data[$key];
        }
    }

    return $result;
}

/**
 * Removes given keys
 */
function remove(array $data, array $keys): array
{
    return array_diff_key($data, array_flip($keys));
}

/**
 * Prefixes values
 */
function prefix(array $data, string $prefix): array
{
    return array_map(fn(string $val): string => $prefix . $val, $data);
}

/**
 * Replaces given search value by another
 */
function change(array $data, $search, $replace): array
{
    foreach (array_keys($data, $search, true) as $key) {
        $data[$key] = $replace;
    }

    return $data;
}

/**
 * Indicates wheter given array has all provided keys
 */
function has(array $data, array $keys, bool $filter = false): bool
{
    return !array_diff_key(array_flip($keys), $filter ? array_filter($data) : $data);
}
