<?php
declare(strict_types = 1);

namespace viewer;

use app;
use entity;
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
function entity(array $attr, int $val): string
{
    return $val ? entity\one($attr['ref'], [['id', $val]], ['select' => ['id', 'name']])['name'] : '';
}

/**
 * Page viewer
 */
function page(array $attr, int $val): string
{
    if (!$val) {
        return '';
    }

    $page = entity\one($attr['ref'], [['id', $val]], ['select' => ['id', 'name', 'menu_name']]);

    return $page['menu_name'] ?: $page['name'];
}

/**
 * File viewer
 */
function file(array $attr, int $val): string
{
    if (!$val) {
        return '';
    }

    $file = entity\one($attr['ref'], [['id', $val]], ['select' => ['id', 'name']]);

    return upload($attr, $file['name']);
}

/**
 * Upload viewer
 */
function upload(array $attr, string $val): string
{
    if (!$val || !($file = entity\one('file', [['name', $val]], ['select' => ['id', 'info']]))) {
        return '';
    }

    $mime = mime_content_type(app\file($val));
    $type = $mime && preg_match('#^(audio|image|video)/#', $mime, $match) ? $match[1] : null;

    if ($type === 'image') {
        $attr['html']['alt'] = app\enc($file['info']);
        return html\tag('img', ['src' => $val] + $attr['html'], null, true);
    }

    if ($type === 'audio' || $type === 'video') {
        return html\tag($type, ['src' => $val, 'controls' => true] + $attr['html']);
    }

    return html\tag('a', ['href' => $val] + $attr['html'], $val);
}
