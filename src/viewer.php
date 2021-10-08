<?php
declare(strict_types=1);

namespace viewer;

use app;
use entity;
use html;
use str;

function audio(string $val): string
{
    return html\element('audio', ['src' => $val, 'controls' => true]);
}

function date(string $val): string
{
    return (string)app\datetime($val, app\cfg('app', 'date'));
}

function datetime(string $val): string
{
    return (string)app\datetime($val, app\cfg('app', 'datetime'));
}

function email(string $val): string
{
    return html\element('a', ['href' => 'mailto:' . $val], $val);
}

function enc(mixed $val): string
{
    return str\enc((string)$val);
}

function entity(int $val, array $attr): string
{
    return html\element('app-entity', ['id' => $attr['ref'] . '-' . $val]);
}

function file(string|int $val, array $attr): string
{
    $attr['ref'] = $attr['ref'] ?: 'file';
    $crit = is_string($val) ? [['name', $val]] : [['id', $val]];

    if (!$data = entity\one($attr['ref'], crit: $crit, select: ['name', 'mime', 'thumb', 'info'])) {
        return '';
    }

    if ($data['mime'] === 'text/html') {
        $a = $data['thumb'] ? ['data-thumb' => $data['thumb']] : [];

        return html\element('iframe', ['src' => $data['name'], 'allowfullscreen' => 'allowfullscreen'] + $a);
    }

    $type = strstr($data['mime'], '/', true);

    if ($type === 'image') {
        return html\element('img', ['src' => $data['name'], 'alt' => str\enc($data['info'])]);
    }

    if ($type === 'audio' && !$data['thumb']) {
        return html\element('audio', ['src' => $data['name'], 'controls' => true]);
    }

    if (in_array($type, ['audio', 'video'])) {
        $a = $data['thumb'] ? ['poster' => $data['thumb']] : [];

        return html\element('video', ['src' => $data['name'], 'controls' => true] + $a);
    }

    $v = $data['name'];

    if ($data['thumb']) {
        $v = html\element('img', ['src' => $data['thumb'], 'alt' => str\enc($data['info'])]);
    }

    return html\element('a', ['href' => $data['name']], $v);
}

function iframe(string $val): string
{
    return html\element('iframe', ['src' => $val, 'allowfullscreen' => 'allowfullscreen']);
}

function image(string $val): string
{
    return html\element('img', ['src' => $val]);
}

function json(array $val): string
{
    return html\element('pre', [], str\enc(print_r($val, true)));
}

function multientity(array $val, array $attr): string
{
    $html = '';

    foreach ($val as $v) {
        $html .= ($html ? ', ' : '') . entity($v, $attr);
    }

    return $html;
}

function opt(mixed $val, array $attr): string
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

function position(string $val): string
{
    return preg_replace('#(^|\.)0+#', '$1', $val);
}

function raw(mixed $val): string
{
    return (string)$val;
}

function tel(string $val): string
{
    return html\element('a', ['href' => 'tel:' . $val], $val);
}

function time(string $val): string
{
    return (string)app\datetime($val, app\cfg('app', 'time'));
}

function url(string $val): string
{
    return html\element('a', ['href' => $val], $val);
}

function video(string $val): string
{
    return html\element('video', ['src' => $val, 'controls' => true]);
}
