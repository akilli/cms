<?php
declare(strict_types = 1);

namespace attr;

use app;
use arr;
use ent;
use filter;
use html;
use opt;
use DomainException;

/**
 * Validator
 *
 * @throws DomainException
 */
function validator(array $data, array $attr): array
{
    $data[$attr['id']] = cast($data[$attr['id']] ?? null, $attr);

    if ($attr['nullable'] && $data[$attr['id']] === null) {
        return $data;
    }

    if ($attr['validator']) {
        $data[$attr['id']] = ('validator\\' . $attr['validator'])($data[$attr['id']], opt($attr));
    }

    if ($attr['unique'] && $data[$attr['id']] !== ($data['_old'][$attr['id']] ?? null) && ent\size($data['_ent']['id'], [[$attr['id'], $data[$attr['id']]]])) {
        throw new DomainException(app\i18n('Value must be unique'));
    }

    if ($attr['required'] && ($data[$attr['id']] === null || $data[$attr['id']] === '')) {
        throw new DomainException(app\i18n('Value is required'));
    }

    $vals = $attr['multiple'] ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if ($attr['min'] > 0 && $val < $attr['min']
            || $attr['max'] > 0 && $val > $attr['max']
            || $attr['minlength'] > 0 && strlen($val) < $attr['minlength']
            || $attr['maxlength'] > 0 && strlen($val) > $attr['maxlength']
        ) {
            throw new DomainException(app\i18n('Value out of range'));
        }
    }

    return $data;
}

/**
 * Frontend
 */
function frontend(array $data, array $attr): string
{
    $data[$attr['id']] = $data[$attr['id']] ?? $attr['val'] ?? cast(null, $attr);
    $html['id'] =  'data-' . $attr['id'];
    $html['name'] =  'data[' . $attr['id'] . ']';
    $html['data-type'] =  $attr['type'];
    $label = $attr['name'];
    $error = '';

    if ($attr['multiple']) {
        $html['name'] .= '[]';
        $html['multiple'] = true;
    }

    if ($attr['required'] && !ignorable($data, $attr)) {
        $html['required'] = true;
        $label .= ' ' . html\tag('em', ['class' => 'required'], app\i18n('Required'));
    }

    if ($attr['unique']) {
        $label .= ' ' . html\tag('em', ['class' => 'unique'], app\i18n('Unique'));
    }

    foreach ([['min', 'max'], ['minlength', 'maxlength']] as $edge) {
        if ($attr[$edge[0]] <= $attr[$edge[1]]) {
            if ($attr[$edge[0]] > 0) {
                $html[$edge[0]] = $attr[$edge[0]];
            }

            if ($attr[$edge[1]] > 0) {
                $html[$edge[1]] = $attr[$edge[1]];
            }
        }
    }

    if (!empty($data['_error'][$attr['id']])) {
        $html['class'] = empty($html['class']) ? 'invalid' : $html['class'] . ' invalid';
        $error = html\tag('div', ['class' => 'error'], $data['_error'][$attr['id']]);
    }

    if ($out = ('frontend\\' . $attr['frontend'])($html, $data[$attr['id']], opt($attr))) {
        return html\tag('label', ['for' => $html['id']], $label) . $out . $error;
    }

    return '';
}

/**
 * Viewer
 */
function viewer(array $data, array $attr): string
{
    if (!isset($data[$attr['id']]) || $data[$attr['id']] === '') {
        return '';
    }

    if ($attr['viewer']) {
        return ('viewer\\' . $attr['viewer'])($data[$attr['id']], opt($attr));
    }

    return filter\enc((string) $data[$attr['id']]);
}

/**
 * Option
 */
function opt(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return [app\i18n('No'), app\i18n('Yes')];
    }

    if ($attr['type'] === 'ent') {
        return opt\ent($attr['opt']);
    }

    if (is_string($attr['opt'])) {
        return ('opt\\' . $attr['opt'])($attr);
    }

    return $attr['opt'];
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
        return is_array($val) ? arr\map(__FUNCTION__, $val, $attr) : [];
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
    return !empty($data['_old'][$attr['id']]) && in_array($attr['frontend'], ['file', 'password']);
}
