<?php
namespace qnd;

/**
 * Encode
 *
 * @param string $var
 *
 * @return string
 */
function encode(string $var): string
{
    static $charset;

    if ($charset === null) {
        $charset = data('i18n', 'charset');
    }

    return htmlspecialchars($var, ENT_QUOTES | ENT_HTML5, $charset, false);
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
        $allowed = data('filter', 'html');
    }

    return trim(strip_tags($string, $allowed));
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
        $data = data('filter', 'id');
        $keys = array_keys($data);
    }

    return trim(preg_replace($keys, $data, strtolower($id)), '-');
}
