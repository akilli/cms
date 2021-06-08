<?php
declare(strict_types=1);

namespace viewer;

use app;
use attr;
use entity;
use html;
use str;

function audio(string $val): string
{
    return html\element('audio', ['src' => $val, 'controls' => true]);
}

function date(string $val): string
{
    return attr\datetime($val, APP['date.backend'], app\cfg('app', 'date'));
}

function datetime(string $val): string
{
    return attr\datetime($val, APP['datetime.backend'], app\cfg('app', 'datetime'));
}

function editor(string $val): string
{
    return $val;
}

function email(string $val): string
{
    return html\element('a', ['href' => 'mailto:' . $val], $val);
}

function entity(int $val, array $attr): string
{
    return entity\one($attr['ref'], crit: [['id', $val]], select: ['name'])['name'];
}

function file(string|int $val, array $attr): string
{
    $attr['ref'] = $attr['ref'] ?: 'file';
    $crit = is_string($val) ? [['url', $val]] : [['id', $val]];

    if (!$data = entity\one($attr['ref'], crit: $crit, select: ['url', 'mime', 'thumb', 'info'])) {
        return '';
    }

    if ($data['mime'] === 'text/html') {
        $a = $data['thumb'] ? ['data-thumb' => $data['thumb']] : [];
        return html\element('iframe', ['src' => $data['url'], 'allowfullscreen' => 'allowfullscreen'] + $a);
    }

    if (($p = explode('/', $data['mime'])) && $p[0] === 'image') {
        return html\element('img', ['src' => $data['url'], 'alt' => str\enc($data['info'])]);
    }

    if ($p[0] === 'audio' && !$data['thumb']) {
        return html\element('audio', ['src' => $data['url'], 'controls' => true]);
    }

    if (in_array($p[0], ['audio', 'video'])) {
        $a = $data['thumb'] ? ['poster' => $data['thumb']] : [];
        return html\element('video', ['src' => $data['url'], 'controls' => true] + $a);
    }

    $v = $data['url'];

    if ($data['thumb']) {
        $v = html\element('img', ['src' => $data['thumb'], 'alt' => str\enc($data['info'])]);
    }

    return html\element('a', ['href' => $data['url']], $v);
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
    return implode(', ', array_column(entity\all($attr['ref'], crit: [['id', $val]], select: ['name']), 'name'));
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

function tel(string $val): string
{
    return html\element('a', ['href' => 'tel:' . $val], $val);
}

function time(string $val): string
{
    return attr\datetime($val, APP['time.backend'], app\cfg('app', 'time'));
}

function url(string $val): string
{
    return html\element('a', ['href' => $val], $val);
}

function video(string $val): string
{
    return html\element('video', ['src' => $val, 'controls' => true]);
}
