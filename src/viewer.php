<?php
declare(strict_types = 1);

namespace viewer;

use app;
use ent;
use html;

/**
 * URL viewer
 */
function url(array $attr, string $val): string
{
    return html\tag('a', ['href' => $val] + $attr['html'], $val);
}

/**
 * Datetime viewer
 */
function datetime(array $attr, string $val): string
{
    return date_format(date_create($val), $attr['cfg.viewer']);
}

/**
 * Rich text viewer
 */
function rte(array $attr, string $val): string
{
    return $val;
}

/**
 * JSON viewer
 */
function json(array $attr, array $val): string
{
    return html\tag('pre', $attr['html'], app\enc(print_r($val, true)));
}

/**
 * Position viewer
 */
function pos(array $attr, string $val): string
{
    $parts = explode('.', $val);

    foreach ($parts as $k => $v) {
        $parts[$k] = ltrim($v, '0');
    }

    return implode('.', $parts);
}

/**
 * Option viewer
 */
function opt(array $attr, $val): string
{
    if (!is_array($val)) {
        $val = $val === null && $val === '' ? [] : [$val];
    }

    $out = '';

    foreach ($val as $v) {
        if (isset($attr['opt'][$v])) {
            $out .= ($out ? ', ' : '') . $attr['opt'][$v];
        }
    }

    return $out;
}

/**
 * Ent viewer
 */
function ent(array $attr, int $val): string
{
    return $val ? ent\one($attr['ent'], [['id', $val]], ['select' => ['id', 'name']])['name'] : '';
}

/**
 * File viewer
 */
function file(array $attr, int $val): string
{
    if (!$val) {
        return '';
    }

    $file = ent\one($attr['ent'], [['id', $val]], ['select' => ['id', 'name', 'type', 'info']]);

    if (in_array($file['type'] , app\cfg('opt', 'image'))) {
        $attr['html']['alt'] = app\enc($file['info']);
    }

    return upload($attr, $file['name']);
}

/**
 * Upload viewer
 */
function upload(array $attr, string $val): string
{
    $ext = pathinfo($val, PATHINFO_EXTENSION);

    if (in_array($ext, app\cfg('opt', 'image'))) {
        return html\tag('img', ['src' => $val] + $attr['html'], null, true);
    }

    if (in_array($ext, app\cfg('opt', 'video'))) {
        return html\tag('video', ['src' => $val, 'controls' => true] + $attr['html']);
    }

    if (in_array($ext, app\cfg('opt', 'audio'))) {
        return html\tag('audio', ['src' => $val, 'controls' => true] + $attr['html']);
    }

    return html\tag('a', ['href' => $val] + $attr['html'], $val);
}
