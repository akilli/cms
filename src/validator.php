<?php
declare(strict_types = 1);

namespace qnd;

use DomainException;

/**
 * Validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
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

    validator_uniq($attr, $data);
    validator_required($attr, $data);
    validator_boundary($attr, $data);

    return $data;
}

/**
 * Required validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_required(array $attr, array $data): array
{
    if ($attr['required'] && ($data[$attr['id']] === null || $data[$attr['id']] === '') && !ignorable($attr, $data)) {
        throw new DomainException(_('%s is required', $attr['name']));
    }

    return $data;
}

/**
 * Unique validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_uniq(array $attr, array $data): array
{
    if ($attr['uniq'] && $data[$attr['id']] !== ($data['_old'][$attr['id']] ?? null) && size($data['_entity']['id'], [[$attr['id'], $data[$attr['id']]]])) {
        throw new DomainException(_('%s must be unique', $attr['name']));
    }

    return $data;
}

/**
 * Boundary validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_boundary(array $attr, array $data): array
{
    $vals = $attr['multiple'] && is_array($data[$attr['id']]) ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if (in_array($attr['backend'], ['json', 'text', 'varchar'])) {
            $val = strlen($val);
        }

        if ($attr['minval'] > 0 && $val < $attr['minval'] || $attr['maxval'] > 0 && $val > $attr['maxval']) {
            throw new DomainException(_('Value out of range'));
        }
    }

    return $data;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * Option validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_text(array $attr, array $data): array
{
    $data[$attr['id']] = trim((string) filter_var($data[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return $data;
}

/**
 * ID validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
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
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
 *
 * @return array
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
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_file(array $attr, array $data): array
{
    $file = request('files')[$attr['id']] ?? null;

    if ($file) {
        if (!in_array($attr['type'], data('file', pathinfo($file['name'], PATHINFO_EXTENSION)) ?? [])) {
            throw new DomainException(_('Invalid file %s', $file['name']));
        }

        if (($data['_old'][$attr['id']] ?? null) === $file['name']) {
            $data[$attr['id']] = $file['name'];
        } else {
            $data[$attr['id']] = filter_file($file['name'], path('media'));
        }
    }

    return $data;
}
