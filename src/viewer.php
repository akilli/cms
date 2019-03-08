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
 * File Entity
 */
function entity_file(int $val, array $attr): string
{
    if (!$val || !($data = entity\one($attr['ref'], [['id', $val]], ['select' => ['name', 'url', 'mime', 'info', 'thumb_url']]))) {
        return '';
    }

    if ($data['mime'] === 'text/html') {
        $a = $data['thumb_url'] ? ['data-thumb' => $data['thumb_url']] : [];
        return app\html('iframe', ['src' => $data['url'], 'allowfullscreen' => 'allowfullscreen'] + $a, $data['url']);
    }

    if (!preg_match('#^(audio|image|video)/#', $data['mime'], $match)) {
        $v = $data['thumb_url'] ? app\html('img', ['src' => $data['thumb_url'], 'alt' => app\enc($data['info'])]) : $data['url'];
        return app\html('a', ['href' => $data['url']], $v);
    }

    if ($match[1] === 'image') {
        return app\html('img', ['src' => $data['url'], 'alt' => app\enc($data['info'])]);
    }

    if ($data['thumb_url']) {
        $a = ['poster' => $data['thumb_url']];
        $match[1] = 'video';
    } else {
        $a = [];
    }

    return app\html($match[1], ['src' => $data['url'], 'controls' => true] + $a);
}

/**
 * File
 */
function file(string $val, array $attr): string
{
    $attr['ref'] = 'file';

    return $val ? entity_file((int) pathinfo($val, PATHINFO_FILENAME), $attr) : '';
}

/**
 * Audio
 */
function audio(string $val): string
{
    return app\html('audio', ['src' => $val, 'controls' => true]);
}

/**
 * Iframe
 */
function iframe(string $val): string
{
    return app\html('iframe', ['src' => $val, 'allowfullscreen' => 'allowfullscreen']);
}

/**
 * Image
 */
function image(string $val): string
{
    return app\html('img', ['src' => $val]);
}

/**
 * Video
 */
function video(string $val): string
{
    return app\html('video', ['src' => $val, 'controls' => true]);
}
