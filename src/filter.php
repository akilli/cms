<?php
namespace filter;

use akilli;

/**
 * Encode
 *
 * @param string|array $var
 *
 * @return string|array
 */
function encode($var)
{
    static $charset;

    if ($charset === null) {
        $charset = akilli\config('i18n.charset');
    }

    if (is_array($var)) {
        return array_map(__FUNCTION__, $var);
    }

    return is_string($var) ? htmlspecialchars($var, ENT_QUOTES, $charset, false) : $var;
}

/**
 * Decode
 *
 * @param string|array $var
 *
 * @return string|array
 */
function decode($var)
{
    if (is_array($var)) {
        return array_map(__FUNCTION__, $var);
    }

    return is_string($var) ? htmlspecialchars_decode($var, ENT_QUOTES) : $var;
}

/**
 * HTML
 *
 * @param string $string
 *
 * @return string
 */
function html(string $string): string
{
    static $allowed;

    if ($allowed === null) {
        $allowed = akilli\config('filter.html');
    }

    return strip_tags(trim($string), $allowed);
}

/**
 * Converts backslashes to forward slashes in Windows-style paths
 *
 * @param string $path
 *
 * @return string
 */
function path(string $path): string
{
    return strpos($path, '\\') !== false ? str_replace('\\', '/', $path) : $path;
}

/**
 * Identifier
 *
 * @param string $id
 *
 * @return string
 */
function identifier(string $id): string
{
    static $data, $keys, $charset;

    if ($data === null) {
        $data = akilli\config('filter.identifier');
        $keys = array_keys($data);
        $charset = akilli\config('i18n.charset');
    }

    return trim(preg_replace($keys, $data, mb_strtolower($id, $charset)), '-');
}
