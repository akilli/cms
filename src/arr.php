<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Load data from file
 *
 * @param string $file
 *
 * @return array
 */
function arr_load(string $file): array
{
    return is_readable($file) && ($data = include $file) && is_array($data) ? $data : [];
}

/**
 * Order data
 *
 * @param array $data
 * @param array $order
 *
 * @return array
 */
function arr_order(array $data, array $order): array
{
    uasort(
        $data,
        function (array $a, array $b) use ($order) {
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
