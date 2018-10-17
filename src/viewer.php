<?php
declare(strict_types = 1);

namespace viewer;

use app;
use entity;
use html;

/**
 * URL viewer
 */
function url(string $val, array $attr): string
{
    return html\tag('a', ['href' => $val] + $attr['html'], $val);
}

/**
 * Datetime viewer
 */
function datetime(string $val, array $attr): string
{
    return date_format(date_create($val), $attr['cfg.viewer']);
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
function json(array $val, array $attr): string
{
    return html\tag('pre', $attr['html'], app\enc(print_r($val, true)));
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
 * Option viewer
 */
function opt($val, array $attr): string
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
function entity(int $val, array $attr): string
{
    return $val ? entity\one($attr['ref'], [['id', $val]], ['select' => ['name']])['name'] : '';
}

/**
 * Page viewer
 */
function page(int $val, array $attr): string
{
    if (!$val) {
        return '';
    }

    $page = entity\one($attr['ref'], [['id', $val]], ['select' => ['name', 'menu_name']]);

    return $page['menu_name'] ?: $page['name'];
}

/**
 * File viewer
 */
function file(int $val, array $attr): string
{
    if (!$val || !($file = entity\one($attr['ref'], [['id', $val]], ['select' => ['name', 'info']]))) {
        return '';
    }

    $mime = mime_content_type(app\file($file['name']));
    $type = $mime && preg_match('#^(audio|image|video)/#', $mime, $match) ? $match[1] : null;

    if ($type === 'image') {
        $attr['html']['alt'] = app\enc($file['info']);
        return html\tag('img', ['src' => $file['name']] + $attr['html'], null, true);
    }

    if ($type === 'audio' || $type === 'video') {
        return html\tag($type, ['src' => $file['name'], 'controls' => true] + $attr['html']);
    }

    return html\tag('a', ['href' => $file['name']] + $attr['html'], $file['name']);
}

/**
 * Upload viewer
 */
function upload(string $val, array $attr): string
{
    $attr['ref'] = 'file';

    return $val ? file((int) pathinfo($val, PATHINFO_FILENAME), $attr) : '';
}
