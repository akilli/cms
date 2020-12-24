<?php
declare(strict_types=1);

namespace attr\file;

use app;
use attr\urlpath;
use entity;
use html;
use str;
use DomainException;

function frontend(?string $val, array $attr): string
{
    $html = html\element('div', ['class' => 'view'], $val ? $attr['viewer']($val, $attr) : '');

    if (!$attr['required']) {
        $id = $attr['html']['id'] . '-delete';
        $del = html\element('input', ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => '']);
        $del .= html\element('label', ['for' => $id], app\i18n('Delete'));
        $html .= html\element('div', ['class' => 'delete'], $del);
    }

    $html .= html\element('input', ['type' => 'file', 'accept' => implode(', ', $attr['accept'])] + $attr['html']);

    return $html;
}

/**
 * @throws DomainException
 */
function validator(string $val, array $attr): string
{
    $mime = app\data('request', 'file')[$attr['id']]['type'] ?? null;

    if ($val && (!$mime || !in_array($mime, $attr['accept']))) {
        throw new DomainException(app\i18n('Invalid file type'));
    }

    return urlpath\validator($val);
}

function viewer(string|int $val, array $attr): string
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

    $v = $data['thumb'] ? html\element('img', ['src' => $data['thumb'], 'alt' => str\enc($data['info'])]) : $data['url'];

    return html\element('a', ['href' => $data['url']], $v);
}
