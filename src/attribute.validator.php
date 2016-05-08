<?php
namespace qnd;

/**
 * Attribute validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validator(array $attr, array & $item): bool
{
    return (!$attr['validator'] || $attr['validator']($attr, $item)) && validator_default($attr, $item);
}

/**
 * String validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_string(array $attr, array & $item): bool
{
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);
    $item[$attr['id']] = trim((string) filter_var($item[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return (!isset($attr['min']) || $attr['min'] <= strlen($item[$attr['id']]))
        && (!isset($attr['max']) || $attr['max'] >= strlen($item[$attr['id']]));
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

    return (!isset($attr['min']) || $attr['min'] <= strlen($item[$attr['id']]))
        && (!isset($attr['max']) || $attr['max'] >= strlen($item[$attr['id']]));
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

    return (!isset($attr['min']) || $attr['min'] <= strlen($item[$attr['id']]))
        && (!isset($attr['max']) || $attr['max'] >= strlen($item[$attr['id']]));
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
    $format = $attr['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';

    if (!empty($item[$attr['id']])) {
        if ($datetime = date_format(date_create($item[$attr['id']]), $format)) {
            $item[$attr['id']] = $datetime;
        } else {
            $item[$attr['id']] = null;
            $item['_error'][$attr['id']] = _('Invalid date');

            return false;
        }
    }

    return (!isset($attr['min']) || $attr['min'] <= $item[$attr['id']])
        && (!isset($attr['max']) || $attr['max'] >= $item[$attr['id']]);
}

/**
 * Number validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_number(array $attr, array & $item): bool
{
    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);

    return (!isset($attr['min']) || $attr['min'] <= $item[$attr['id']])
        && (!isset($attr['max']) || $attr['max'] >= $item[$attr['id']]);
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

    return (!isset($attr['min']) || $attr['min'] <= strlen($item[$attr['id']]))
        && (!isset($attr['max']) || $attr['max'] >= strlen($item[$attr['id']]));
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
                $item['_error'][$attr['id']] = _('Invalid option for attribute %s', $attr['id']);

                return false;
            }
        }
    } elseif (!empty($attr['nullable'])) {
        $item[$attr['id']] = null;
    }

    return true;
}

/**
 * Menubasis validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_menubasis(array $attr, array & $item): bool
{
    if (!empty($item[$attr['id']]) && strpos($item[$attr['id']], ':') > 0) {
        $parts = explode(':', $item[$attr['id']]);
        $item['root_id'] = cast($item['_meta']['attributes']['root_id'], $parts[0]);
        $item['basis'] = cast($item['_meta']['attributes']['id'], $parts[1]);
    } else {
        $item['_error'][$attr['id']] = _('%s is a mandatory field', $attr['name']);

        return false;
    }

    return true;
}

/**
 * Callback validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_callback(array $attr, array & $item): bool
{
    if (!empty($item[$attr['id']]) && !is_callable($item[$attr['id']])) {
        $item[$attr['id']] = null;
        $item['_error'][$attr['id']] = _('Invalid callback');

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
    if (!empty($item[$attr['id']]) && json_decode($item[$attr['id']], true) === null) {
        $item[$attr['id']] = null;
        $item['_error'][$attr['id']] = _('Invalid JSON notation');

        return false;
    }

    return true;
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
    return !empty($attr['auto'])
        || !meta_action('edit', $attr) && (empty($attr['required']) || !empty($item['_old']))
        || validator_unambiguous($attr, $item) && validator_required($attr, $item);
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
 * Unambiguous validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_unambiguous(array $attr, array & $item): bool
{
    static $data = [];

    if (empty($attr['unambiguous'])) {
        return true;
    }

    $entity = $item['_meta']['id'];

    // Existing values
    if (!isset($data[$entity])) {
        $data[$entity] = entity_load($entity, null, 'unambiguous');

        if ($entity === 'entity' && ($ids = array_keys(data('meta')))
            || $entity === 'attribute' && ($ids = array_keys(data('meta', 'content')['attributes']))
        ) {
            $ids = array_combine($ids, $ids);
            $data[$entity]['id'] = !empty($data[$entity]['id']) ? array_replace($data[$entity]['id'], $ids) : $ids;
        }
    }

    if (!isset($data[$entity][$attr['id']])) {
        $data[$entity][$attr['id']] = [];
    }

    // Generate unambiguous value
    if ($attr['generator']) {
        if (!empty($item[$attr['id']])) {
            $base = $item[$attr['id']];
        } elseif (!empty($attr['generator_base']) && !empty($item[$attr['generator_base']])) {
            $base = $item[$attr['generator_base']];
        } else {
            $base = null;
        }

        $item[$attr['id']] = $attr['generator']($base, $data[$entity][$attr['id']], $item['_id']);

        return true;
    } elseif (!empty($attr['nullable']) && $item[$attr['id']] == '') {
        $item[$attr['id']] = null;
        return true;
    } elseif (!empty($item[$attr['id']])
        && (array_search($item[$attr['id']], $data[$entity][$attr['id']]) === $item['_id']
            || !in_array($item[$attr['id']], $data[$entity][$attr['id']])
        )
    ) {
        // Provided value is unambiguous
        $data[$entity][$attr['id']][$item['_id']] = $item[$attr['id']];

        return true;
    }

    $item['_error'][$attr['id']] = _('%s must be unambiguous', $attr['name']);

    return false;
}
