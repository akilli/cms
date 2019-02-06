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
    $val = cast($data[$attr['id']] ?? null, $attr);

    if ($attr['nullable'] && $val === null) {
        return $val;
    }

    $set = $val !== null && $val !== '';
    $pattern = $attr['pattern'] ? '#^' . str_replace('#', '\#', $attr['pattern']) . '$#' : null;
    $attr['opt'] = opt($data, $attr);

    if ($attr['validator']) {
        $val = $attr['validator']($val, $attr);
    }

    if ($set && $pattern && !preg_match($pattern, $attr['multiple'] ? implode("\n", $val) : (string) $val)) {
        throw new DomainException(app\i18n('Value contains invalid characters'));
    }

    $crit = [[$attr['id'], $val]];

    if ($data['_old']) {
        $crit[] = ['id', $data['_old']['id'], APP['crit']['!=']];
    }

    if ($attr['unique'] && entity\size($data['_entity']['id'], $crit)) {
        throw new DomainException(app\i18n('Value must be unique'));
    }

    if (!$set && $attr['required']) {
        throw new DomainException(app\i18n('Value is required'));
    }

    $vs = $attr['multiple'] ? $val : [$val];

    foreach ($vs as $v) {
        if ($attr['min'] > 0 && $v < $attr['min']
            || $attr['max'] > 0 && $v > $attr['max']
            || $attr['minlength'] > 0 && mb_strlen($v) < $attr['minlength']
            || $attr['maxlength'] > 0 && mb_strlen($v) > $attr['maxlength']
        ) {
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
    $val = cast($data[$attr['id']] ?? $attr['val'], ['nullable' => false] + $attr);
    $attr['opt'] = opt($data, $attr);
    $attr['html']['id'] =  'data-' . $attr['id'];
    $attr['html']['name'] =  'data[' . $attr['id'] . ']';
    $attr['html']['data-type'] =  $attr['type'];
    $label = ['for' => $attr['html']['id']];
    $error = '';

    if ($attr['multiple']) {
        $attr['html']['name'] .= '[]';
        $attr['html']['multiple'] = true;
    }

    if ($attr['pattern']) {
        $attr['html']['pattern'] = $attr['pattern'];
    }

    if ($attr['required'] && !ignorable($data, $attr)) {
        $attr['html']['required'] = true;
        $label['data-required'] = true;
    }

    if ($attr['unique']) {
        $label['data-unique'] = true;
    }

    foreach ([['min', 'max'], ['minlength', 'maxlength']] as $edge) {
        if ($attr[$edge[0]] <= $attr[$edge[1]]) {
            if ($attr[$edge[0]] > 0) {
                $attr['html'][$edge[0]] = $attr[$edge[0]];
            }

            if ($attr[$edge[1]] > 0) {
                $attr['html'][$edge[1]] = $attr[$edge[1]];
            }
        }
    }

    if (!empty($data['_error'][$attr['id']])) {
        $attr['html']['class'] = (!empty($attr['html']['class']) ? $attr['html']['class'] . ' ' : '') . 'invalid';
        $error = app\html('div', ['class' => 'error'], $data['_error'][$attr['id']]);
    }

    $out = $attr['frontend']($val, $attr);

    return app\html('label', $label, $attr['name']) . $out . $error;
}

/**
 * Viewer
 */
function viewer(array $data, array $attr): string
{
    $val = $data[$attr['id']] ?? null;

    if ($val === null || $val === '') {
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
        return is_array($val) || $val && ($val = explode(',', trim($val, '{}'))) ? arr\map(__FUNCTION__, $val, ['multiple' => false] + $attr) : [];
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
    return $attr['ignorable'] && !empty($data['_old'][$attr['id']]);
}

/**
 * Converts a date, time or datetime from one to another format
 */
function datetime(?string $val, string $in, string $out): string
{
    $val = $val ? date_create_from_format($in, $val) : date_create();

    return $val && ($val = date_format($val, $out)) ? $val : '';
}
