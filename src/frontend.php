<?php
declare(strict_types = 1);

namespace frontend;

use app;
use attr;
use html;
use viewer;

/**
 * Bool
 */
function bool(array $html, bool $val): string
{
    $out = html\tag('input', ['id' => $html['id'], 'name' => $html['name'], 'type' => 'hidden'], null, true);

    return $out . html\tag('input', ['type' => 'checkbox', 'value' => 1, 'checked' => $val] + $html, null, true);
}

/**
 * Checkbox
 */
function checkbox(array $html, array $val, array $opt): string
{
    $out = html\tag('input', ['id' => $html['id'], 'name' => str_replace('[]', '', $html['name']), 'type' => 'hidden'], null, true);

    foreach ($opt as $k => $v) {
        $id = $html['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $html['name'], 'type' => 'checkbox', 'value' => $k, 'checked' => in_array($k, $val)] + $html;
        $out .= html\tag('input', $a, null, true) . html\tag('label', ['for' => $id], $v);
    }

    return $out;
}

/**
 * Radio
 */
function radio(array $html, $val, array $opt): string
{
    $out = '';

    foreach ($opt as $k => $v) {
        $id = $html['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $html['name'], 'type' => 'radio', 'value' => $k, 'checked' => $k === $val] + $html;
        $out .= html\tag('input', $a, null, true) . html\tag('label', ['for' => $id], $v);
    }

    return $out;
}

/**
 * Select
 */
function select(array $html, $val, array $opt): string
{
    if (!is_array($val)) {
        $val = $val === null && $val === '' ? [] : [$val];
    }

    $out = html\tag('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($opt as $k => $v) {
        $out .= html\tag('option', ['value' => $k, 'selected' => in_array($k, $val)], $v);
    }

    return html\tag('select', $html, $out);
}

/**
 * Text
 */
function text(array $html, string $val): string
{
    return html\tag('input', ['type' => 'text', 'value' => app\enc($val)] + $html, null, true);
}

/**
 * Password
 */
function password(array $html): string
{
    return html\tag('input', ['type' => 'password', 'autocomplete' => 'off'] + $html, null, true);
}

/**
 * Email
 */
function email(array $html, string $val): string
{
    return html\tag('input', ['type' => 'email', 'value' => app\enc($val)] + $html, null, true);
}

/**
 * URL
 */
function url(array $html, string $val): string
{
    return html\tag('input', ['type' => 'url', 'value' => app\enc($val)] + $html, null, true);
}

/**
 * Number
 */
function number(array $html, $val): string
{
    $a = ['type' => 'number', 'value' => $val] + $html;
    $a['step'] = is_float($val) ? '0.01' : '1';

    return html\tag('input', $a, null, true);
}

/**
 * Range
 */
function range(array $html, $val): string
{
    $a = ['type' => 'range', 'value' => $val] + $html;
    $a['step'] = is_float($val) ? '0.01' : '1';

    return html\tag('input', $a, null, true);
}

/**
 * Date
 */
function date(array $html, string $val): string
{
    $html['value'] = $val ? attr\datetime($val, APP['backend.date'], APP['frontend.date']) : '';

    return html\tag('input', ['type' => 'date'] + $html, null, true);
}

/**
 * Datetime
 */
function datetime(array $html, string $val): string
{
    $html['value'] = $val ? attr\datetime($val, APP['backend.datetime'], APP['frontend.datetime']) : '';

    return html\tag('input', ['type' => 'datetime-local'] + $html, null, true);
}

/**
 * Time
 */
function time(array $html, string $val): string
{
    $html['value'] = $val ? attr\datetime($val, APP['backend.time'], APP['frontend.time']) : '';

    return html\tag('input', ['type' => 'time'] + $html, null, true);
}

/**
 * Textarea
 */
function textarea(array $html, string $val): string
{
    return html\tag('textarea', $html, app\enc($val));
}

/**
 * JSON
 */
function json(array $html, array $val): string
{
    return textarea($html, json_encode($val));
}

/**
 * File
 */
function file(array $html, int $val): string
{
    $out = html\tag('div', ['id' => $html['id'] . '-file'], $val ? viewer\file($val) : '');
    $out .= html\tag('input', ['type' => 'hidden', 'value' => $val ?: ''] + $html, null, true);
    $out .= html\tag('span', ['data-id' => $html['id'], 'data-act' => 'browser'], app\i18n('Browse'));
    $out .= ' ';
    $out .= html\tag('span', ['data-id' => $html['id'], 'data-act' => 'remove'], app\i18n('Remove'));

    return  $out;
}

/**
 * File Upload
 */
function upload(array $html, string $val): string
{
    $out = html\tag('div', [], $val ? viewer\upload($val) : '');
    $out .= html\tag('input', ['type' => 'file'] + $html, null, true);

    return $out;
}
