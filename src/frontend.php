<?php
declare(strict_types=1);

namespace frontend;

use app;
use attr;
use contentfilter;
use html;
use str;

function bool(?bool $val, array $attr): string
{
    $html = html\element('input', ['name' => $attr['html']['name'], 'type' => 'hidden']);

    return $html . html\element('input', ['type' => 'checkbox', 'value' => 1, 'checked' => !!$val] + $attr['html']);
}

function browser(?int $val, array $attr): string
{
    $allowed = app\allowed(app\id($attr['ref'], 'index'));
    $optionalAndSet = !$attr['required'] && $val;

    if (!$allowed && !$optionalAndSet) {
        return '';
    }

    $opt = $attr['opt']();
    $html = html\element('output', ['id' => $attr['html']['id'] . '-output'], $val ? $opt[$val] : null);
    $html .= html\element('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html']);

    if ($allowed) {
        $browse = app\i18n('Browse');
        $html .= html\element(
            'a',
            [
                'data-id' => $attr['html']['id'],
                'data-ref' => $attr['ref'],
                'data-action' => 'browser',
                'title' => $browse,
            ],
            $browse
        );
    }

    if ($optionalAndSet) {
        $html .= ' ';
        $remove = app\i18n('Remove');
        $html .= html\element(
            'a',
            ['data-id' => $attr['html']['id'], 'data-action' => 'remove', 'title' => $remove],
            $remove
        );
    }

    return $html;
}

function checkbox(?array $val, array $attr): string
{
    $val = (array)$val;
    $html = html\element(
        'input',
        ['id' => $attr['html']['id'], 'name' => str_replace('[]', '', $attr['html']['name']), 'type' => 'hidden']
    );

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = [
            'id' => $id,
            'name' => $attr['html']['name'],
            'type' => 'checkbox',
            'value' => $k,
            'checked' => !!array_keys($val, $k, true),
        ] + $attr['html'];
        $html .= html\element('input', $a) . html\element('label', ['for' => $id], $v);
    }

    return $html;
}

function date(?string $val, array $attr): string
{
    $val = app\datetime($val, APP['date.frontend']);

    return html\element('input', ['type' => 'date', 'value' => $val] + $attr['html']);
}

function datetime(?string $val, array $attr): string
{
    $val = app\datetime($val, APP['datetime.frontend']);

    return html\element('input', ['type' => 'datetime-local', 'value' => $val] + $attr['html']);
}

function decimal(?float $val, array $attr): string
{
    return html\element('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '0.01']);
}

function editor(?string $val, array $attr): string
{
    return textarea($val ? contentfilter\file($val) : $val, $attr);
}

function email(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'email', 'value' => str\enc($val)] + $attr['html']);
}

function file(?string $val, array $attr): string
{
    $html = '';

    if ($val) {
        $a = html\element('a', ['href' => $val], $val);
        $html = html\element('div', ['class' => 'file-current'], app\i18n('Current file:') . ' ' . $a);
    }

    if (!$attr['required']) {
        $id = $attr['html']['id'] . '-delete';
        $del = html\element(
            'input',
            ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => '']
        );
        $del .= html\element('label', ['for' => $id], app\i18n('Delete'));
        $html .= html\element('div', ['class' => 'file-delete'], $del);
    }

    $html .= html\element('input', ['type' => 'file', 'accept' => implode(', ', $attr['accept'])] + $attr['html']);

    return $html;
}

function int(?int $val, array $attr): string
{
    return html\element('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '1']);
}

function json(?array $val, array $attr): string
{
    return textarea(json_encode((array)$val), $attr);
}

function password(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'password', 'value' => false] + $attr['html']);
}

function radio(mixed $val, array $attr): string
{
    $val = is_bool($val) ? (int)$val : $val;
    $html = '';

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = [
            'id' => $id,
            'name' => $attr['html']['name'],
            'type' => 'radio',
            'value' => $k,
            'checked' => $k === $val,
        ] + $attr['html'];
        $html .= html\element('input', $a) . html\element('label', ['for' => $id], $v);
    }

    return $html;
}

function range(?int $val, array $attr): string
{
    return html\element('input', ['type' => 'range', 'value' => $val] + $attr['html'] + ['step' => '1']);
}

function select(mixed $val, array $attr): string
{
    $val = match (true) {
        !attr\set($val) => [],
        is_bool($val) => [(int)$val],
        !is_array($val) => [$val],
        default => $val,
    };
    $html = !empty($attr['html']['multiple']) ? '' : html\element('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt']() as $k => $v) {
        $html .= html\element('option', ['value' => $k, 'selected' => !!array_keys($val, $k, true)], $v);
    }

    return html\element('select', $attr['html'], $html);
}

function tel(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'tel', 'value' => str\enc($val)] + $attr['html']);
}

function text(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'text', 'value' => str\enc($val)] + $attr['html']);
}

function textarea(?string $val, array $attr): string
{
    return html\element('textarea', $attr['html'], str\enc($val));
}

function time(?string $val, array $attr): string
{
    $val = app\datetime($val, APP['time.frontend']);

    return html\element('input', ['type' => 'time', 'value' => $val] + $attr['html']);
}

function url(?string $val, array $attr): string
{
    return html\element('input', ['type' => 'url', 'value' => str\enc($val)] + $attr['html']);
}
