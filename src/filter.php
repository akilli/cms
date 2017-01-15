<?php
namespace qnd;

/**
 * Special chars filter
 *
 * @param string $var
 *
 * @return string
 */
function encode(string $var): string
{
    return htmlspecialchars($var, ENT_QUOTES | ENT_HTML5, data('app', 'charset'), false);
}

/**
 * HTML filter
 *
 * @param string $string
 *
 * @return string
 */
function filter_html(string $string): string
{
    return trim(strip_tags($string, data('filter', 'html')));
}

/**
 * UID filter
 *
 * @param string $id
 *
 * @return string
 */
function filter_uid(string $id): string
{
    $data = data('filter', 'uid');

    return trim(preg_replace(array_keys($data), $data, strtolower($id)), '-_');
}

/**
 * Generates a unique file name in given path
 *
 * @param string $str
 * @param string $path
 *
 * @return string
 */
function filter_file(string $str, string $path): string
{
    $parts = explode('.', $str);
    $ext = array_pop($parts);
    $str = filter_uid(implode('-', $parts));

    if (file_exists($path . '/' . $str . '.' . $ext)) {
        $str .= '-';

        for ($i = 1; file_exists($path . '/' . $str . $i . '.' . $ext); $i++);

        $str .= $i;
    }

    return $str . '.' . $ext;
}

/**
 * Converts a date, time or datetime from one to another format
 *
 * @param string $date
 * @param string $in
 * @param string $out
 *
 * @return string
 */
function filter_date(string $date, string $in, string $out): string
{
    return date_format(date_create_from_format($in, $date), $out) ?: '';
}
