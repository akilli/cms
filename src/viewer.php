<?php
declare(strict_types = 1);

namespace viewer;

use app;
use html;

/**
 * Option viewer
 */
function opt($val, array $opt): string
{
    $result = [];

    foreach ((array) $val as $v) {
        if (isset($opt[$v])) {
            $result[] = $opt[$v];
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
    return html\tag('a', ['href' => app\asset($val)], $val);
}

/**
 * Image viewer
 */
function image(string $val): string
{
    return html\tag('img', ['src' => app\asset($val), 'alt' => $val], null, true);
}

/**
 * Audio viewer
 */
function audio(string $val): string
{
    return html\tag('audio', ['src' => app\asset($val), 'controls' => true]);
}

/**
 * Video viewer
 */
function video(string $val): string
{
    return html\tag('video', ['src' => app\asset($val), 'controls' => true]);
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
