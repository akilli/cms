<?php
declare(strict_types = 1);

namespace cms;

/**
 * Special chars filter
 *
 * @param string $var
 *
 * @return string
 */
function encode(string $var): string
{
    return htmlspecialchars($var, ENT_QUOTES, data('app', 'charset'), false);
}

/**
 * HTML filter
 *
 * @param string $html
 *
 * @return string
 */
function filter_html(string $html): string
{
    return $html ? trim(strip_tags($html, data('filter', 'html'))) : '';
}

/**
 * ID filter
 *
 * @param string $id
 * @param string $sep
 *
 * @return string
 */
function filter_id(string $id, string $sep = '-'): string
{
    return trim(preg_replace('#[^a-z0-9]+#', $sep, strtolower(strtr($id, data('filter', 'id')))), $sep);
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
    $str = filter_id(implode('-', $parts));

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
    if (!$format = date_create_from_format($in, $date)) {
        return '';
    }

    return date_format($format, $out) ?: '';
}

/**
 * Parameter filter
 *
 * @param string $param
 *
 * @return string
 */
function filter_param(string $param): string
{
    return preg_replace('#[^\w ]#u', '', $param);
}
