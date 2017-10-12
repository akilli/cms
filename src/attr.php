<?php
declare(strict_types = 1);

namespace attr;

use function app\i18n;
use function filter\enc;
use function html\tag;
use ent;
use opt;
use DomainException;

const ATTR = [
    'id' => null,
    'name' => null,
    'col' => null,
    'auto' => false,
    'sort' => 0,
    'type' => null,
    'backend' => null,
    'frontend' => null,
    'nullable' => false,
    'required' => false,
    'unique' => false,
    'multiple' => false,
    'searchable' => false,
    'opt' => [],
    'act' => [],
    'val' => null,
    'min' => 0,
    'max' => 0,
    'minlength' => 0,
    'maxlength' => 0,
    'html' => [],
    'validator' => null,
    'loader' => null,
    'editor' => null,
    'viewer' => null,
];
const DATE = ['b' => 'Y-m-d', 'f' => 'Y-m-d'];
const DATETIME = ['b' => 'Y-m-d H:i:s', 'f' => 'Y-m-d\TH:i'];
const TIME = ['b' => 'H:i:s', 'f' => 'H:i'];

/**
 * Validator
 *
 * @throws DomainException
 */
function validator(array $attr, array $data): array
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    if ($attr['nullable'] && $data[$attr['id']] === null) {
        return $data;
    }

    $attr['opt'] = opt($attr);

    if ($attr['validator']) {
        $data = $attr['validator']($attr, $data);
    }

    if ($attr['unique'] && $data[$attr['id']] !== ($data['_old'][$attr['id']] ?? null) && ent\size($data['_ent']['id'], [[$attr['id'], $data[$attr['id']]]])) {
        throw new DomainException(i18n('Value must be unique'));
    }

    if ($attr['required'] && ($data[$attr['id']] === null || $data[$attr['id']] === '')) {
        throw new DomainException(i18n('Value is required'));
    }

    $vals = $attr['multiple'] && is_array($data[$attr['id']]) ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if ($attr['min'] > 0 && $val < $attr['min']
            || $attr['max'] > 0 && $val > $attr['max']
            || $attr['minlength'] > 0 && strlen($val) < $attr['minlength']
            || $attr['maxlength'] > 0 && strlen($val) > $attr['maxlength']
        ) {
            throw new DomainException(i18n('Value out of range'));
        }
    }

    return $data;
}

/**
 * Loader
 *
 * @return mixed
 */
function loader(array $attr, array $data)
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    return $attr['loader'] ? $attr['loader']($attr, $data) : $data[$attr['id']];
}

/**
 * Editor
 */
function editor(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ?? $attr['val'];
    $attr['opt'] = opt($attr);
    $attr['html']['id'] =  'data-' . $attr['id'];
    $attr['html']['name'] =  'data[' . $attr['id'] . ']' . (!empty($attr['multiple']) ? '[]' : '');
    $attr['html']['data-type'] =  $attr['type'];
    $label = $attr['name'];
    $error = '';

    if ($attr['required'] && !ignorable($attr, $data)) {
        $attr['html']['required'] = true;
        $label .= ' ' . tag('em', ['class' => 'required'], i18n('Required'));
    }

    if ($attr['unique']) {
        $label .= ' ' . tag('em', ['class' => 'unique'], i18n('Unique'));
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

    if ($attr['multiple']) {
        $attr['html']['multiple'] = true;
    }

    if (!empty($data['_error'][$attr['id']])) {
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
        $error = tag('div', ['class' => 'error'], $data['_error'][$attr['id']]);
    }

    if ($attr['editor'] && ($html = $attr['editor']($attr, $data))) {
        return tag('label', ['for' => $attr['html']['id']], $label) . $html . $error;
    }

    return '';
}

/**
 * Viewer
 */
function viewer(array $attr, array $data): string
{
    $attr['opt'] = opt($attr);

    if ($attr['viewer']) {
        return $attr['viewer']($attr, $data);
    }

    return $data[$attr['id']] ? enc((string) $data[$attr['id']]) : (string) $data[$attr['id']];
}

/**
 * Option
 */
function opt(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return [i18n('No'), i18n('Yes')];
    }

    if ($attr['type'] === 'ent') {
        return opt\ent($attr['opt']);
    }

    if (is_string($attr['opt'])) {
        return $attr['opt']($attr);
    }

    return $attr['opt'];
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

    if ($attr['multiple'] && is_array($val)) {
        foreach ($val as $k => $v) {
            $val[$k] = cast($attr, $v);
        }

        return $val;
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
    return !empty($data['_old'][$attr['id']]) && in_array($attr['frontend'], ['file', 'password']);
}
