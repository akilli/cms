<?php
declare(strict_types = 1);

namespace frontend;

use app;
use attr;
use entity;

/**
 * Input
 */
function input(string $val, array $attr): string
{
    return app\html('input', ['value' => app\enc($val)] + $attr['html']);
}

/**
 * Password
 */
function password(string $val, array $attr): string
{
    return app\html('input', ['autocomplete' => 'off', 'type' => 'password', 'value' => false] + $attr['html']);
}

/**
 * Number
 */
function number($val, array $attr): string
{
    if (empty($attr['html']['step'])) {
        $attr['html']['step'] = is_float($val) ? '0.01' : '1';
    }

    return app\html('input', ['value' => $val] + $attr['html']);
}

/**
 * Datetime
 */
function datetime(string $val, array $attr): string
{
    $attr['html']['value'] = $val ? attr\datetime($val, $attr['cfg.backend'], $attr['cfg.frontend']) : '';

    return app\html('input', $attr['html']);
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

    foreach ($attr['opt'] as $k => $v) {
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

    foreach ($attr['opt'] as $k => $v) {
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
        $val = $val === null && $val === '' ? [] : [$val];
    }

    $out = app\html('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt'] as $k => $v) {
        $out .= app\html('option', ['value' => $k, 'selected' => in_array($k, $val)], $v);
    }

    return app\html('select', $attr['html'], $out);
}

/**
 * Entity
 */
function entity(int $val, array $attr): string
{
    if (($attr['opt'] = & app\registry('opt.entity.' . $attr['ref'])) === null) {
        $attr['opt'] = array_column(entity\all($attr['ref'], [], ['select' => ['id', 'name'], 'order' => ['name' => 'asc']]), 'name', 'id');
    }

    return select($val, $attr);
}

/**
 * Page
 */
function page(int $val, array $attr): string
{
    if (($attr['opt'] = & app\registry('opt.page')) === null) {
        $attr['opt'] = [];

        foreach (entity\all('page_content', [], ['select' => ['id', 'name', 'menu_name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
            $attr['opt'][$item['id']] = attr\viewer($item, $item['_entity']['attr']['pos']) . ' ' . ($item['menu_name'] ?: $item['name']);
        }
    }

    return select($val, $attr);
}

/**
 * File
 */
function file(int $val, array $attr): string
{
    $out = app\html('div', ['id' => $attr['html']['id'] . '-file'], $val ? $attr['viewer']($val, $attr) : '');
    $out .= app\html('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html']);
    $out .= app\html('span', ['data-id' => $attr['html']['id'], 'data-action' => 'browser'], app\i18n('Browse'));
    $out .= ' ';
    $out .= app\html('span', ['data-id' => $attr['html']['id'], 'data-action' => 'remove'], app\i18n('Remove'));

    return  $out;
}

/**
 * File Upload
 */
function upload(string $val, array $attr): string
{
    $out = app\html('div', [], $val ? $attr['viewer']($val, $attr) : '');
    $out .= app\html('input', ['type' => 'file'] + $attr['html']);

    return $out;
}
