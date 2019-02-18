<?php
declare(strict_types = 1);

namespace frontend;

use app;
use attr;

/**
 * Input
 */
function input($val, array $attr): string
{
    $attr['html']['type'] = $attr['html']['type'] ?? $attr['type'];

    if ($attr['html']['type'] === 'password') {
        $val = false;
    } elseif ($val && $attr['backend'] === 'datetime') {
        $val = attr\datetime($val, APP['attr.datetime.backend'], APP['attr.datetime.frontend']);
    } elseif ($val && $attr['backend'] === 'date') {
        $val = attr\datetime($val, APP['attr.date.backend'], APP['attr.date.frontend']);
    } elseif ($val && $attr['backend'] === 'time') {
        $val = attr\datetime($val, APP['attr.time.backend'], APP['attr.time.frontend']);
    } elseif ($val && is_string($val)) {
        $val = app\enc($val);
    }

    return app\html('input', ['value' => $val] + $attr['html']);
}

/**
 * Textarea
 */
function textarea(string $val, array $attr): string
{
    return app\html('textarea', $attr['html'], app\enc($val));
}

/**
 * JSON
 */
function json(array $val, array $attr): string
{
    return textarea(json_encode($val), $attr);
}

/**
 * Bool
 */
function bool(bool $val, array $attr): string
{
    $out = app\html('input', ['id' => $attr['html']['id'], 'name' => $attr['html']['name'], 'type' => 'hidden']);

    return $out . app\html('input', ['type' => 'checkbox', 'value' => 1, 'checked' => $val] + $attr['html']);
}

/**
 * Checkbox
 */
function checkbox(array $val, array $attr): string
{
    $out = app\html('input', ['id' => $attr['html']['id'], 'name' => str_replace('[]', '', $attr['html']['name']), 'type' => 'hidden']);

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => $k, 'checked' => in_array($k, $val)] + $attr['html'];
        $out .= app\html('input', $a) . app\html('label', ['for' => $id], $v);
    }

    return $out;
}

/**
 * Radio
 */
function radio($val, array $attr): string
{
    $out = '';

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'radio', 'value' => $k, 'checked' => $k === $val] + $attr['html'];
        $out .= app\html('input', $a) . app\html('label', ['for' => $id], $v);
    }

    return $out;
}

/**
 * Select
 */
function select($val, array $attr): string
{
    if (!is_array($val)) {
        $val = $val === null || $val === '' ? [] : [$val];
    }

    $out = app\html('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt']() as $k => $v) {
        $out .= app\html('option', ['value' => $k, 'selected' => in_array($k, $val)], $v);
    }

    return app\html('select', $attr['html'], $out);
}

/**
 * File
 */
function file(int $val, array $attr): string
{
    $browse = app\i18n('Browse');
    $remove = app\i18n('Remove');
    $out = app\html('div', ['id' => $attr['html']['id'] . '-file'], $val ? $attr['viewer']($val, $attr) : '');
    $out .= app\html('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html']);
    $out .= app\html('a', ['data-id' => $attr['html']['id'], 'data-ref' => $attr['ref'], 'data-action' => 'browser', 'title' => $browse], $browse);
    $out .= ' ';
    $out .= app\html('a', ['data-id' => $attr['html']['id'], 'data-action' => 'remove', 'title' => $remove], $remove);

    return  $out;
}

/**
 * Upload
 */
function upload(string $val, array $attr): string
{
    $out = app\html('div', [], $val ? $attr['viewer']($val, $attr) : '');
    $out .= app\html('input', ['type' => 'file', 'accept' => implode(', ', $attr['accept'])] + $attr['html']);

    return $out;
}
