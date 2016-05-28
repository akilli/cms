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
    if (!data_action('edit', $attr)) {
        return true;
    }

    $item[$attr['id']] = cast($attr, $item[$attr['id']] ?? null);
    $attr['opt'] = opt($attr);
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
                $valid = validator_opt($attr, $item);
                break;
            case 'password':
            case 'textarea':
                $valid = validator_text($attr, $item);
                break;
            case 'date':
            case 'time':
                $valid = validator_datetime($attr, $item);
                break;
            case 'file':
                $valid = validator_file($attr, $item);
                break;
        }
    }

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
    if ($attr['required'] && empty($item[$attr['id']]) && !$attr['opt'] && !ignorable($attr, $item)) {
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
    if (empty($attr['uniq']) || !empty($attr['nullable']) && $item[$attr['id']] == null) {
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
    if ($attr['multiple']) {
        return true;
    }

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
 * Email validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_email(array $attr, array & $item): bool
{
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
    if ($item[$attr['id']]) {
        $item[$attr['id']] = filter_html($item[$attr['id']]);
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
    if ($attr['frontend'] === 'date') {
        $in = 'Y-m-d';
        $out = 'Y-m-d';
    } elseif ($attr['frontend'] === 'time') {
        $in = 'H:i';
        $out = 'H:i:s';
    } else {
        $in = 'Y-m-d\TH:i';
        $out = 'Y-m-d H:i:s';
    }

    if ($item[$attr['id']]) {
        if ($value = date_format(date_create_from_format($in, $item[$attr['id']]), $out)) {
            $item[$attr['id']] = $value;
        } else {
            $item[$attr['id']] = null;
            $item['_error'][$attr['id']] = _('Invalid value');

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
    $file = http_files('data')[$item['_id']][$attr['id']] ?? null;

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
    if (empty(config('ext.' . $attr['type'])[$file['extension']])) {
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
