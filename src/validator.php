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
    if ($attr['generator'] === 'auto') {
        return true;
    }

    $valid = true;
    $callback = fqn('validator_' . $attr['type']);

    if (is_callable($callback)) {
        $valid = $callback($attr, $item);
    } else {
        // Temporary
        switch ($attr['frontend']) {
            case 'checkbox':
            case 'radio':
            case 'select':
                $valid = validator_option($attr, $item);
                break;
            case 'password':
            case 'textarea':
                $valid = validator_text($attr, $item);
                break;
            case 'number':
            case 'range':
                $valid = validator_int($attr, $item);
                break;
            case 'file':
                $valid = validator_file($attr, $item);
                break;
        }
    }

    return $valid && validator_default($attr, $item);
}

/**
 * Default validator
 *
 * Skips attributes that need no validation or are uneditable (unless required and new)
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_default(array $attr, array & $item): bool
{
    return !data_action('edit', $attr) && (empty($attr['required']) || !empty($item['_old']))
        || validator_uniq($attr, $item) && validator_required($attr, $item) && validator_boundary($attr, $item);
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
    if (!empty($attr['required']) && empty($item[$attr['id']]) && !option($attr) && !ignorable($attr, $item)) {
        $item['_error'][$attr['id']] = _('%s is a mandatory field', $attr['name']);
        return false;
    }

    return true;
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
    static $data = [];

    if (empty($attr['uniq'])) {
        return true;
    }

    $eId = $item['_entity']['id'];

    // Existing values
    if (!isset($data[$eId])) {
        $data[$eId] = entity_load($eId, [], 'uniq');

        if ($eId === 'entity' && ($ids = array_keys(data('entity')))
            || $eId === 'attribute' && ($ids = array_keys(data('entity', 'content')['attributes']))
        ) {
            $ids = array_combine($ids, $ids);
            $data[$eId]['id'] = !empty($data[$eId]['id']) ? array_replace($data[$eId]['id'], $ids) : $ids;
        }
    }

    if (!isset($data[$eId][$attr['id']])) {
        $data[$eId][$attr['id']] = [];
    }

    // Generate unique value
    if ($attr['generator'] === 'id') {
        $base = data_action('edit', $attr) ? $attr['id'] : 'name';

        if (empty($item[$base]) && !$item[$base]) {
            return false;
        }

        $item[$attr['id']] = generator_id($item[$base], $data[$eId][$attr['id']], $item['_id']);
        return true;
    }

    if (!empty($attr['nullable']) && $item[$attr['id']] == '') {
        $item[$attr['id']] = null;
        return true;
    }

    if (!empty($item[$attr['id']])
        && (array_search($item[$attr['id']], $data[$eId][$attr['id']]) === $item['_id']
            || !in_array($item[$attr['id']], $data[$eId][$attr['id']])
        )
    ) {
        // Provided value is unique
        $data[$eId][$attr['id']][$item['_id']] = $item[$attr['id']];
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
    $value = in_array($attr['backend'], ['json', 'text', 'varchar']) ? strlen($item[$attr['id']]) : $item[$attr['id']];

    return (!isset($attr['min']) || $attr['min'] <= $value) && (!isset($attr['max']) || $attr['max'] >= $value);
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_option(array $attr, array & $item): bool
{
    $attr['options'] = option($attr);
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

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
            if (!isset($attr['options'][$v])) {
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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);
    $item[$attr['id']] = trim((string) filter_var($item[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return true;
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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if ($item[$attr['id']] && !$item[$attr['id']] = filter_var($item[$attr['id']], FILTER_VALIDATE_EMAIL)) {
        $item[$attr['id']] = null;
        $item['_error'][$attr['id']] = _('Invalid email');

        return false;
    }

    return true;
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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if ($item[$attr['id']] && !$item[$attr['id']] = filter_var($item[$attr['id']], FILTER_VALIDATE_URL)) {
        $item[$attr['id']] = null;
        $item['_error'][$attr['id']] = _('Invalid URL');

        return false;
    }

    return true;
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
    if (isset($item[$attr['id']]) && trim($item[$attr['id']]) === '') {
        $item[$attr['id']] = null;
    }

    if (!empty($item[$attr['id']]) && json_decode($item[$attr['id']], true) === null) {
        $item[$attr['id']] = null;
        $item['_error'][$attr['id']] = _('Invalid JSON notation');

        return false;
    }

    return true;
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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if ($item[$attr['id']]) {
        $item[$attr['id']] = filter_html($item[$attr['id']]);
    }

    return true;
}

/**
 * Int validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_int(array $attr, array & $item): bool
{
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if (!empty($item[$attr['id']])) {
        $value = date_create_from_format('Y-m-d', $item[$attr['id']]);

        if ($value && ($value = date_format($value, 'Y-m-d'))) {
            $item[$attr['id']] = $value;
        } else {
            $item[$attr['id']] = null;
            $item['_error'][$attr['id']] = _('Invalid date');

            return false;
        }
    }

    return true;
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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if (!empty($item[$attr['id']])) {
        $value = date_create_from_format('Y-m-d\TH:i', $item[$attr['id']]);

        if ($value && ($value = date_format($value, 'Y-m-d H:i:s'))) {
            $item[$attr['id']] = $value;
        } else {
            $item[$attr['id']] = null;
            $item['_error'][$attr['id']] = _('Invalid datetime');

            return false;
        }
    }

    return true;
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
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    if (!empty($item[$attr['id']])) {
        $value = date_create_from_format('H:i', $item[$attr['id']]);

        if ($value && ($value = date_format($value, 'H:i:s'))) {
            $item[$attr['id']] = $value;
        } else {
            $item[$attr['id']] = null;
            $item['_error'][$attr['id']] = _('Invalid time');

            return false;
        }
    }

    return true;
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
    $item[$attr['id']] = null;
    $file = files('data')[$item['_id']][$attr['id']] ?? null;

    // Delete old file
    if (!empty($item['_old'][$attr['id']])
        && ($file || !empty($item['_reset'][$attr['id']]))
        && !media_delete($item['_old'][$attr['id']])
    ) {
        $item['_error'][$attr['id']] = _('Could not delete old file %s', $item['_old'][$attr['id']]);

        return false;
    }

    // No upload
    if (!$file) {
        return true;
    }

    // Invalid file
    if (empty(file_ext($attr['type'])[$file['extension']])) {
        $item['_error'][$attr['id']] = _('Invalid file');

        return false;
    }

    $value = generator_file($file['name'], path('media'));

    // Upload failed
    if (!file_upload($file['tmp_name'], path('media', $value))) {
        $item['_error'][$attr['id']] = _('File upload failed');

        return false;
    }

    $item[$attr['id']] = $value;

    return true;
}
