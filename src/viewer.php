<?php
declare(strict_types = 1);

namespace viewer;

use app;
use html;

/**
 * Bool viewer
 */
function bool($val): string
{
    return $val ? app\i18n('Yes') : app\i18n('No');
}

/**
 * Option viewer
 */
function opt($val, array $opt): string
{
    $result = [];

    foreach ((array) $val as $v) {
        if (isset($opt[$v])) {
            $result[] = $opt[$v]['name'];
        }
    }

    return app\enc(implode(', ', $result));
}

/**
 * Date viewer
 */
function date(string $val): string
{
    return date_format(date_create($val), app\cfg('app', 'date'));
}

/**
 * Datetime viewer
 */
function datetime(string $val): string
{
    return date_format(date_create($val), app\cfg('app', 'datetime'));
}

/**
 * Time viewer
 */
function time(string $val): string
{
    return date_format(date_create($val), app\cfg('app', 'time'));
}

/**
 * Rich text viewer
 */
function rte(string $val): string
{
    return $val;
}

/**
 * File viewer
 */
function file(string $val): string
{
    $ext = pathinfo($val, PATHINFO_EXTENSION);
    $val = app\asset($val);

    if (in_array($ext, APP['file.image'])) {
        return html\tag('img', ['src' => $val], null, true);
    }

    if (in_array($ext, APP['file.video'])) {
        return html\tag('video', ['src' => $val, 'controls' => true]);
    }

    if (in_array($ext, APP['file.audio'])) {
        return html\tag('audio', ['src' => $val, 'controls' => true]);
    }

    return html\tag('a', ['href' => $val], $val);
}

/**
 * Filesize viewer
 */
function filesize(int $val): string
{
    $key = 0;
    $units = ['B', 'kB', 'MB', 'GB'];

    foreach (array_keys($units) as $key) {
        if ($val < 1000 ** ($key + 1)) {
            break;
        }
    }

    return round($val / 1000 ** $key, 1) . ' ' . $units[$key];
}

/**
 * Position viewer
 */
function pos(string $val): string
{
    $parts = explode('.', $val);

    foreach ($parts as $k => $v) {
        $parts[$k] = ltrim($v, '0');
    }

    return implode('.', $parts);
}
