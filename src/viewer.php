<?php
declare(strict_types = 1);

namespace viewer;

use function filter\enc;
use function html\tag;
use app;

/**
 * Option viewer
 */
function opt(array $attr, array $data): string
{
    $result = [];

    foreach ((array) $data[$attr['id']] as $v) {
        if (isset($attr['opt'][$v])) {
            $result[] = $attr['opt'][$v];
        }
    }

    return $result ? enc(implode(', ', $result)) : '';
}

/**
 * Date viewer
 */
function date(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), app\cfg('app', 'date')) : '';
}

/**
 * Datetime viewer
 */
function datetime(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), app\cfg('app', 'datetime')) : '';
}

/**
 * Time viewer
 */
function time(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), app\cfg('app', 'time')) : '';
}

/**
 * Rich text viewer
 */
function rte(array $attr, array $data): string
{
    return (string) $data[$attr['id']];
}

/**
 * Iframe viewer
 */
function iframe(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('figure', ['class' => 'iframe'], tag('iframe', ['src' => $data[$attr['id']], 'allowfullscreen' => true])) : '';
}

/**
 * File viewer
 */
function file(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('a', ['href' => app\media($data[$attr['id']])], $data[$attr['id']]) : '';
}

/**
 * Image viewer
 */
function image(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('img', ['src' => app\media($data[$attr['id']]), 'alt' => $data[$attr['id']]], null, true) : '';
}

/**
 * Audio viewer
 */
function audio(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('audio', ['src' => app\media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Embed viewer
 */
function embed(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('embed', ['src' => app\media($data[$attr['id']])], null, true) : '';
}

/**
 * Object viewer
 */
function object(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('object', ['data' => app\media($data[$attr['id']])]) : '';
}

/**
 * Video viewer
 */
function video(array $attr, array $data): string
{
    return $data[$attr['id']] ? tag('video', ['src' => app\media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Filesize viewer
 */
function filesize(array $attr, array $data): string
{
    if ($data[$attr['id']]) {
        $units = ['B', 'kB', 'MB', 'GB'];
        $c = count($units);

        foreach ($units as $key => $unit) {
            if ($data[$attr['id']] < 1000 ** ($key + 1) || $c == $key - 1) {
                return round($data[$attr['id']] / 1000 ** $key, 1) . ' ' . $unit;
            }
        }
    }

    return '';
}

/**
 * Position viewer
 */
function pos(array $attr, array $data): string
{
    $parts = explode('.', $data[$attr['id']]);

    foreach ($parts as $k => $v) {
        $parts[$k] = ltrim($v, '0');
    }

    return implode('.', $parts);
}
