<?php
declare(strict_types = 1);

namespace cms;

use DomainException;

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

    if ($attr['uniq'] && $data[$attr['id']] !== ($data['_old'][$attr['id']] ?? null) && size($data['_entity']['id'], [[$attr['id'], $data[$attr['id']]]])) {
        throw new DomainException(_('%s must be unique', $attr['name']));
    }

    if ($attr['required'] && ($data[$attr['id']] === null || $data[$attr['id']] === '') && !ignorable($attr, $data)) {
        throw new DomainException(_('%s is required', $attr['name']));
    }

    $vals = $attr['multiple'] && is_array($data[$attr['id']]) ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if ($attr['min'] > 0 && $val < $attr['min']
            || $attr['max'] > 0 && $val > $attr['max']
            || $attr['minlength'] > 0 && strlen($val) < $attr['minlength']
            || $attr['maxlength'] > 0 && strlen($val) > $attr['maxlength']
        ) {
            throw new DomainException(_('Value out of range'));
        }
    }

    return $data;
}

/**
 * Option validator
 *
 * @throws DomainException
 */
function validator_opt(array $attr, array $data): array
{
    if (!empty($data[$attr['id']]) || is_scalar($data[$attr['id']]) && !is_string($data[$attr['id']])) {
        foreach ((array) $data[$attr['id']] as $v) {
            if (!isset($attr['opt'][$v])) {
                throw new DomainException(_('Invalid option for attribute %s', $attr['name']));
            }
        }
    }

    return $data;
}

/**
 * Page validator
 *
 * @throws DomainException
 */
function validator_page(array $attr, array $data): array
{
    $old = $data['_old']['id'] ?? null;

    if ($data[$attr['id']] && $old && in_array($old, one('page', [['id', $data[$attr['id']]]])['path'])) {
        throw new DomainException(_('Cannot assign the page itself or a child page as parent'));
    }

    return $data;
}

/**
 * Text validator
 */
function validator_text(array $attr, array $data): array
{
    $data[$attr['id']] = trim((string) filter_var($data[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return $data;
}

/**
 * ID validator
 */
function validator_id(array $attr, array $data): array
{
    $data = validator_text($attr, $data);
    $data[$attr['id']] = filter_id($data[$attr['id']]);

    return $data;
}

/**
 * Email validator
 *
 * @throws DomainException
 */
function validator_email(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_EMAIL))) {
        throw new DomainException(_('Invalid email'));
    }

    return $data;
}

/**
 * URL validator
 *
 * @throws DomainException
 */
function validator_url(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_URL))) {
        throw new DomainException(_('Invalid URL'));
    }

    return $data;
}

/**
 * JSON validator
 *
 * @throws DomainException
 */
function validator_json(array $attr, array $data): array
{
    if ($data[$attr['id']] && json_decode($data[$attr['id']], true) === null) {
        throw new DomainException(_('Invalid JSON notation'));
    }

    if (!$data[$attr['id']]) {
        $data[$attr['id']] = '[]';
    }

    return $data;
}

/**
 * Rich text validator
 *
 * @return array
 */
function validator_rte(array $attr, array $data): array
{
    $data[$attr['id']] = filter_html($data[$attr['id']]);

    return $data;
}

/**
 * Date validator
 *
 * @throws DomainException
 */
function validator_date(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], DATE['f'], DATE['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Datetime validator
 *
 * @throws DomainException
 */
function validator_datetime(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], DATETIME['f'], DATETIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Time validator
 *
 * @throws DomainException
 */
function validator_time(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], TIME['f'], TIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * File validator
 *
 * @throws DomainException
 */
function validator_file(array $attr, array $data): array
{
    $file = request('file')[$attr['id']] ?? null;

    if ($file) {
        if (!in_array($attr['type'], cfg('file', pathinfo($file['name'], PATHINFO_EXTENSION)) ?? [])) {
            throw new DomainException(_('Invalid file %s', $file['name']));
        }

        if (($data['_old'][$attr['id']] ?? null) === $file['name']) {
            $data[$attr['id']] = $file['name'];
        } else {
            $data[$attr['id']] = filter_file($file['name'], path('data'));
        }
    }

    return $data;
}
