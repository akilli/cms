<?php
declare(strict_types = 1);

namespace frontend;

use app;
use filter;
use html;

/**
 * Checkbox
 */
function checkbox(array $attr, $val): string
{
    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    if ($attr['backend'] === 'bool') {
        $attr['opt'] = [1 => app\i18n('Yes')];
    }

    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => 'checkbox',
            'value' => $optId,
            'checked' => in_array($optId, $val)
        ];
        $a = array_replace($attr['html'], $a);
        $html .= html\tag('input', $a, null, true);
        $html .= html\tag('label', ['for' => $htmlId], $optVal);
    }

    return $html;
}

/**
 * Radio
 */
function radio(array $attr, $val): string
{
    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => 'radio',
            'value' => $optId,
            'checked' => $optId === $val,
        ];
        $a = array_replace($attr['html'], $a);
        $html .= html\tag('input', $a, null, true);
        $html .= html\tag('label', ['for' => $htmlId], $optVal);
    }

    return $html;
}

/**
 * Select
 */
function select(array $attr, $val): string
{
    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $html = html\tag('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt'] as $optId => $optVal) {
        $html .= html\tag('option', ['value' => $optId, 'selected' => in_array($optId, $val)], $optVal);
    }

    return html\tag('select', $attr['html'], $html);
}

/**
 * Text
 */
function text(array $attr, ?string $val): string
{
    $attr['html']['type'] = 'text';
    $attr['html']['value'] = $val ? filter\enc($val) : $val;

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Password
 */
function password(array $attr): string
{
    $attr['html']['type'] = 'password';
    $attr['html']['autocomplete'] = 'off';

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Email
 */
function email(array $attr, ?string $val): string
{
    $attr['html']['type'] = 'email';
    $attr['html']['value'] = $val ? filter\enc($val) : $val;

    return html\tag('input', $attr['html'], null, true);
}

/**
 * URL
 */
function url(array $attr, ?string $val): string
{
    $attr['html']['type'] = 'url';
    $attr['html']['value'] = $val ? filter\enc($val) : $val;

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Number
 */
function number(array $attr, $val): string
{
    $attr['html']['type'] = 'number';
    $attr['html']['value'] = $val;

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Range
 */
function range(array $attr, $val): string
{
    $attr['html']['type'] = 'range';
    $attr['html']['value'] = $val;

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Date
 */
function date(array $attr, ?string $val): string
{
    $attr['html']['type'] = 'date';
    $attr['html']['value'] = $val ? filter\date($val, DATE['b'], DATE['f']) : '';

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Datetime
 */
function datetime(array $attr, ?string $val): string
{
    $attr['html']['type'] = 'datetime-local';
    $attr['html']['value'] = $val ? filter\date($val, DATETIME['b'], DATETIME['f']) : '';

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Time
 */
function time(array $attr, ?string $val): string
{
    $attr['html']['type'] = 'time';
    $attr['html']['value'] = $val ? filter\date($val, TIME['b'], TIME['f']) : '';

    return html\tag('input', $attr['html'], null, true);
}

/**
 * File
 */
function file(array $attr): string
{
    $hidden = html\tag('input', ['name' => $attr['html']['name'], 'type' => 'hidden'], null, true);
    $attr['html']['type'] = 'file';

    return $hidden . html\tag('input', $attr['html'], null, true);
}

/**
 * Textarea
 */
function textarea(array $attr, ?string $val): string
{
    $val = $val ? filter\enc($val) : $val;

    return html\tag('textarea', $attr['html'], $val);
}
