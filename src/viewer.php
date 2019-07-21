<?php
declare(strict_types = 1);

namespace viewer;

use app;
use attr;
use entity;
use str;

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
function datetime(string $val, array $attr): string
{
    return attr\datetime($val, $attr['cfg.backend'], $attr['cfg.viewer']);
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
    return app\html('pre', [], str\enc(print_r($val, true)));
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
    $html = '';

    foreach ($val as $v) {
        if (isset($opt[$v])) {
            $html .= ($html ? ', ' : '') . $opt[$v];
        }
    }

    return $html;
}

/**
 * Entity
 */
function entity(int $val, array $attr): string
{
    return entity\one($attr['ref'], [['id', $val]], ['select' => ['name']])['name'];
}

/**
 * Multi-Entity
 */
function multientity(array $val, array $attr): string
{
    return implode(', ', array_column(entity\all($attr['ref'], [['id', $val]], ['select' => ['name']]), 'name'));
}

/**
 * File
 */
function file($val, array $attr): string
{
    $attr['ref'] = $attr['ref'] ?: 'file';
    $crit = is_string($val) ? [['url', $val]] : [['id', $val]];

    if (!$data = entity\one($attr['ref'], $crit, ['select' => ['url', 'mime', 'info']])) {
        return '';
    }

    if ($data['mime'] === 'text/html') {
        return app\html('iframe', ['src' => $data['url'], 'allowfullscreen' => 'allowfullscreen'], $data['url']);
    }

    if (($p = explode('/', $data['mime'])) && $p[0] === 'image') {
        return app\html('img', ['src' => $data['url'], 'alt' => str\enc($data['info'])]);
    }

    if (in_array($p[0], ['audio', 'video'])) {
        return app\html($p[0], ['src' => $data['url'], 'controls' => true]);
    }

    return app\html('a', ['href' => $data['url']], $data['url']);
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
