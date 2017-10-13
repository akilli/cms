<?php
declare(strict_types = 1);

namespace editor;

use const attr\{DATE, DATETIME, TIME};
use function app\i18n;
use function html\tag;
use attr;
use file;
use filter;

/**
 * Option editor
 */
function opt(array $attr, array $data): string
{
    $val = $data[$attr['id']];

    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    if ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['opt'] = [1 => i18n('Yes')];
    }

    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => $attr['frontend'],
            'value' => $optId,
            'checked' => in_array($optId, $val)
        ];
        $a = array_replace($attr['html'], $a);
        $html .= tag('input', $a, null, true);
        $html .= tag('label', ['for' => $htmlId], $optVal);
    }

    return $html;
}

/**
 * Select editor
 */
function select(array $attr, array $data): string
{
    $val = $data[$attr['id']];

    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $html = tag('option', ['value' => ''], i18n('Please choose'));

    foreach ($attr['opt'] as $optId => $optVal) {
        $html .= tag('option', ['value' => $optId, 'selected' => in_array($optId, $val)], $optVal);
    }

    return tag('select', $attr['html'], $html);
}

/**
 * Text editor
 */
function text(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? filter\enc($data[$attr['id']]) : $data[$attr['id']];

    return tag('input', $attr['html'], null, true);
}

/**
 * Password editor
 */
function password(array $attr, array $data): string
{
    $data[$attr['id']] = null;
    $attr['html']['autocomplete'] = 'off';

    return text($attr, $data);
}

/**
 * Int editor
 */
function int(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']];

    return tag('input', $attr['html'], null, true);
}

/**
 * Date editor
 */
function date(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? filter\date($data[$attr['id']], DATE['b'], DATE['f']) : '';

    return tag('input', $attr['html'], null, true);
}

/**
 * Datetime editor
 */
function datetime(array $attr, array $data): string
{
    $attr['html']['type'] = 'datetime-local';
    $attr['html']['value'] = $data[$attr['id']] ? filter\date($data[$attr['id']], DATETIME['b'], DATETIME['f']) : '';

    return tag('input', $attr['html'], null, true);
}

/**
 * Time editor
 */
function time(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? filter\date($data[$attr['id']], TIME['b'], TIME['f']) : '';

    return tag('input', $attr['html'], null, true);
}

/**
 * File editor
 */
function file(array $attr, array $data): string
{
    $current = $data[$attr['id']] ? tag('div', [], attr\viewer($attr, $data)) : '';
    $hidden = tag('input', ['name' => $attr['html']['name'], 'type' => 'hidden'], null, true);
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['accept'] = file\accept($attr['type']);

    return $current . $hidden . tag('input', $attr['html'], null, true);
}

/**
 * Textarea editor
 */
function textarea(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter\enc($data[$attr['id']]) : $data[$attr['id']];

    return tag('textarea', $attr['html'], $data[$attr['id']]);
}

/**
 * JSON editor
 */
function json(array $attr, array $data): string
{
    if (is_array($data[$attr['id']])) {
        $data[$attr['id']] = $data[$attr['id']] ? json_encode($data[$attr['id']]) : '';
    }

    return textarea($attr, $data);
}
