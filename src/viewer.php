<?php
declare(strict_types = 1);

namespace viewer;

use app;
use entity;

/**
 * Email
 */
function email(string $val): string
{
    return app\html('a', ['href' => 'mailto:' . $val], $val);
}

/**
 * URL
 */
function url(string $val): string
{
    return app\html('a', ['href' => $val], $val);
}

/**
 * Datetime
 */
function datetime(string $val): string
{
    return date_format(date_create($val), app\cfg('app', 'i18n.datetime'));
}

/**
 * Date
 */
function date(string $val): string
{
    return date_format(date_create($val), app\cfg('app', 'i18n.date'));
}

/**
 * Time
 */
function time(string $val): string
{
    return date_format(date_create($val), app\cfg('app', 'i18n.time'));
}

/**
 * Rich text
 */
function rte(string $val): string
{
    return $val;
}

/**
 * JSON
 */
function json(array $val): string
{
    return app\html('pre', [], app\enc(print_r($val, true)));
}

/**
 * Position
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
 * Option
 */
function opt($val, array $attr): string
{
    if (!is_array($val)) {
        $val = $val === null && $val === '' ? [] : [$val];
    }

    $opt = $attr['opt']();
    $out = '';

    foreach ($val as $v) {
        if (isset($opt[$v])) {
            $out .= ($out ? ', ' : '') . $opt[$v];
        }
    }

    return $out;
}

/**
 * Ent
 */
function entity(int $val, array $attr): string
{
    return $val ? entity\one($attr['ref'], [['id', $val]], ['select' => ['name']])['name'] : '';
}

/**
 * Page
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
 * File
 */
function file(int $val, array $attr): string
{
    if (!$val || !($data = entity\one($attr['ref'], [['id', $val]], ['select' => ['url', 'mime', 'info']]))) {
        return '';
    }

    if (!preg_match('#^(audio|image|video)/#', $data['mime'], $match)) {
        return app\html('a', ['href' => $data['url']], $data['url']);
    }

    if ($match[1] === 'image') {
        return app\html('img', ['src' => $data['url'], 'alt' => app\enc($data['info'])]);
    }

    return app\html($match[1], ['src' => $data['url'], 'controls' => true]);
}

/**
 * Upload
 */
function upload(string $val, array $attr): string
{
    $attr['ref'] = 'file';

    return $val ? file((int) pathinfo($val, PATHINFO_FILENAME), $attr) : '';
}
