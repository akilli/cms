<?php
declare(strict_types = 1);

namespace arr;

/**
 * Filters a recordset-like multi-dimensional array by given column
 */
function filter(array $data, string $col, $val): array
{
    return array_intersect_key($data, array_flip(array_keys(array_combine(array_keys($data), array_column($data, $col)), $val, true)));
}

/**
 * Order data
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
 * Extracts given keys
 */
function extract(array $data, array $keys): array
{
    return $data && $keys ? replace(array_fill_keys(array_intersect($keys, array_keys($data)), null), $data) : [];
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
