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
    $html['type'] = 'checkbox';
    $html['value'] = 1;
    $html['checked'] = $val;

    return html\tag('input', $html, null, true);
}

/**
 * Checkbox
 */
function checkbox(array $html, array $val, array $opt): string
{
    $out = '';

    foreach ($opt as $optId => $optVal) {
        $htmlId = $html['id'] . '-' . $optId;
        $a = ['id' => $htmlId, 'name' => $html['name'], 'type' => 'checkbox', 'value' => $optId, 'checked' => in_array($optId, $val)];
        $a = array_replace($html, $a);
        $out .= html\tag('input', $a, null, true);
        $out .= html\tag('label', ['for' => $htmlId], $optVal);
    }

    return $out;
}

/**
 * Radio
 */
function radio(array $html, $val, array $opt): string
{
    $out = '';

    foreach ($opt as $optId => $optVal) {
        $htmlId = $html['id'] . '-' . $optId;
        $a = ['id' => $htmlId, 'name' => $html['name'], 'type' => 'radio', 'value' => $optId, 'checked' => $optId === $val];
        $a = array_replace($html, $a);
        $out .= html\tag('input', $a, null, true);
        $out .= html\tag('label', ['for' => $htmlId], $optVal);
    }

    return $out;
}

/**
 * Select
 */
function select(array $html, $val, array $opt): string
{
    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $out = html\tag('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($opt as $optId => $optVal) {
        $out .= html\tag('option', ['value' => $optId, 'selected' => in_array($optId, $val)], $optVal);
    }

    return html\tag('select', $html, $out);
}

/**
 * Text
 */
function text(array $html, string $val): string
{
    $html['type'] = 'text';
    $html['value'] = $val ? filter\enc($val) : $val;

    return html\tag('input', $html, null, true);
}

/**
 * Password
 */
function password(array $html): string
{
    $html['type'] = 'password';
    $html['autocomplete'] = 'off';

    return html\tag('input', $html, null, true);
}

/**
 * Email
 */
function email(array $html, string $val): string
{
    $html['type'] = 'email';
    $html['value'] = $val ? filter\enc($val) : $val;

    return html\tag('input', $html, null, true);
}

/**
 * URL
 */
function url(array $html, string $val): string
{
    $html['type'] = 'url';
    $html['value'] = $val ? filter\enc($val) : $val;

    return html\tag('input', $html, null, true);
}

/**
 * Number
 */
function number(array $html, $val): string
{
    $html['type'] = 'number';
    $html['value'] = $val;

    return html\tag('input', $html, null, true);
}

/**
 * Range
 */
function range(array $html, $val): string
{
    $html['type'] = 'range';
    $html['value'] = $val;

    return html\tag('input', $html, null, true);
}

/**
 * Date
 */
function date(array $html, string $val): string
{
    $html['type'] = 'date';
    $html['value'] = $val ? filter\date($val, APP['backend.date'], APP['frontend.date']) : '';

    return html\tag('input', $html, null, true);
}

/**
 * Datetime
 */
function datetime(array $html, string $val): string
{
    $html['type'] = 'datetime-local';
    $html['value'] = $val ? filter\date($val, APP['backend.datetime'], APP['frontend.datetime']) : '';

    return html\tag('input', $html, null, true);
}

/**
 * Time
 */
function time(array $html, string $val): string
{
    $html['type'] = 'time';
    $html['value'] = $val ? filter\date($val, APP['backend.time'], APP['frontend.time']) : '';

    return html\tag('input', $html, null, true);
}

/**
 * File
 */
function file(array $html): string
{
    $hidden = html\tag('input', ['name' => $html['name'], 'type' => 'hidden'], null, true);
    $html['type'] = 'file';

    return $hidden . html\tag('input', $html, null, true);
}

/**
 * Textarea
 */
function textarea(array $html, string $val): string
{
    $val = $val ? filter\enc($val) : $val;

    return html\tag('textarea', $html, $val);
}
