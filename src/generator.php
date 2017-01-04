<?php
namespace qnd;

/**
 * Generates a unique URL for given base id
 *
 * @param string $needle
 * @param array $haystack
 * @param int|string $id
 *
 * @return string
 */
function generator_url(string $needle, array $haystack, $id): string
{
    $ext = data('url', 'ext');
    $base = url();

    if ($ext) {
        foreach ($haystack as $key => $value) {
            $haystack[$key] = explode($ext, $value)[0];
        }
    }

    $needle = $base . trim(preg_replace(['#/#', '#[-]+#i'], '-', filter_id($needle)), '-_');

    if (array_search($needle, $haystack) === $id || !in_array($needle, $haystack)) {
        return $needle . $ext;
    }

    $needle .= '-';

    for ($i = 1; in_array($needle . $i, $haystack) && array_search($needle . $i, $haystack) !== $id; $i++);

    return $needle . $i . $ext;
}

/**
 * Generates a unique file name in given path
 *
 * @param string $str
 * @param string $path
 *
 * @return string
 */
function generator_file(string $str, string $path): string
{
    $parts = explode('.', $str);
    $ext = array_pop($parts);
    $str = filter_id(implode('-', $parts));

    if (file_exists($path . '/' . $str . '.' . $ext)) {
        $str .= '-';

        for ($i = 1; file_exists($path . '/' . $str . $i . '.' . $ext); $i++);

        $str .= $i;
    }

    return $str . '.' . $ext;
}
