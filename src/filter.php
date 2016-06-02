<?php
namespace qnd;

/**
 * Encode
 *
 * @param string $var
 *
 * @return string
 */
function encode(string $var)
{
    static $charset;

    if ($charset === null) {
        $charset = config('i18n.charset');
    }

    return htmlspecialchars($var, ENT_QUOTES, $charset, false);
}

/**
 * HTML
 *
 * @param string $string
 *
 * @return string
 */
function filter_html(string $string): string
{
    static $allowed;

    if ($allowed === null) {
        $allowed = config('filter.html');
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
function filter_path(string $path): string
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
function filter_id(string $id): string
{
    static $data, $keys;

    if ($data === null) {
        $data = config('filter.identifier');
        $keys = array_keys($data);
    }

    return trim(preg_replace($keys, $data, strtolower($id)), '-');
}
