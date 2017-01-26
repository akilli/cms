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

    $item[$attr['uid']] = cast($attr, $item[$attr['uid']] ?? null);

    if ($item[$attr['uid']] === null && !empty($attr['nullable'])) {
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
    if (!$attr['required'] || $item[$attr['uid']] || $attr['opt'] || ignorable($attr, $item)) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('%s is a mandatory field', $attr['name']);

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
    if (empty($attr['uniq']) || !empty($attr['nullable']) && $item[$attr['uid']] === null) {
        return true;
    }

    $old = all($item['_entity']['uid'], [$attr['uid'] => $item[$attr['uid']]]);

    if (!$old || count($old) === 1 && !empty($old[$item['_id']])) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('%s must be unique', $attr['name']);

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
    $values = $attr['multiple'] && is_array($item[$attr['uid']]) ? $item[$attr['uid']] : [$item[$attr['uid']]];

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
 * Keyword validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_keyword(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']] || !in_array($item[$attr['uid']], data('sql', 'keyword'))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Reserved database keywords must not be used');
    $item[$attr['uid']] = null;

    return false;
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
    if (is_array($item[$attr['uid']])) {
        $item[$attr['uid']] = array_filter(
            $item[$attr['uid']],
            function ($value) {
                return !empty($value) || !is_string($value);
            }
        );
    }

    if (!empty($item[$attr['uid']]) || is_scalar($item[$attr['uid']]) && !is_string($item[$attr['uid']])) {
        foreach ((array) $item[$attr['uid']] as $v) {
            if (!isset($attr['opt'][$v])) {
                $item['_error'][$attr['uid']] = _('Invalid option for attribute %s', $attr['name']);
                $item[$attr['uid']] = null;

                return false;
            }
        }
    } elseif (!empty($attr['nullable'])) {
        $item[$attr['uid']] = null;
    }

    return true;
}

/**
 * Attribute UID validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_attr(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']]
        || !($eUid = array_search($item['entity_id'], array_filter(array_column(data('entity'), 'id', 'uid'))))
        || !($old = data('entity', $eUid)['attr'][$item[$attr['uid']]] ?? null)
        || !empty($item['id']) && $item['id'] === $old['id']
    ) {
        return validator_keyword($attr, $item);
    }

    $item['_error'][$attr['uid']] = _('UID is already in use');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Entity UID validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_entity(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']]
        || !($old = data('entity', $item[$attr['uid']]))
        || !empty($item['id']) && $item['id'] === $old['id']
    ) {
        return validator_keyword($attr, $item);
    }

    $item['_error'][$attr['uid']] = _('UID is already in use');
    $item[$attr['uid']] = null;

    return false;
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
    $item[$attr['uid']] = trim((string) filter_var($item[$attr['uid']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

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
    return (bool) preg_match('/#[a-f0-9]{6}/', $item[$attr['uid']]);
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
    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_var($item[$attr['uid']], FILTER_VALIDATE_EMAIL))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid email');
    $item[$attr['uid']] = null;

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
    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_var($item[$attr['uid']], FILTER_VALIDATE_URL))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid URL');
    $item[$attr['uid']] = null;

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
    if (!$item[$attr['uid']] && ($item[$attr['uid']] = '[]') || json_decode($item[$attr['uid']], true) !== null) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid JSON notation');
    $item[$attr['uid']] = null;

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
    $item[$attr['uid']] = filter_html($item[$attr['uid']]);

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

    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_date($item[$attr['uid']], $in, $out))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid value');
    $item[$attr['uid']] = null;

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

    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_date($item[$attr['uid']], $in, $out))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid value');
    $item[$attr['uid']] = null;

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

    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_date($item[$attr['uid']], $in, $out))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid value');
    $item[$attr['uid']] = null;

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
    $file = http_files('data')[$item['_id']][$attr['uid']] ?? null;

    if (!$file || ($ext = data('file', $file['ext'])) && in_array($attr['type'], $ext)) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid file %s', $file);

    return false;
}
