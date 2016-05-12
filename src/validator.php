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
    $callback = fqn('validator_' . $attr['type']);

    if (in_array($attr['frontend'], ['checkbox', 'radio', 'select'])) {
        return validator_option($attr, $item) && validator_default($attr, $item);
    }

    return (!is_callable($callback) || $callback($attr, $item)) && validator_default($attr, $item);
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
    return $attr['generator'] === 'auto'
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
        $data[$entity] = entity_load($entity, [], 'unambiguous');

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
    if ($attr['generator'] === 'id') {
        $base = meta_action('edit', $attr) ? $attr['id'] : 'name';

        if (empty($item[$base]) && !$item[$base]) {
            return false;
        }

        $item[$attr['id']] = generator_id($item[$base], $data[$entity][$attr['id']], $item['_id']);
        return true;
    }

    if (!empty($attr['nullable']) && $item[$attr['id']] == '') {
        $item[$attr['id']] = null;
        return true;
    }

    if (!empty($item[$attr['id']])
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
 * Password validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_password(array $attr, array & $item): bool
{
    return validator_text($attr, $item);
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
 * Index validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_index(array $attr, array & $item): bool
{
    return validator_text($attr, $item);
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
 * Textarea validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_textarea(array $attr, array & $item): bool
{
    return validator_text($attr, $item);
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

    return (!isset($attr['min']) || $attr['min'] <= $item[$attr['id']])
        && (!isset($attr['max']) || $attr['max'] >= $item[$attr['id']]);
}

/**
 * Decimal validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_decimal(array $attr, array & $item): bool
{
    return validator_int($attr, $item);
}

/**
 * Range validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_range(array $attr, array & $item): bool
{
    return validator_int($attr, $item);
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
 * Date validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_date(array $attr, array & $item): bool
{
    return validator_datetime($attr, $item);
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
 * Audio validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_audio(array $attr, array & $item): bool
{
    return validator_file($attr, $item);
}

/**
 * Embed validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_embed(array $attr, array & $item): bool
{
    return validator_file($attr, $item);
}

/**
 * Image validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_image(array $attr, array & $item): bool
{
    return validator_file($attr, $item);
}

/**
 * Video validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_video(array $attr, array & $item): bool
{
    return validator_file($attr, $item);
}
