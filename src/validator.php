<?php
namespace qnd;

/**
 * Validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator(array $attr, array & $item): bool
{
    if (!in_array('edit', $attr['actions'])) {
        return true;
    }

    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if ($item[$attr['id']] === null && !empty($attr['nullable'])) {
        return true;
    }

    $attr['opt'] = opt($attr);
    $valid = !$attr['validator'] || ($call = fqn('validator_' . $attr['validator'])) && $call($attr, $item);

    return $valid && validator_uniq($attr, $item) && validator_required($attr, $item) && validator_boundary($attr, $item);
}

/**
 * Required validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_required(array $attr, array & $item): bool
{
    if (!$attr['required'] || $item[$attr['id']] || $attr['opt'] || ignorable($attr, $item)) {
        return true;
    }

    $item['_error'][$attr['id']] = _('%s is a mandatory field', $attr['name']);

    return false;
}

/**
 * Unique validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_uniq(array $attr, array & $item): bool
{
    if (empty($attr['uniq']) || !empty($attr['nullable']) && $item[$attr['id']] === null) {
        return true;
    }

    $old = all($item['_entity']['id'], [$attr['id'] => $item[$attr['id']]]);

    if (!$old || count($old) === 1 && !empty($old[$item['_id']])) {
        return true;
    }

    $item['_error'][$attr['id']] = _('%s must be unique', $attr['name']);

    return false;
}

/**
 * Boundary validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_boundary(array $attr, array & $item): bool
{
    $values = $attr['multiple'] && is_array($item[$attr['id']]) ? $item[$attr['id']] : [$item[$attr['id']]];

    foreach ($values as $value) {
        if (in_array($attr['backend'], ['json', 'text', 'varchar'])) {
            $value = strlen($value);
        }

        if (isset($attr['minval']) && $value < $attr['minval'] || isset($attr['maxval']) && $value > $attr['maxval']) {
            return false;
        }
    }

    return true;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_opt(array $attr, array & $item): bool
{
    if (is_array($item[$attr['id']])) {
        $item[$attr['id']] = array_filter(
            $item[$attr['id']],
            function ($value) {
                return !empty($value) || !is_string($value);
            }
        );
    }

    if (!empty($item[$attr['id']]) || is_scalar($item[$attr['id']]) && !is_string($item[$attr['id']])) {
        foreach ((array) $item[$attr['id']] as $v) {
            if (!isset($attr['opt'][$v])) {
                $item[$attr['id']] = null;
                $item['_error'][$attr['id']] = _('Invalid option for attribute %s', $attr['name']);

                return false;
            }
        }
    } elseif (!empty($attr['nullable'])) {
        $item[$attr['id']] = null;
    }

    return true;
}

/**
 * Text validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_text(array $attr, array & $item): bool
{
    $item[$attr['id']] = trim((string) filter_var($item[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return true;
}

/**
 * Color validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_color(array $attr, array & $item): bool
{
    return (bool) preg_match('/#[a-f0-9]{6}/', $item[$attr['id']]);
}

/**
 * Email validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_email(array $attr, array & $item): bool
{
    if (!$item[$attr['id']] || ($item[$attr['id']] = filter_var($item[$attr['id']], FILTER_VALIDATE_EMAIL))) {
        return true;
    }

    $item[$attr['id']] = null;
    $item['_error'][$attr['id']] = _('Invalid email');

    return false;
}

/**
 * URL validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_url(array $attr, array & $item): bool
{
    if (!$item[$attr['id']] || ($item[$attr['id']] = filter_var($item[$attr['id']], FILTER_VALIDATE_URL))) {
        return true;
    }

    $item[$attr['id']] = null;
    $item['_error'][$attr['id']] = _('Invalid URL');

    return false;
}

/**
 * JSON validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_json(array $attr, array & $item): bool
{
    if (!$item[$attr['id']] && ($item[$attr['id']] = '[]') || json_decode($item[$attr['id']], true) !== null) {
        return true;
    }

    $item[$attr['id']] = null;
    $item['_error'][$attr['id']] = _('Invalid JSON notation');

    return false;
}

/**
 * Rich text validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_rte(array $attr, array & $item): bool
{
    $item[$attr['id']] = $item[$attr['id']] ? filter_html($item[$attr['id']]) : '';

    return true;
}

/**
 * Date validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_date(array $attr, array & $item): bool
{
    $in = data('format', 'date.frontend');
    $out = data('format', 'date.backend');

    if (!$item[$attr['id']] || ($item[$attr['id']] = filter_date($item[$attr['id']], $in, $out))) {
        return true;
    }

    $item[$attr['id']] = null;
    $item['_error'][$attr['id']] = _('Invalid value');

    return false;
}

/**
 * Datetime validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_datetime(array $attr, array & $item): bool
{
    $in = data('format', 'datetime.frontend');
    $out = data('format', 'datetime.backend');

    if (!$item[$attr['id']] || ($item[$attr['id']] = filter_date($item[$attr['id']], $in, $out))) {
        return true;
    }

    $item[$attr['id']] = null;
    $item['_error'][$attr['id']] = _('Invalid value');

    return false;
}

/**
 * Time validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_time(array $attr, array & $item): bool
{
    $in = data('format', 'time.frontend');
    $out = data('format', 'time.backend');

    if (!$item[$attr['id']] || ($item[$attr['id']] = filter_date($item[$attr['id']], $in, $out))) {
        return true;
    }

    $item[$attr['id']] = null;
    $item['_error'][$attr['id']] = _('Invalid value');

    return false;
}

/**
 * File validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_file(array $attr, array & $item): bool
{
    $file = http_files('data')[$item['_id']][$attr['id']] ?? null;

    if (!$file || ($ext = data('file', $file['ext'])) && in_array($attr['type'], $ext)) {
        return true;
    }

    $item['_error'][$attr['id']] = _('Invalid file %s', $file);

    return false;
}
