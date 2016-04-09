<?php
namespace akilli;

/**
 * Set appropriate php type
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return mixed
 */
function attribute_cast(array $attr, $value)
{
    if ($value === null && !empty($attr['null'])) {
        return null;
    }

    if ($attr['backend'] === 'bool') {
        return boolval($value);
    }

    if ($attr['backend'] === 'int') {
        return intval($value);
    }

    if ($attr['backend'] === 'decimal') {
        return floatval($value);
    }

    if (!empty($attr['is_multiple']) && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = attribute_cast($attr, $v);
        }

        return $value;
    }

    return strval($value);
}

/**
 * Retrieve value
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function attribute_value(array $attr, array $item)
{
    return $item[$attr['id']] ?? $attr['default'] ?? null;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_ignore(array $attr, array $item): bool
{
    $code = $attr['id'];
    $mustEdit = empty($item[$code]) || $attr['action'] === 'edit' && !empty($item[$code]);

    return !empty($item['_old'])
        && empty($item['_reset'][$code])
        && $mustEdit
        && in_array($attr['frontend'], ['password', 'file']);
}

/**
 * Prepare attribute if edit action is allowed
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_editable(array & $attr, array $item): bool
{
    if (!meta_action('edit', $attr)) {
        return false;
    }

    if (!empty($item['_error'][$attr['id']])) {
        $attr['class'] = empty($attr['class']) ? [] : (array) $attr['class'];
        $attr['class'][] = 'invalid';
    }

    $attr['action'] = 'edit';

    return true;
}

/**
 * Prepare attribute if view action is allowed
 *
 * @param array $attr
 *
 * @return bool
 */
function attribute_viewable(array & $attr): bool
{
    return $attr['action'] === 'system' || $attr['action'] && meta_action($attr['action'], $attr);
}

/**
 * Load
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function attribute_load(array $attr, array $item)
{
    return attribute_cast($attr, $item[$attr['id']] ?? null);
}

/**
 * Load datetime
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function attribute_load_datetime(array $attr, array $item)
{
    $code = $attr['id'];

    return empty($item[$code]) || $item[$code] === '0000-00-00 00:00:00' ? null : $item[$code];
}

/**
 * Load JSON
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function attribute_load_json(array $attr, array $item): array
{
    $code = $attr['id'];

    if (empty($item[$code])) {
        return [];
    } elseif (is_array($item[$code])) {
        return $item[$code];
    }

    return json_decode($item[$code], true) ?: [];
}

/**
 * Save
 *
 * @return bool
 */
function attribute_save(): bool
{
    return true;
}

/**
 * Save password
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_save_password(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && is_string($item[$code])) {
        $item[$code] = password_hash($item[$code], PASSWORD_DEFAULT);
    }

    return true;
}

/**
 * Save multiple
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_save_multiple(array $attr, array & $item): bool
{
    $item[$attr['id']] = json_encode(array_filter(array_map('trim', (array) $item[$attr['id']])));

    return true;
}

/**
 * Save search index
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_save_index(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = '';

    foreach ($item['_meta']['attributes'] as $a) {
        if ($a['is_searchable'] || meta_action(['view', 'index', 'list'], $a)) {
            $item[$code] .= ' ' . str_replace("\n", '', strip_tags($item[$a['id']]));
        }
    }

    return true;
}

/**
 * Delete
 *
 * @return bool
 */
function attribute_delete(): bool
{
    return true;
}

/**
 * Delete file
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_delete_file(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && !media_delete($item[$code])) {
        $item['_error'][$code] = _('Could not delete old file %s', $item[$code]);

        return false;
    }

    return true;
}

/**
 * Validate
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate(array $attr, array & $item): bool
{
    // Skip attributes that need no validation or are uneditable (unless required and new)
    if (!empty($attr['auto']) || !meta_action('edit', $attr) && (empty($attr['is_required']) || !empty($item['_old']))) {
        return true;
    }

    return attribute_validate_unique($attr, $item) && attribute_validate_required($attr, $item);
}

/**
 * Validate string
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_string(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);
    $item[$code] = trim((string) filter_var($item[$code], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return attribute_validate($attr, $item);
}

/**
 * Validate email
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_email(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_EMAIL)) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid email');

        return false;
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate url
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_url(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_URL)) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid URL');

        return false;
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate file
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_file(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = null;
    $file = files('data')[$item['_id']][$code] ?? null;

    // Delete old file
    if (!empty($item['_old'][$code])
        && ($file || !empty($item['_reset'][$code]))
        && !media_delete($item['_old'][$code])
    ) {
        $item['_error'][$code] = _('Could not delete old file %s', $item['_old'][$code]);

        return false;
    }

    // No upload
    if (!$file) {
        return attribute_validate($attr, $item);
    }

    // Invalid file
    if (empty(file_ext($attr['type'])[$file['extension']])) {
        $item['_error'][$code] = _('Invalid file');

        return false;
    }

    $value = attribute_unique_file($file['name'], path('media'));

    // Upload failed
    if (!file_upload($file['tmp_name'], path('media', $value))) {
        $item['_error'][$code] = _('File upload failed');

        return false;
    }

    $item[$code] = $value;

    return attribute_validate($attr, $item);
}

/**
 * Validate datetime
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_datetime(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);
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

    return attribute_validate($attr, $item);
}

/**
 * Validate number
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_number(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);

    return attribute_validate($attr, $item);
}

/**
 * Validate editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_editor(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);

    if ($item[$code]) {
        $item[$code] = filter_html($item[$code]);
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate option
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_option(array $attr, array & $item): bool
{
    $attr['options'] = option($attr, $item);
    $code = $attr['id'];
    $item[$code] = attribute_cast($attr, $item[$code] ?? null);

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
    } elseif (!empty($attr['null'])) {
        $item[$code] = null;
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate menubasis
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_menubasis(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $meta = data('meta', $attr['entity_id']);

    if (!empty($item[$code]) && strpos($item[$code], ':') > 0) {
        $parts = explode(':', $item[$code]);
        $item['root_id'] = attribute_cast($meta['attributes']['root_id'], $parts[0]);
        $item['basis'] = attribute_cast($meta['attributes']['id'], $parts[1]);
    } else {
        $item['_error'][$code] = _('%s is a mandatory field', $attr['name']);

        return false;
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate callback
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_callback(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && !is_callable($item[$code])) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid callback');

        return false;
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate option
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_json(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && json_decode($item[$code], true) === null) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid JSON notation');

        return false;
    }

    return attribute_validate($attr, $item);
}

/**
 * Validate required
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_required(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($attr['is_required'])
        && empty($item[$code])
        && !option($attr, $item)
        && !attribute_ignore($attr, $item)
    ) {
        $item['_error'][$code] = _('%s is a mandatory field', $attr['name']);

        return false;
    }

    return true;
}

/**
 * Validate unique
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_unique(array $attr, array & $item): bool
{
    static $data = [];

    if (empty($attr['is_unique'])) {
        return true;
    }

    $code = $attr['id'];
    $entity = $attr['entity_id'];

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
    if (!empty($attr['unique_callback']) && is_callable($attr['unique_callback'])) {
        if (!empty($item[$code])) {
            $base = $item[$code];
        } elseif (!empty($attr['unique_base']) && !empty($item[$attr['unique_base']])) {
            $base = $item[$attr['unique_base']];
        } else {
            $base = null;
        }

        $item[$code] = $attr['unique_callback']($base, $data[$entity][$code], $item['_id']);

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

/**
 * Edit
 *
 * @return string
 */
function attribute_edit(): string
{
    return '';
}

/**
 * Edit varchar
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_varchar(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend'] . '" name="'
        . html_name($attr, $item) . '" value="' . encode(attribute_value($attr, $item))
        . '"' . html_required($attr, $item) . html_title($attr) . html_class($attr) . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit select
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_select(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $value = attribute_value($attr, $item);
    $attr['options'] = option($attr, $item);
    $htmlId =  html_id($attr, $item);
    $htmlName =  html_name($attr, $item);
    $multiple = !empty($attr['is_multiple']) ? ' multiple="multiple"' : '';

    if (!is_array($value)) {
        $value = empty($value) && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attr['options'])) {
        $html = '<optgroup label="' . _('No options configured') . '"></optgroup>';
    } else {
        $html = '<option value="" class="empty">' . _('Please choose') . '</option>';

        foreach ($attr['options'] as $optionId => $optionValue) {
            $selected = in_array($optionId, $value) ? ' selected="selected"' : '';
            $class = '';
            $level = '';

            if (is_array($optionValue) && !empty($optionValue['class'])) {
                $class = ' class="' . $optionValue['class'] . '"';
            }

            if (is_array($optionValue) && !empty($optionValue['level'])) {
                $level = ' data-level="' . $optionValue['level'] . '"';
            }

            $html .= '<option value="' . $optionId . '"' . $selected . $class . $level . '>'
                . option_name($optionId, $optionValue) . '</option>';
        }
    }

    $html = '<select id="' . $htmlId . '" name="' . $htmlName . '"' . html_required($attr, $item)
        . html_title($attr) . html_class($attr) . $multiple . '>' . $html . '</select>';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit input checkbox and radio
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_input_option(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $value = attribute_value($attr, $item);

    if ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['options'] = [1 => _('Yes')];
    } else {
        $attr['options'] = option($attr, $item);
    }

    $htmlId =  html_id($attr, $item);
    $htmlName =  html_name($attr, $item);
    $html = '';

    if ($attr['backend'] === 'bool') {
        $value = [(int) $value];
    } elseif (!is_array($value)) {
        $value = empty($value) && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attr['options'])) {
        $html .= '<span id="' . $htmlId . '">' .  _('No options configured') . '</span>';
    } else {
        foreach ($attr['options'] as $optionId => $optionValue) {
            $checked = in_array($optionId, $value) ? ' checked="checked"' : '';
            $html .= '<input id="' . $htmlId . '-' . $optionId . '" type="' . $attr['frontend']
                . '" name="' . $htmlName . '" value="' . $optionId . '"' . html_required($attr, $item)
                . html_title($attr) . html_class($attr) . $checked . ' /> <label for="' . $htmlId . '-'
                . $optionId . '" class="inline">' . option_name($optionId, $optionValue) . '</label>';
        }
    }

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit password
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_password(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend']
        . '" name="' . html_name($attr, $item) . '"  autocomplete="off"'
        . html_required($attr, $item) . html_title($attr)
        . html_class($attr) . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit file
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_file(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $html = '<div>' . $attr['view']($attr, $item) . '</div>'
        . '<input id="' . html_id($attr, $item) . '" type="file" name="'
        . html_name($attr, $item) . '"' . html_required($attr, $item)
        . html_title($attr) . html_class($attr) . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit datetime
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_datetime(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $code = $attr['id'];
    $item[$code] = attribute_value($attr, $item);
    $format = $attr['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i:s';

    if (!empty($item[$code]) && ($datetime = date_format(date_create($item[$code]), $format))) {
        $item[$code] = $datetime;
    } else {
        $item[$code] = null;
    }

    return attribute_edit_varchar($attr, $item);
}

/**
 * Edit number
 *
 * Renders input type range if min and max are set, otherwise input type number
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_number(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $value = attribute_value($attr, $item);
    $step = '';
    $min = '';
    $max = '';

    if (!empty($attr['step']) && is_numeric($attr['step'])) {
        $step = ' step="' . $attr['step'] . '"';
    }

    if (isset($attr['min']) && is_numeric($attr['min'])) {
        $min = ' min="' . $attr['min'] . '"';
    }

    if (isset($attr['max']) && is_numeric($attr['max'])) {
        $max = ' max="' . $attr['max'] . '"';
    }

    $type = $min && $max ? 'range' : 'number';
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $type
        . '" name="' . html_name($attr, $item) . '" value="' . $value . '"'
        . html_required($attr, $item) . html_title($attr) . html_class($attr) . $step . $min
        . $max . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit textarea
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_textarea(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $html = '<textarea id="' . html_id($attr, $item) . '" name="' . html_name($attr, $item) . '"'
        . html_required($attr, $item) . html_title($attr) . html_class($attr) . '>'
        . encode(attribute_value($attr, $item)) . '</textarea>';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Edit JSON
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_edit_json(array $attr, array $item): string
{
    if (!attribute_editable($attr, $item)) {
        return '';
    }

    $code = $attr['id'];
    $item[$code] = attribute_value($attr, $item);

    if (is_array($item[$code])) {
        $item[$code] = !empty($item[$code]) ? json_encode($item[$code]) : '';
    }

    return attribute_edit_textarea($attr, $item);
}

/**
 * View
 *
 * @return string
 */
function attribute_view(): string
{
    return '';
}

/**
 * View default
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_view_default(array $attr, array $item): string
{
    return attribute_viewable($attr) ? encode(attribute_value($attr, $item)) : '';
}

/**
 * View file
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_view_file(array $attr, array $item): string
{
    if (!attribute_viewable($attr)) {
        return '';
    }

    $value = attribute_value($attr, $item);

    if ($attr['action'] === 'system') {
        return $value;
    } elseif (!$value || !($file = media_load($value)) || empty(file_ext($attr['type'])[$file['extension']])) {
        return '';
    }

    $class = 'file-' . $attr['type'] . ' media-' . $attr['action'];
    $config = data('media', $attr['action']);

    if ($config) {
        $style = ' style="max-width:' . $config['width'] . 'px;max-height:' . $config['height'] . 'px;"';
    } else {
        $style = '';
    }

    $url = url_media($value);
    $link = '<a href="' . $url . '" title="' . $value . '" class="' . $class . '">' . $value . '</a>';

    if ($attr['type'] === 'image') {
        return '<img src="' . media_image($file, $attr['action']) . '" alt="' . $value . '" title="'
            . $value . '" class="' . $class . '" />';
    } elseif ($attr['type'] === 'audio') {
        return '<audio src="' . $url . '" title="' . $value . '" controls="controls" class="' . $class . '"'
            . $style . '>' . $link . '</audio>';
    } elseif ($attr['type'] === 'video') {
        return '<video src="' . $url . '" title="' . $value . '" controls="controls" class="' . $class . '"'
            . $style . '>' . $link . '</video>';
    } elseif ($attr['type'] === 'embed') {
        return '<embed src="' . $url . '" title="' . $value . '" autoplay="no" loop="no" class="' . $class . '"'
            . $style . ' />';
    }

    return $link;
}

/**
 * View datetime
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_view_datetime(array $attr, array $item): string
{
    if (!attribute_viewable($attr)) {
        return '';
    }

    $code = $attr['id'];
    $format = $attr['frontend'] === 'date' ? config('i18n.date_format') : config('i18n.datetime_format');

    return empty($item[$code]) ? '' : date_format(date_create($item[$code]), $format);
}

/**
 * View editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_view_editor(array $attr, array $item): string
{
    return attribute_viewable($attr) ? attribute_value($attr, $item) : '';
}

/**
 * View option
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_view_option(array $attr, array $item): string
{
    if (!attribute_viewable($attr)) {
        return '';
    }

    $value = attribute_value($attr, $item);

    if (!$attr['options'] = option($attr, $item)) {
        return '';
    }

    $values = [];

    foreach ((array) $value as $v) {
        if (!empty($attr['options'][$v])) {
            if (is_array($attr['options'][$v]) && !empty($attr['options'][$v]['name'])) {
                $values[] = $attr['options'][$v]['name'];
            } elseif (is_scalar($attr['options'][$v])) {
                $values[] = $attr['options'][$v];
            }
        }
    }

    return encode(implode(', ', $values));
}

/**
 * Get unique value
 *
 * @param string $needle
 * @param array $haystack
 * @param int|string $id
 *
 * @return string
 */
function attribute_unique(string $needle, array & $haystack, $id): string
{
    $needle = trim(preg_replace(['#/#', '#[-]+#i'], '-', filter_identifier($needle)), '-_');

    if (array_search($needle, $haystack) === $id || !in_array($needle, $haystack)) {
        $haystack[$id] = $needle;

        return $needle;
    }

    $needle .= '-';

    for ($i = 1; in_array($needle . $i, $haystack) && array_search($needle . $i, $haystack) !== $id; $i++);

    $haystack[$id] = $needle . $i;

    return $needle . $i;
}

/**
 * Generate unique file name in given path
 *
 * @param string $str
 * @param string $path
 *
 * @return string
 */
function attribute_unique_file(string $str, string $path): string
{
    $parts = explode('.', $str);
    $ext = array_pop($parts);
    $str = filter_identifier(implode('-', $parts));

    if (file_exists($path . '/' . $str . '.' . $ext)) {
        $str .= '-';

        for ($i = 1; file_exists($path . '/' . $str . $i . '.' . $ext); $i++);

        $str .= $i;
    }

    $str .= '.' . $ext;

    return $str;
}

/**
 * Label
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_label(array $attr, array $item): string
{
    $message = '';

    if (!empty($attr['is_required']) && !attribute_ignore($attr, $item)) {
        $message .= ' <em class="required">' . _('Required') . '</em>';
    }

    if (!empty($attr['is_unique'])) {
        $message .= ' <em class="unique">' . _('Unique') . '</em>';
    }

    return '<label for="' . html_id($attr, $item) . '">' . _($attr['name']) . $message
        . '</label>';
}

/**
 * Flag
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_flag(array $attr, array $item): string
{
    $html = '';

    if (!empty($attr['flag']) && is_array($attr['flag'])) {
        foreach ($attr['flag'] as $flag => $name) {
            $htmlId =  'data-' . $item['_id'] . '-' . $flag . '-' . $attr['id'];
            $htmlName =  'data[' . $item['_id'] . '][' . $flag . ']' . '[' . $attr['id'] . ']';
            $html .= ' <input id="' .  $htmlId . '" type="checkbox" name="' . $htmlName . '" value="1" title="'
                . _($name) . '" /> <label for="' . $htmlId . '" class="inline">'
                . _($name) . '</label>';
        }
    }

    return $html;
}

/**
 * HTML id attribute
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_id(array $attr, array $item): string
{
    return 'data-' . $item['_id'] . '-' . $attr['id'];
}

/**
 * HTML name attribute
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_name(array $attr, array $item): string
{
    return 'data[' . $item['_id'] . '][' . $attr['id'] . ']' . (!empty($attr['is_multiple']) ? '[]' : '');
}

/**
 * HTML required attribute
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_required(array $attr, array $item): string
{
    return !empty($attr['is_required']) && !attribute_ignore($attr, $item) ? ' required="required"' : '';
}

/**
 * HTML class attribute
 *
 * @param array $attr
 *
 * @return string
 */
function html_class(array $attr): string
{
    if (empty($attr['class'])) {
        return '';
    }

    $class = is_array($attr['class']) ? implode(' ', $attr['class']) : $attr['class'];

    return ' class="' . $class . '"';
}

/**
 * HTML title attribute
 *
 * @param array $attr
 *
 * @return string
 */
function html_title(array $attr): string
{
    return !empty($attr['description']) ? ' title="' . _($attr['description']) . '"' : '';
}

/**
 * Message
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_message(array $attr, array $item): string
{
    $message = '';

    if (!empty($attr['description'])) {
        $message .= '<p class="message">' . _($attr['description']) . '</p>';
    }

    if (!empty($item['_error'][$attr['id']])) {
        $message .= '<p class="message error">' . $item['_error'][$attr['id']] . '</p>';
    }

    return $message;
}
