<?php
declare(strict_types = 1);

namespace viewer;

use app;
use ent;
use html;

/**
 * URL viewer
 */
function url(string $val): string
{
    return html\tag('a', ['href' => $val], $val);
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
 * JSON viewer
 */
function json(array $val): string
{
    return html\tag('pre', [], app\enc(print_r($val, true)));
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
    $out = '';

    foreach ((array) $val as $v) {
        if (isset($opt[$v])) {
            $out .= ($out ? ', ' : '') . $opt[$v];
        }
    }

    return $out;
}

/**
 * File viewer
 */
function file(int $val): string
{
    return $val ? upload(ent\one('file', [['id', $val]], ['select' => ['id', 'name']])['name']) : '';
}

/**
 * Upload viewer
 */
function upload(string $val): string
{
    $ext = pathinfo($val, PATHINFO_EXTENSION);

    if (in_array($ext, app\cfg('opt', 'image'))) {
        return html\tag('img', ['src' => $val, 'alt' => ''], null, true);
    }

    if (in_array($ext, app\cfg('opt', 'video'))) {
        return html\tag('video', ['src' => $val, 'controls' => true]);
    }

    if (in_array($ext, app\cfg('opt', 'audio'))) {
        return html\tag('audio', ['src' => $val, 'controls' => true]);
    }

    return html\tag('a', ['href' => $val], $val);
}
