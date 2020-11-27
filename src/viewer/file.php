<?php
declare(strict_types=1);

namespace viewer;

use app;
use entity;
use str;

/**
 * File
 */
function file(string|int $val, array $attr): string
{
    $attr['ref'] = $attr['ref'] ?: 'file';
    $crit = is_string($val) ? [['url', $val]] : [['id', $val]];

    if (!$data = entity\one($attr['ref'], $crit, select: ['url', 'mime', 'thumb', 'info'])) {
        return '';
    }

    if ($data['mime'] === 'text/html') {
        $a = $data['thumb'] ? ['data-thumb' => $data['thumb']] : [];
        return app\html('iframe', ['src' => $data['url'], 'allowfullscreen' => 'allowfullscreen'] + $a);
    }

    if (($p = explode('/', $data['mime'])) && $p[0] === 'image') {
        return app\html('img', ['src' => $data['url'], 'alt' => str\enc($data['info'])]);
    }

    if ($p[0] === 'audio' && !$data['thumb']) {
        return app\html('audio', ['src' => $data['url'], 'controls' => true]);
    }

    if (in_array($p[0], ['audio', 'video'])) {
        $a = $data['thumb'] ? ['poster' => $data['thumb']] : [];
        return app\html('video', ['src' => $data['url'], 'controls' => true] + $a);
    }

    $v = $data['thumb'] ? app\html('img', ['src' => $data['thumb'], 'alt' => str\enc($data['info'])]) : $data['url'];

    return app\html('a', ['href' => $data['url']], $v);
}
