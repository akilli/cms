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
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);
    $item[$code] = trim((string) filter_var($item[$code], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

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
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_EMAIL)) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid email');

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
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_URL)) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid URL');

        return false;
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
    $code = $attr['id'];
    $item[$code] = null;
    $file = files('data')[$item['_id']][$code] ?? null;

    // Delete old file
    if (!empty($item['_old'][$code]) && ($file || !empty($item['_reset'][$code])) && !media_delete($item['_old'][$code])) {
        $item['_error'][$code] = _('Could not delete old file %s', $item['_old'][$code]);

        return false;
    }

    // No upload
    if (!$file) {
        return true;
    }

    // Invalid file
    if (empty(file_ext($attr['type'])[$file['extension']])) {
        $item['_error'][$code] = _('Invalid file');

        return false;
    }

    $value = generator_file($file['name'], path('media'));

    // Upload failed
    if (!file_upload($file['tmp_name'], path('media', $value))) {
        $item['_error'][$code] = _('File upload failed');

        return false;
    }

    $item[$code] = $value;

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
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);
    $format = $attr['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';

    if (!empty($item[$code])) {
        if ($datetime = date_format(date_create($item[$code]), $format)) {
            $item[$code] = $datetime;
        } else {
            $item[$code] = null;
            $item['_error'][$code] = _('Invalid date');

            return false;
        }
    }

    return true;
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
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);

    return true;
}

/**
 * Rich text editor validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_rte(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);

    if ($item[$code]) {
        $item[$code] = filter_html($item[$code]);
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
function validator_option(array $attr, array & $item): bool
{
    $attr['options'] = option($attr, $item);
    $code = $attr['id'];
    $item[$code] = cast($attr, $item[$code] ?? null);

    if (is_array($item[$code])) {
        $item[$code] = array_filter(
            $item[$code],
            function ($value) {
                return !empty($value) || !is_string($value);
            }
        );
    }

    if (!empty($item[$code]) || is_scalar($item[$code]) && !is_string($item[$code])) {
        foreach ((array) $item[$code] as $v) {
            if (!isset($attr['options'][$v])) {
                $item[$code] = null;
                $item['_error'][$code] = _('Invalid option for attribute %s', $code);

                return false;
            }
        }
    } elseif (!empty($attr['nullable'])) {
        $item[$code] = null;
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
    $code = $attr['id'];

    if (!empty($item[$code]) && strpos($item[$code], ':') > 0) {
        $parts = explode(':', $item[$code]);
        $item['root_id'] = cast($item['_meta']['attributes']['root_id'], $parts[0]);
        $item['basis'] = cast($item['_meta']['attributes']['id'], $parts[1]);
    } else {
        $item['_error'][$code] = _('%s is a mandatory field', $attr['name']);

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
    $code = $attr['id'];

    if (!empty($item[$code]) && !is_callable($item[$code])) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid callback');

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
    $code = $attr['id'];

    if (!empty($item[$code]) && json_decode($item[$code], true) === null) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid JSON notation');

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
        || validator_unique($attr, $item) && validator_required($attr, $item);
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
    $code = $attr['id'];

    if (!empty($attr['required']) && empty($item[$code]) && !option($attr, $item) && !ignorable($attr, $item)) {
        $item['_error'][$code] = _('%s is a mandatory field', $attr['name']);

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
function validator_unique(array $attr, array & $item): bool
{
    static $data = [];

    if (empty($attr['is_unique'])) {
        return true;
    }

    $code = $attr['id'];
    $entity = $item['_meta']['id'];

    // Existing values
    if (!isset($data[$entity])) {
        $data[$entity] = model_load($entity, null, 'unique');

        if ($entity === 'entity' && ($ids = array_keys(data('meta')))
            || $entity === 'attribute' && ($ids = array_keys(data('meta', 'content')['attributes']))
        ) {
            $ids = array_combine($ids, $ids);
            $data[$entity]['id'] = !empty($data[$entity]['id']) ? array_replace($data[$entity]['id'], $ids) : $ids;
        }
    }

    if (!isset($data[$entity][$code])) {
        $data[$entity][$code] = [];
    }

    // Generate unique value
    if ($attr['unique_callback']) {
        if (!empty($item[$code])) {
            $base = $item[$code];
        } elseif (!empty($attr['unique_base']) && !empty($item[$attr['unique_base']])) {
            $base = $item[$attr['unique_base']];
        } else {
            $base = null;
        }

        $item[$code] = $attr['unique_callback']($base, $data[$entity][$code], $item['_id']);

        return true;
    } elseif (!empty($attr['nullable']) && $item[$code] == '') {
        $item[$code] = null;
        return true;
    } elseif (!empty($item[$code])
        && (array_search($item[$code], $data[$entity][$code]) === $item['_id']
            || !in_array($item[$code], $data[$entity][$code])
        )
    ) {
        // Provided value is unique
        $data[$entity][$code][$item['_id']] = $item[$code];

        return true;
    }

    $item['_error'][$code] = _('%s must be unique', $attr['name']);

    return false;
}
