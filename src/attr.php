<?php
declare(strict_types = 1);

namespace attr;

use app;
use arr;
use ent;
use html;
use DomainException;

/**
 * Filter
 *
 * @throws DomainException
 */
function filter(array $attr, array $data): array
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    if ($attr['nullable'] && $data[$attr['id']] === null) {
        return $data;
    }

    $set = $data[$attr['id']] === null || $data[$attr['id']] === '';
    $attr['opt'] = opt($attr, $data);

    if ($attr['filter']) {
        $data[$attr['id']] = $attr['filter']($attr, $data[$attr['id']]);
    }

    if ($set && $attr['pattern']) {
        $subj = $attr['multiple'] ? implode("\n", $data[$attr['id']]) : (string) $data[$attr['id']];

        if (!preg_match('#^' . str_replace('#', '\#', $attr['pattern']) . '$#', $subj)) {
            throw new DomainException(app\i18n('Value contains invalid characters'));
        }
    }

    $crit = [[$attr['id'], $data[$attr['id']]]];

    if ($data['_old']) {
        $crit[] = ['id', $data['_old']['id'], APP['crit']['!=']];
    }

    if ($attr['unique'] && ent\size($data['_ent']['id'], $crit)) {
        throw new DomainException(app\i18n('Value must be unique'));
    }

    if ($set && $attr['required']) {
        throw new DomainException(app\i18n('Value is required'));
    }

    $vals = $attr['multiple'] ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if ($attr['min'] > 0 && $val < $attr['min']
            || $attr['max'] > 0 && $val > $attr['max']
            || $attr['minlength'] > 0 && mb_strlen($val) < $attr['minlength']
            || $attr['maxlength'] > 0 && mb_strlen($val) > $attr['maxlength']
        ) {
            throw new DomainException(app\i18n('Value out of range'));
        }
    }

    return $data;
}

/**
 * Frontend
 */
function frontend(array $attr, array $data): string
{
    $data[$attr['id']] = cast(['nullable' => false] + $attr, $data[$attr['id']] ?? $attr['val']);
    $attr['opt'] = opt($attr, $data);
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

    if ($attr['required'] && !ignorable($attr, $data)) {
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
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
        $error = html\tag('div', ['class' => 'error'], $data['_error'][$attr['id']]);
    }

    $out = $attr['frontend']($attr, $data[$attr['id']]);

    return html\tag('label', $label, $attr['name']) . $out . $error;
}

/**
 * Viewer
 */
function viewer(array $attr, array $data): string
{
    if (!isset($data[$attr['id']]) || $data[$attr['id']] === '') {
        return '';
    }

    $attr['opt'] = opt($attr, $data);

    if ($attr['viewer']) {
        return $attr['viewer']($attr, $data[$attr['id']]);
    }

    return app\enc((string) $data[$attr['id']]);
}

/**
 * Option
 */
function opt(array $attr, array $data): array
{
    if ($attr['opt'] && strpos($attr['opt'], '\\') !== false) {
        return $attr['opt']($attr, $data);
    }

    if ($attr['opt']) {
        return app\cfg('opt', $attr['opt']);
    }

    return [];
}

/**
 * Cast to appropriate php type
 *
 * @return mixed
 */
function cast(array $attr, $val)
{
    if ($attr['nullable'] && ($val === null || $val === '')) {
        return null;
    }

    if ($attr['backend'] === 'json') {
        return is_array($val) || $val && ($val = json_decode($val, true)) ? $val : [];
    }

    if ($attr['multiple']) {
        return is_array($val) ? arr\map(__FUNCTION__, $val, ['multiple' => false] + $attr) : [];
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
function ignorable(array $attr, array $data): bool
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
