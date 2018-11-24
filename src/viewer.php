<?php
declare(strict_types = 1);

namespace viewer;

use app;
use entity;

/**
 * URL viewer
 */
function url(string $val, array $attr): string
{
    return app\html('a', ['href' => $val] + $attr['html'], $val);
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
    return app\html('pre', $attr['html'], app\enc(print_r($val, true)));
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
    if (!$val || !($data = entity\one($attr['ref'], [['id', $val]], ['select' => ['url', 'mime', 'info']]))) {
        return '';
    }

    if (!preg_match('#^(audio|image|video)/#', $data['mime'], $match)) {
        return app\html('a', ['href' => $data['url']] + $attr['html'], $data['url']);
    }

    if ($match[1] === 'image') {
        $attr['html']['alt'] = app\enc($data['info']);
        return app\html('img', ['src' => $data['url']] + $attr['html']);
    }

    return app\html($match[1], ['src' => $data['url'], 'controls' => true] + $attr['html']);
}

/**
 * Upload viewer
 */
function upload(string $val, array $attr): string
{
    $attr['ref'] = 'file';

    return $val ? file((int) pathinfo($val, PATHINFO_FILENAME), $attr) : '';
}
