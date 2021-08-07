<?php
declare(strict_types=1);

namespace attr;

use DomainException;
use app;
use arr;
use entity;
use html;

/**
 * Frontend
 */
function frontend(array $data, array $attr): string
{
    $val = val($data, $attr);
    $attr['opt'] = opt($data, $attr);
    $attr['html'] = html($attr);
    $div = ['data-attr' => $attr['id'], 'data-type' => $attr['type']];

    if (in_array($attr['backend'], ['multiint', 'multitext'])) {
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

    if (!$frontend = $attr['frontend']($val, $attr)) {
        return '';
    }

    $out = html\element('label', ['for' => $attr['html']['id']], $attr['name']) . $frontend;

    if (!empty($data['_error'][$attr['id']])) {
        $div['data-invalid'] = true;
        $out .= html\element('div', ['class' => 'error'], implode('<br />', $data['_error'][$attr['id']]));
    }

    return html\element('div', $div, $out);
}

/**
 * Filter
 */
function filter(array $data, array $attr): string
{
    if ($attr['type'] === 'password') {
        return '';
    }

    $val = val($data, $attr);
    $attr['opt'] = opt($data, $attr);
    $attr['html'] = html($attr, 'filter');

    return html\element('label', ['for' => $attr['html']['id']], $attr['name']) . $attr['filter']($val, $attr);
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
    $vp = is_array($val) ? implode("\n", $val) : (string)$val;
    $set = set($val);

    if ($pattern && $set && !preg_match($pattern, $vp)) {
        throw new DomainException(app\i18n('Value contains invalid characters'));
    }

    if ($attr['required'] && !$set) {
        throw new DomainException(app\i18n('Value is required'));
    }

    $parent = $data['_entity']['parent_id'] ? app\cfg('entity', $data['_entity']['parent_id']) : null;
    $entityId = $parent && !empty($parent['attr'][$attr['id']]) ? $parent['id'] : $data['_entity']['id'];
    $crit = $data['_old'] ? [[$attr['id'], $val], ['id', $data['_old']['id'], APP['op']['!=']]] : [[$attr['id'], $val]];

    if ($attr['unique'] && entity\size($entityId, crit: $crit)) {
        throw new DomainException(app\i18n('Value must be unique'));
    }

    $vs = is_array($val) ? $val : [$val];
    $strlen = in_array($attr['backend'], ['json', 'multitext', 'text', 'varchar']);

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
    if (!$attr['viewer']) {
        return '';
    }

    $cfg = arr\replace(APP['viewer'], $cfg);
    $val = $attr['type'] !== 'password' && isset($data[$attr['id']]) ? $data[$attr['id']] : null;
    $attr['opt'] = opt($data, $attr);
    $html = set($val) ? $attr['viewer']($val, $attr) : '';

    if (!$cfg['wrap'] || !$html && !$cfg['empty']) {
        return $html;
    }

    $a = ['data-attr' => $attr['id'], 'data-type' => $attr['type']];

    if ($cfg['link'] && !preg_match('#<(a|audio|details|iframe|video)#', $html)) {
        $html = html\element('a', ['href' => $cfg['link']], $html);
    }

    if ($cfg['label']) {
        $html = html\element('label', [], $attr['name']) . $html;
    }

    if (in_array($attr['id'], ['name', 'title'])) {
        return html\element($cfg['subheading'] ? 'h3' : 'h2', $a, $html);
    }

    if ($attr['id'] === 'aside') {
        return html\element('aside', $a, $html);
    }

    if (preg_match('#<(audio|iframe|img|video)#', $html, $match)) {
        return html\element('figure', $a + (['class' => $match[1] === 'img' ? 'image' : $match[1]]), $html);
    }

    if (in_array($attr['type'], ['date', 'datetime', 'time'])) {
        return html\element('time', $a + ($val !== $html ? ['datetime' => $val] : []), $html);
    }

    return html\element('div', $a, $html);
}

/**
 * Option
 */
function opt(array $data, array $attr): callable
{
    return $attr['opt'] ? fn(): array => $attr['opt']($data, $attr) : fn(): array => [];
}

/**
 * Value
 */
function val(array $data, array $attr): mixed
{
    return array_key_exists($attr['id'], $data) && $data[$attr['id']] !== null ? cast($data[$attr['id']], $attr) : null;
}

/**
 * Cast to appropriate php type
 */
function cast(mixed $val, array $attr): mixed
{
    $map = function (callable $call, mixed $val): array {
        if (is_array($val) || ($val = trim((string)$val, '{}')) && ($val = explode(',', $val))) {
            return array_map($call, $val);
        }
        return [];
    };

    return match (true) {
        $attr['nullable'] && !set($val) => null,
        default => match ($attr['backend']) {
            'bool' => (bool)$val,
            'int', 'serial' => (int)$val,
            'decimal' => (float)$val,
            'multiint' => $map('intval', $val),
            'multitext' => $map('strval', $val),
            'json' => is_array($val) || $val && ($val = json_decode($val, true)) ? $val : [],
            default => (string)$val,
        },
    };
}

/**
 * Indicates if a value is set, i.e. is neither null nor an empty string
 */
function set(mixed $val): bool
{
    return $val !== null && $val !== '';
}

/**
 * Check wheter attribute can be ignored
 */
function ignorable(array $data, array $attr): bool
{
    return $attr['ignorable'] && !empty($data['_old'][$attr['id']]);
}

/**
 * Returns base HTML config for given attribute
 */
function html(array $attr, string $key = 'attr'): array
{
    $backends = ['json', 'multitext', 'text', 'varchar'];
    $minmax = in_array($attr['backend'], $backends) ? ['minlength', 'maxlength'] : ['min', 'max'];
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
