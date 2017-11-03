<?php
declare(strict_types = 1);

namespace filter;

use app;

/**
 * HTML filter
 */
function html(string $val): string
{
    return trim(strip_tags($val, app\cfg('filter', 'html')));
}

/**
 * ID filter
 */
function id(string $val): string
{
    return trim(preg_replace('#[^a-z0-9]+#', '-', strtolower(strtr($val, app\cfg('filter', 'id')))), '-');
}

/**
 * Converts a date, time or datetime from one to another format
 */
function date(string $val, string $in, string $out): string
{
    return ($val = date_create_from_format($in, $val)) && ($val = date_format($val, $out)) ? $val : '';
}

/**
 * Parameter filter
 */
function param(string $val): string
{
    return preg_replace('#[^\w ]#u', '', $val);
}
