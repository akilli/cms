<?php
declare(strict_types=1);

namespace frontend;

use app;
use attr;
use str;

/**
 * Text
 */
function text(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'text', 'value' => str\enc($val)] + $attr['html']);
}

/**
 * Email
 */
function email(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'email', 'value' => str\enc($val)] + $attr['html']);
}

/**
 * URL
 */
function url(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'url', 'value' => str\enc($val)] + $attr['html']);
}

/**
 * Telephone
 */
function tel(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'tel', 'value' => str\enc($val)] + $attr['html']);
}

/**
 * Password
 */
function password(?string $val, array $attr): string
{
    return app\html('input', ['type' => 'password', 'value' => false] + $attr['html']);
}

/**
 * Int
 */
function int(?int $val, array $attr): string
{
    return app\html('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '1']);
}

/**
 * Decimal
 */
function decimal(?float $val, array $attr): string
{
    return app\html('input', ['type' => 'number', 'value' => $val] + $attr['html'] + ['step' => '0.01']);
}

/**
 * Range
 */
function range(?int $val, array $attr): string
{
    return app\html('input', ['type' => 'range', 'value' => $val] + $attr['html'] + ['step' => '1']);
}

/**
 * Datetime
 */
function datetime(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['datetime.backend'], APP['datetime.frontend']) : '';

    return app\html('input', ['type' => 'datetime-local', 'value' => $val] + $attr['html']);
}

/**
 * Date
 */
function date(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['date.backend'], APP['date.frontend']) : '';

    return app\html('input', ['type' => 'date', 'value' => $val] + $attr['html']);
}

/**
 * Time
 */
function time(?string $val, array $attr): string
{
    $val = $val ? attr\datetime($val, APP['time.backend'], APP['time.frontend']) : '';

    return app\html('input', ['type' => 'time', 'value' => $val] + $attr['html']);
}

/**
 * Textarea
 */
function textarea(?string $val, array $attr): string
{
    return app\html('textarea', $attr['html'], str\enc($val));
}

/**
 * JSON
 */
function json(?array $val, array $attr): string
{
    return textarea(json_encode((array) $val), $attr);
}

/**
 * Bool
 */
function bool(?bool $val, array $attr): string
{
    $html = app\html('input', ['name' => $attr['html']['name'], 'type' => 'hidden']);

    return $html . app\html('input', ['type' => 'checkbox', 'value' => 1, 'checked' => !!$val] + $attr['html']);
}

/**
 * Checkbox
 */
function checkbox(?array $val, array $attr): string
{
    $val = (array) $val;
    $html = app\html('input', ['id' => $attr['html']['id'], 'name' => str_replace('[]', '', $attr['html']['name']), 'type' => 'hidden']);

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => $k, 'checked' => !!array_keys($val, $k, true)] + $attr['html'];
        $html .= app\html('input', $a) . app\html('label', ['for' => $id], $v);
    }

    return $html;
}

/**
 * Radio
 */
function radio(mixed $val, array $attr): string
{
    $val = is_bool($val) ? (int) $val : $val;
    $html = '';

    foreach ($attr['opt']() as $k => $v) {
        $id = $attr['html']['id'] . '-' . $k;
        $a = ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'radio', 'value' => $k, 'checked' => $k === $val] + $attr['html'];
        $html .= app\html('input', $a) . app\html('label', ['for' => $id], $v);
    }

    return $html;
}

/**
 * Select
 */
function select(mixed $val, array $attr): string
{
    if ($val === null || $val === '') {
        $val = [];
    } elseif (is_bool($val)) {
        $val = [(int) $val];
    } elseif (!is_array($val)) {
        $val = [$val];
    }

    $html = !empty($attr['html']['multiple']) ? '' : app\html('option', ['value' => ''], app\i18n('Please choose'));

    foreach ($attr['opt']() as $k => $v) {
        $html .= app\html('option', ['value' => $k, 'selected' => !!array_keys($val, $k, true)], $v);
    }

    return app\html('select', $attr['html'], $html);
}

/**
 * Browser
 */
function browser(?int $val, array $attr): string
{
    $browse = app\i18n('Browse');
    $remove = app\i18n('Remove');
    $html = app\html('div', ['id' => $attr['html']['id'] . '-file'], $val ? $attr['viewer']($val, $attr) : '');
    $html .= app\html('input', ['type' => 'hidden', 'value' => $val ?: ''] + $attr['html']);
    $html .= app\html('a', ['data-id' => $attr['html']['id'], 'data-ref' => $attr['ref'], 'data-action' => 'browser', 'title' => $browse], $browse);
    $html .= ' ';
    $html .= app\html('a', ['data-id' => $attr['html']['id'], 'data-action' => 'remove', 'title' => $remove], $remove);

    return  $html;
}

/**
 * File
 */
function file(?string $val, array $attr): string
{
    $html = app\html('div', ['class' => 'view'], $val ? $attr['viewer']($val, $attr) : '');

    if (!$attr['required']) {
        $id = $attr['html']['id'] . '-delete';
        $del = app\html('input', ['id' => $id, 'name' => $attr['html']['name'], 'type' => 'checkbox', 'value' => '']);
        $del .= app\html('label', ['for' => $id], app\i18n('Delete'));
        $html .= app\html('div', ['class' => 'delete'], $del);
    }

    $html .= app\html('input', ['type' => 'file', 'accept' => implode(', ', $attr['accept'])] + $attr['html']);

    return $html;
}
