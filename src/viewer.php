<?php
declare(strict_types = 1);

namespace viewer;

use app;
use attr;
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
    return attr\datetime($val, APP['attr.datetime.backend'], app\cfg('app', 'i18n.datetime'));
}

/**
 * Date
 */
function date(string $val): string
{
    return attr\datetime($val, APP['attr.date.backend'], app\cfg('app', 'i18n.date'));
}

/**
 * Time
 */
function time(string $val): string
{
    return attr\datetime($val, APP['attr.time.backend'], app\cfg('app', 'i18n.time'));
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
    $val = is_array($val) ? $val : [$val];
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
 * Entity
 */
function entity(int $val, array $attr): string
{
    return $val ? entity\one($attr['ref'], [['id', $val]], ['select' => ['name']])['name'] : '';
}

/**
 * Multi-Entity
 */
function multientity(array $val, array $attr): string
{
    return $val ? implode(', ', array_column(entity\all($attr['ref'], [['id', $val]], ['select' => ['name']]), 'name')) : '';
}

/**
 * File
 */
function file(int $val, array $attr): string
{
    if (!$val || !($data = entity\one($attr['ref'], [['id', $val]], ['select' => ['url', 'mime', 'info']]))) {
        return '';
    }

    if ($data['mime'] === 'text/html') {
        return app\html('iframe', ['src' => $data['url'], 'allowfullscreen' => 'allowfullscreen'], $data['url']);
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
