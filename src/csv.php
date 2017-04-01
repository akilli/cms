<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Serializes an array to CSV
 *
 * @param array $data
 * @param array $opts
 *
 * @return string
 */
function csv_serialize(array $data, array $opts = []): string
{
    $opts = array_replace(CSV, $opts);
    $handle = fopen('php://memory', 'r+');
    $i = 0;

    foreach ($data as $key => $item) {
        if ($opts['single']) {
            $item = [$key, $item];
        } elseif (!is_array($item)) {
            $item = [$item];
        } elseif ($opts['header'] && ++$i === 1) {
            fputcsv($handle, array_keys($item), $opts['del'], $opts['enc']);
        }

        fputcsv($handle, $item, $opts['del'], $opts['enc']);
    }

    rewind($handle);

    return stream_get_contents($handle);
}

/**
 * Unserializes a CSV string to an array
 *
 * @param string $src
 * @param array $opts
 *
 * @return array
 */
function csv_unserialize(string $src, array $opts = []): array
{
    $opts = array_replace(CSV, $opts);

    if (!$rows = str_getcsv($src, "\n")) {
        return [];
    }

    $data = [];
    $keys = [];

    if ($opts['header']) {
        $keys = $rows[0];
        unset($rows[0]);
    } elseif ($opts['keys'] && is_array($opts['keys'])) {
        $keys = $opts['keys'];
    }

    $k = count($keys);
    $skel = array_fill(0, $k, null);

    foreach ($rows as $row => $item) {
        $item = str_getcsv($item, $opts['del'], $opts['enc'], $opts['esc']);

        if ($opts['single']) {
            $data[$item[0]] = $item[1];
        } elseif ($keys) {
            $item = $k >= count($item) ? array_replace($skel, $item) : array_slice($item, 0, $k);
            $data[] = array_combine($keys, $item);
        } else {
            $data[] = $item;
        }
    }

    return $data;
}
