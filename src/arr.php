<?php
declare(strict_types = 1);

namespace arr;

/**
 * Filters a recordset-like multi-dimensional array by given column
 */
function filter(array $data, string $col, $val): array
{
    foreach ($data as $id => $item) {
        if (($item[$col] ?? null) !== $val) {
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

                if ($result = ($a[$key] ?? null) <=> ($b[$key] ?? null)) {
                    return $result * $factor;
                }
            }

            return 0;
        }
    );

    return $data;
}

/**
 * Applies callback to all elements of given array with passing additional params
 */
function map(callable $call, array $data, ...$params): array
{
    foreach ($data as $key => $val) {
        $data[$key] = $call($val, ...$params);
    }

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

/**
 * Returns the distinct values from a single column in the input array or all items grouped by given key
 */
function group(array $data, string $group, string $col = null): array
{
    $result = [];

    foreach ($data as $key => $val) {
        if (!($k = $val[$group] ?? null)) {
            continue;
        } elseif (!$col) {
            $result[$k][$key] = $val;
        } elseif (array_key_exists($col, $val) && !array_keys($result[$k] ?? [], $val[$col], true)) {
            $result[$k][] = $val[$col];
        }
    }

    return $result;
}
