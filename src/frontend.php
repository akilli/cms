<?php
declare(strict_types = 1);

namespace frontend;

use app;
use filter;
use html;

/**
 * Toggle
 */
function toggle(array $html, bool $val): string
{
    return html\tag('input', ['type' => 'checkbox', 'value' => 1, 'checked' => $val] + $html, null, true);
}

/**
 * Checkbox
 */
function checkbox(array $html, array $val, array $opt): string
{
    $out = '';

    foreach ($opt as $k => $v) {
        $id = $html['id'] . '-' . $k;
        $checked = in_array($k, $val);
        $a = ['id' => $id, 'name' => $html['name'], 'type' => 'checkbox', 'value' => $k, 'checked' => $checked] + $html;
        $out .= html\tag('input', $a, null, true);
        $out .= html\tag('label', ['for' => $id], $v);
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
        $out .= html\tag('input', $a, null, true);
        $out .= html\tag('label', ['for' => $id], $v);
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
    return html\tag('input', ['type' => 'text', 'value' => filter\enc($val)] + $html, null, true);
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
    return html\tag('input', ['type' => 'email', 'value' => filter\enc($val)] + $html, null, true);
}

/**
 * URL
 */
function url(array $html, string $val): string
{
    return html\tag('input', ['type' => 'url', 'value' => filter\enc($val)] + $html, null, true);
}

/**
 * Number
 */
function number(array $html, $val): string
{
    return html\tag('input', ['type' => 'number', 'value' => $val] + $html, null, true);
}

/**
 * Range
 */
function range(array $html, $val): string
{
    return html\tag('input', ['type' => 'range', 'value' => $val] + $html, null, true);
}

/**
 * Date
 */
function date(array $html, string $val): string
{
    $html['value'] = $val ? filter\date($val, APP['backend.date'], APP['frontend.date']) : '';

    return html\tag('input', ['type' => 'date'] + $html, null, true);
}

/**
 * Datetime
 */
function datetime(array $html, string $val): string
{
    $html['value'] = $val ? filter\date($val, APP['backend.datetime'], APP['frontend.datetime']) : '';

    return html\tag('input', ['type' => 'datetime-local'] + $html, null, true);
}

/**
 * Time
 */
function time(array $html, string $val): string
{
    $html['value'] = $val ? filter\date($val, APP['backend.time'], APP['frontend.time']) : '';

    return html\tag('input', ['type' => 'time'] + $html, null, true);
}

/**
 * File
 */
function file(array $html): string
{
    $hidden = html\tag('input', ['name' => $html['name'], 'type' => 'hidden'], null, true);

    return $hidden . html\tag('input', ['type' => 'file'] + $html, null, true);
}

/**
 * Textarea
 */
function textarea(array $html, string $val): string
{
    return html\tag('textarea', $html, filter\enc($val));
}
