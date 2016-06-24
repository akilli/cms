<?php
namespace qnd;

/**
 * CSV default options
 */
const CSV_OPTS = [
    'delimiter' => ';',
    'enclosure' => '"',
    'escape' => '\\',
    'single_item' => false,
    'first_row_as_keys' => false,
    'keys' => [],
];

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
    $opts = array_replace(CSV_OPTS, $opts);
    $handle = fopen('php://memory', 'r+');
    $i = 0;

    foreach ($data as $key => $item) {
        if ($opts['single_item']) {
            $item = [$key, $item];
        } elseif (!is_array($item)) {
            $item = [$item];
        } elseif ($opts['first_row_as_keys'] && ++$i === 1) {
            fputcsv($handle, array_keys($item), $opts['delimiter'], $opts['enclosure']);
        }

        fputcsv($handle, $item, $opts['delimiter'], $opts['enclosure']);
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
    $opts = array_replace(CSV_OPTS, $opts);

    if (!$rows = str_getcsv($src, "\n")) {
        return [];
    }

    $data = [];
    $keys = [];

    if ($opts['first_row_as_keys']) {
        $keys = $rows[0];
        unset($rows[0]);
    } elseif ($opts['keys'] && is_array($opts['keys'])) {
        $keys = $opts['keys'];
    }

    $k = count($keys);
    $skel = array_fill(0, $k, null);

    foreach ($rows as $row => $item) {
        $item = str_getcsv($item, $opts['delimiter'], $opts['enclosure'], $opts['escape']);

        if ($opts['single_item']) {
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
