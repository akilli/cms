<?php
declare(strict_types = 1);

namespace filter;

use app;

/**
 * Special chars filter
 */
function enc(string $var): string
{
    return htmlspecialchars($var, ENT_QUOTES, ini_get('default_charset'), false);
}

/**
 * HTML filter
 */
function html(string $html): string
{
    return $html ? trim(strip_tags($html, app\cfg('filter', 'html'))) : '';
}

/**
 * ID filter
 */
function id(string $id, string $sep = '-'): string
{
    return trim(preg_replace('#[^a-z0-9]+#', $sep, strtolower(strtr($id, app\cfg('filter', 'id')))), $sep);
}

/**
 * Converts a date, time or datetime from one to another format
 */
function date(string $date, string $in, string $out): string
{
    return ($date = date_create_from_format($in, $date)) && ($date = date_format($date, $out)) ? $date : '';
}

/**
 * Parameter filter
 */
function param(string $param): string
{
    return preg_replace('#[^\w ]#u', '', $param);
}
