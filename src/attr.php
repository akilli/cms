<?php
declare(strict_types = 1);

namespace attr;

use app;
use arr;
use entity;
use DomainException;

/**
 * Validator
 *
 * @return mixed
 *
 * @throws DomainException
 */
function validator(array $data, array $attr)
{
    $val = $data[$attr['id']] ?? null;

    if ($attr['nullable'] && $val === null) {
        return $val;
    }

    $attr['opt'] = opt($data, $attr);

    if ($attr['validator']) {
        $val = $attr['validator']($val, $attr);
    }

    $pattern = $attr['pattern'] ? '#^' . str_replace('#', '\#', $attr['pattern']) . '$#' : null;
    $vp = is_array($val) ? implode("\n", $val) : (string) $val;
    $vs = is_array($val) ? $val : [$val];
    $crit = $data['_old'] ? [[$attr['id'], $val], ['id', $data['_old']['id'], APP['op']['!=']]] : [[$attr['id'], $val]];

    if ($pattern && $val !== null && $val !== '' && !preg_match($pattern, $vp)) {
        throw new DomainException(app\i18n('Value contains invalid characters'));
    } elseif ($attr['required'] && ($val === null || $val === '')) {
        throw new DomainException(app\i18n('Value is required'));
    } elseif ($attr['unique'] && entity\size($data['_entity']['id'], $crit)) {
        throw new DomainException(app\i18n('Value must be unique'));
    }

    foreach ($vs as $v) {
        $length = in_array($attr['backend'], ['json', 'text', 'varchar']) ? mb_strlen($v) : $v;

        if ($attr['min'] > 0 && $length < $attr['min'] || $attr['max'] > 0 && $length > $attr['max']) {
            throw new DomainException(app\i18n('Value out of range'));
        }
    }

    return $val;
}

/**
 * Frontend
 */
function frontend(array $data, array $attr): string
{
    $val = array_key_exists($attr['id'], $data) && $data[$attr['id']] !== null ? cast($data[$attr['id']], $attr) : null;
    $attr['opt'] = opt($data, $attr);
    $attr['html'] = html($attr);
    $label = ['for' => $attr['html']['id']];
    $error = '';

    if ($attr['multiple']) {
        $attr['html']['name'] .= '[]';
        $attr['html']['multiple'] = true;
    }

    if ($attr['required'] && !ignorable($data, $attr)) {
        $attr['html']['required'] = true;
        $label['data-required'] = true;
    }

    if ($attr['unique']) {
        $label['data-unique'] = true;
    }

    if (!empty($data['_error'][$attr['id']])) {
        $attr['html']['class'] = (!empty($attr['html']['class']) ? $attr['html']['class'] . ' ' : '') . 'invalid';
        $error = app\html('div', ['class' => 'error'], $data['_error'][$attr['id']]);
    }

    return app\html('label', $label, $attr['name']) . $attr['frontend']($val, $attr) . $error;
}

/**
 * Filter
 */
function filter(array $data, array $attr): string
{
    if ($attr['type'] === 'password') {
        return '';
    }

    $val = array_key_exists($attr['id'], $data) && $data[$attr['id']] !== null ? cast($data[$attr['id']], $attr) : null;
    $attr['opt'] = opt($data, array_replace($attr, ['opt' => $attr['filteropt']]));
    $attr['html'] = html($attr, 'filter');

    return app\html('label', ['for' => $attr['html']['id']], $attr['name']) . $attr['filter']($val, $attr);
}

/**
 * Viewer
 */
function viewer(array $data, array $attr): string
{
    $val = $data[$attr['id']] ?? null;

    if ($attr['type'] === 'password' || $val === null || $val === '') {
        return '';
    }

    $attr['opt'] = opt($data, $attr);

    if ($attr['viewer']) {
        return $attr['viewer']($val, $attr);
    }

    return app\enc((string) $val);
}

/**
 * Option
 */
function opt(array $data, array $attr): callable
{
    if ($attr['opt'] && strpos($attr['opt'], '\\') !== false) {
        return function () use ($data, $attr): array {
            return $attr['opt']($data, $attr);
        };
    }

    if ($attr['opt']) {
        return function () use ($attr): array {
            return app\cfg('opt', $attr['opt']);
        };
    }

    return function (): array {
        return [];
    };
}

/**
 * Cast to appropriate php type
 *
 * @return mixed
 */
function cast($val, array $attr)
{
    if ($attr['nullable'] && ($val === null || $val === '')) {
        return null;
    }

    if ($attr['backend'] === 'json') {
        return is_array($val) || $val && ($val = json_decode($val, true)) ? $val : [];
    }

    if ($attr['multiple']) {
        return is_array($val) || $val && ($val = explode(',', trim((string) $val, '{}'))) ? arr\map(__FUNCTION__, $val, ['multiple' => false] + $attr) : [];
    }

    if ($attr['backend'] === 'bool') {
        return (bool) $val;
    }

    if ($attr['backend'] === 'int') {
        return (int) $val;
    }

    if ($attr['backend'] === 'decimal') {
        return (float) $val;
    }

    return (string) $val;
}

/**
 * Check wheter attribute can be ignored
 */
function ignorable(array $data, array $attr): bool
{
    return in_array($attr['type'], ['password', 'upload']) && ($attr['virtual'] || !empty($data['_old'][$attr['id']]));
}

/**
 * Returns base HTML config for given attribute
 */
function html(array $attr, string $key = 'data'): array
{
    $minmax = in_array($attr['backend'], ['json', 'text', 'varchar']) ? ['minlength', 'maxlength'] : ['min', 'max'];
    $html = ['id' => $key . '-' . $attr['id'], 'name' => $key . '[' . $attr['id'] . ']', 'data-type' => $attr['type']];

    if ($attr['min'] > 0) {
        $html[$minmax[0]] = $attr['min'];
    }

    if ($attr['max'] > 0) {
        $html[$minmax[1]] = $attr['max'];
    }

    return $html;
}

/**
 * Converts a date, time or datetime from one to another format
 */
function datetime(?string $val, string $in, string $out): string
{
    $val = $val ? date_create_from_format($in, $val) : date_create();

    return $val && ($val = date_format($val, $out)) ? $val : '';
}
