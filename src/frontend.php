<?php
declare(strict_types = 1);

namespace frontend;

use app;
use attr;
use entity;
use html;
use viewer;

/**
 * Input
 */
function input(array $attr, string $val): string
{
    return html\tag('input', ['value' => app\enc($val)] + $attr['html'], null, true);
}

/**
 * Password
 */
function password(array $attr): string
{
    return html\tag('input', ['autocomplete' => 'off', 'type' => 'password', 'value' => false] + $attr['html'], null, true);
}

/**
 * Number
 */
function number(array $attr, $val): string
{
    if (empty($attr['html']['step'])) {
        $attr['html']['step'] = is_float($val) ? '0.01' : '1';
    }

    return html\tag('input', ['value' => $val] + $attr['html'], null, true);
}

/**
 * Datetime
 */
function datetime(array $attr, string $val): string
{
    $attr['html']['value'] = $val ? attr\datetime($val, $attr['cfg.backend'], $attr['cfg.frontend']) : '';

    return html\tag('input', $attr['html'], null, true);
}

/**
 * Textarea
 */
function textarea(array $attr, string $val): string
{
    return html\tag('textarea', $attr['html'], app\enc($val));
}

/**
 * JSON
 */
function json(array $attr, array $val): string
{
    return textarea($attr['html'], json_encode($val));
}

/**
 * Bool
 */
function bool(array $attr, bool $val): string
{
    $out = html\tag('input', ['id' => $attr['html']['id'], 'name' => $attr['html']['name'], 'type' => 'hidden'], null, true);

    return $out . html\tag('input', ['type' => 'checkbox', 'value' => 1, 'checked' => $val] + $attr['html'], null, true);
}

/**
 * Checkbox
 */
function checkbox(array $attr, array $val): string
{
    $out = html\tag('input', ['id' => $attr['html']['id'], 'name' => str_replace('[]', '', $attr['html']['name']), 'type' => 'hidden'], null, true);

    foreach ($attr['opt'] as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => $k, 'checked' => in_array($k, $val)] + $attr['html'];
        $out .= html\tag('input', $a, null, true) . html\tag('label', ['for' => $id], $v);
    }

    return $out;
}

/**
 * Radio
 */
function radio(array $attr, $val): string
{
    $out = '';

    foreach ($attr['opt'] as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'radio', 'value' => $k, 'checked' => $k === $val] + $attr['html'];
        $out .= html\tag('input', $a, null, true) . html\tag('label', ['for' => $id], $v);
    }

    return $out;
}

/**
 * Select
 */
function select(array $attr, $val): string
{
    if (!is_array($val)) {
        $val = $val === null && $val === '' ? [] : [$val];
    }

    $out = html\tag('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt'] as $k => $v) {
        $out .= html\tag('option', ['value' => $k, 'selected' => in_array($k, $val)], $v);
    }

    return html\tag('select', $attr['html'], $out);
}

/**
 * Entity
 */
function entity(array $attr, int $val): string
{
    if (($attr['opt'] = & app\registry('opt.entity.' . $attr['ref'])) === null) {
        $attr['opt'] = array_column(entity\all($attr['ref'], [], ['select' => ['id', 'name'], 'order' => ['name' => 'asc']]), 'name', 'id');
    }

    return select($attr, $val);
}

/**
 * Page
 */
function page(array $attr, int $val): string
{
    if (($attr['opt'] = & app\registry('opt.page')) === null) {
        $pos = app\cfg('entity', 'page')['attr']['pos'];
        $attr['opt'] = [];

        foreach (entity\all('content', [], ['select' => ['id', 'name', 'menuname', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
            $attr['opt'][$item['id']] = attr\viewer($pos, $item) . ' ' . ($item['menuname'] ?: $item['name']);
        }
    }

    return select($attr, $val);
}

/**
 * File
 */
function file(array $attr, int $val): string
{
    $out = html\tag('div', ['id' => $attr['html']['id'] . '-file'], $val ? viewer\file($attr, $val) : '');
    $out .= html\tag('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html'], null, true);
    $out .= html\tag('span', ['data-id' => $attr['html']['id'], 'data-act' => 'browser'], app\i18n('Browse'));
    $out .= ' ';
    $out .= html\tag('span', ['data-id' => $attr['html']['id'], 'data-act' => 'remove'], app\i18n('Remove'));

    return  $out;
}

/**
 * File Upload
 */
function upload(array $attr, string $val): string
{
    $out = html\tag('div', [], $val ? viewer\upload($attr, $val) : '');
    $out .= html\tag('input', ['type' => 'file'] + $attr['html'], null, true);

    return $out;
}
