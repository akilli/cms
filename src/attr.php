<?php
declare(strict_types = 1);

namespace attr;

use app;
use arr;
use entity;
use str;
use DomainException;

/**
 * Frontend
 */
function frontend(array $data, array $attr): string
{
    $val = array_key_exists($attr['id'], $data) && $data[$attr['id']] !== null ? cast($data[$attr['id']], $attr) : null;
    $attr['opt'] = opt($data, $attr);
    $attr['html'] = html($attr);
    $div = ['data-attr' => $attr['id'], 'data-type' => $attr['type']];

    if (in_array($attr['backend'], ['int[]', 'text[]'])) {
        $attr['html']['name'] .= '[]';
        $attr['html']['multiple'] = true;
    }

    if ($attr['required'] && !ignorable($data, $attr)) {
        $attr['html']['required'] = true;
        $div['data-required'] = true;
    }

    if ($attr['unique']) {
        $div['data-unique'] = true;
    }

    $out = app\html('label', ['for' => $attr['html']['id']], $attr['name']) . $attr['frontend']($val, $attr);

    if (!empty($data['_error'][$attr['id']])) {
        $div['data-invalid'] = true;
        $out .= app\html('div', ['class' => 'error'], implode('<br />', $data['_error'][$attr['id']]));
    }

    return app\html('div', $div, $out);
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
    $attr['opt'] = opt($data, $attr);
    $attr['html'] = html($attr, 'filter');

    return app\html('label', ['for' => $attr['html']['id']], $attr['name']) . $attr['filter']($val, $attr);
}

/**
 * Validator
 *
 * @throws DomainException
 */
function validator(array $data, array $attr): mixed
{
    $val = $data[$attr['id']] ?? null;

    if ($attr['nullable'] && $val === null) {
        return null;
    }

    $attr['opt'] = opt($data, $attr);

    if ($attr['validator']) {
        $val = $attr['validator']($val, $attr);
    }

    $pattern = $attr['pattern'] ? '#^' . str_replace('#', '\#', $attr['pattern']) . '$#' : null;
    $vp = is_array($val) ? implode("\n", $val) : (string) $val;
    $vs = is_array($val) ? $val : [$val];
    $crit = $data['_old'] ? [[$attr['id'], $val], ['id', $data['_old']['id'], APP['op']['!=']]] : [[$attr['id'], $val]];
    $strlen = in_array($attr['backend'], ['json', 'text', 'text[]', 'varchar']);

    if ($pattern && $val !== null && $val !== '' && !preg_match($pattern, $vp)) {
        throw new DomainException(app\i18n('Value contains invalid characters'));
    } elseif ($attr['required'] && ($val === null || $val === '')) {
        throw new DomainException(app\i18n('Value is required'));
    } elseif ($attr['unique'] && entity\size($data['_entity']['id'], $crit)) {
        throw new DomainException(app\i18n('Value must be unique'));
    }

    foreach ($vs as $v) {
        $length = $strlen ? mb_strlen($v) : $v;

        if ($attr['min'] > 0 && $length < $attr['min'] || $attr['max'] > 0 && $length > $attr['max']) {
            throw new DomainException(app\i18n('Value out of range'));
        }
    }

    return $val;
}

/**
 * Viewer
 */
function viewer(array $data, array $attr, array $cfg = []): string
{
    $cfg = arr\replace(APP['viewer'], $cfg);
    $val = $attr['type'] !== 'password' && isset($data[$attr['id']]) ? $data[$attr['id']] : null;
    $isEmpty = $val === null || $val === '';

    if ($isEmpty && (!$cfg['wrap'] || !$cfg['empty'])) {
        return '';
    }

    $attr['opt'] = opt($data, $attr);
    $html = !$isEmpty && $attr['viewer'] ? $attr['viewer']($val, $attr) : str\enc((string) $val);

    if (!$cfg['wrap'] || !$html && !$cfg['empty']) {
        return $html;
    }

    $a = ['data-attr' => $attr['id'], 'data-type' => $attr['type']];

    if ($cfg['link'] && !preg_match('#<(a|audio|details|iframe|video) #', $html)) {
        $html = app\html('a', ['href' => $cfg['link']], $html);
    }

    if ($cfg['label']) {
        $html = app\html('label', [], $attr['name']) . $html;
    }

    if (in_array($attr['id'], ['name', 'title'])) {
        return app\html($cfg['h3'] ? 'h3' : 'h2', $a, $html);
    }

    if ($attr['id'] === 'aside') {
        return app\html('aside', $a, $html);
    }

    if (($attr['uploadable'] || in_array($attr['type'], ['entity_file', 'iframe'])) && preg_match('#<(audio|iframe|img|video)#', $html, $match)) {
        return app\html('figure', $a + (['class' => $match[1] === 'img' ? 'image' : $match[1]]), $html);
    }

    if (in_array($attr['type'], ['date', 'datetime', 'time'])) {
        return app\html('time', $a + ($val !== $html ? ['datetime' => $val] : []), $html);
    }

    return app\html('div', $a, $html);
}

/**
 * Option
 */
function opt(array $data, array $attr): callable
{
    if ($attr['opt'] && str_contains($attr['opt'], '\\')) {
        return fn(): array => $attr['opt']($data, $attr);
    }

    if ($attr['opt']) {
        return fn(): array => app\cfg('opt', $attr['opt']);
    }

    return fn(): array => [];
}

/**
 * Cast to appropriate php type
 */
function cast(mixed $val, array $attr): mixed
{
    if ($attr['nullable'] && ($val === null || $val === '')) {
        return null;
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

    if ($attr['backend'] === 'int[]') {
        return is_array($val) || ($val = trim((string) $val, '{}')) && ($val = explode(',', $val)) ? array_map('intval', $val) : [];
    }

    if ($attr['backend'] === 'text[]') {
        return is_array($val) || ($val = trim((string) $val, '{}')) && ($val = explode(',', $val)) ? array_map('strval', $val) : [];
    }

    if ($attr['backend'] === 'json') {
        return is_array($val) || $val && ($val = json_decode($val, true)) ? $val : [];
    }

    return (string) $val;
}

/**
 * Check wheter attribute can be ignored
 */
function ignorable(array $data, array $attr): bool
{
    return $attr['ignorable'] && ($attr['virtual'] || !empty($data['_old'][$attr['id']]));
}

/**
 * Returns base HTML config for given attribute
 */
function html(array $attr, string $key = 'attr'): array
{
    $minmax = in_array($attr['backend'], ['json', 'text', 'text[]', 'varchar']) ? ['minlength', 'maxlength'] : ['min', 'max'];
    $name = $key === 'attr' ? $attr['id'] : $key . '[' . $attr['id'] . ']';
    $html = ['id' => $key . '-' . $attr['id'], 'name' => $name, 'data-type' => $attr['type']];

    if ($attr['min'] > 0) {
        $html[$minmax[0]] = $attr['min'];
    }

    if ($attr['max'] > 0) {
        $html[$minmax[1]] = $attr['max'];
    }

    if ($attr['autocomplete']) {
        $html['autocomplete'] = $attr['autocomplete'];
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
