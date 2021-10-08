<?php
declare(strict_types=1);

namespace viewer;

use app;
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
    $parentId = app\cfg('entity', $attr['ref'])['parent_id'];
    $tag = in_array('file', [$attr['ref'], $parentId]) ? 'app-file' : 'app-entity';

    return html\element($tag, ['id' => $attr['ref'] . '-' . $val]);
}

function file(string $val): string
{
    $type = preg_match('#^https?://#', $val) ? null : strstr(mime_content_type(app\assetpath($val)), '/', true);
    $attrs['src'] = $val;

    return match ($type) {
        'image' => html\element('img', $attrs),
        'video' => html\element('video', $attrs + ['controls' => true]),
        'audio' => html\element('audio', $attrs + ['controls' => true]),
        default => html\element('a', ['href' => $val], $val),
    };
}

function iframe(string $val): string
{
    return html\element('iframe', ['src' => $val, 'allowfullscreen' => true]);
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
