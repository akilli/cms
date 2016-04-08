<?php
namespace akilli;

/**
 * Set appropriate php type
 *
 * @param array $attribute
 * @param mixed $value
 *
 * @return mixed
 */
function attribute_cast(array $attribute, $value)
{
    if ($value === null && !empty($attribute['null'])) {
        return null;
    }

    if ($attribute['backend'] === 'bool') {
        return boolval($value);
    }

    if ($attribute['backend'] === 'int') {
        return intval($value);
    }

    if ($attribute['backend'] === 'decimal') {
        return floatval($value);
    }

    if (!empty($attribute['is_multiple']) && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = attribute_cast($attribute, $v);
        }

        return $value;
    }

    return strval($value);
}

/**
 * Retrieve value
 *
 * @param array $attribute
 * @param array $item
 *
 * @return mixed
 */
function attribute_value(array $attribute, array $item)
{
    return $item[$attribute['id']] ?? $attribute['default'] ?? null;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_ignore(array $attribute, array $item): bool
{
    $code = $attribute['id'];
    $mustEdit = empty($item[$code]) || $attribute['action'] === 'edit' && !empty($item[$code]);

    return !empty($item['_old'])
        && empty($item['_reset'][$code])
        && $mustEdit
        && in_array($attribute['frontend'], ['password', 'file']);
}

/**
 * Prepare attribute if edit action is allowed
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_editable(array & $attribute, array $item): bool
{
    if (!meta_action('edit', $attribute)) {
        return false;
    }

    if (!empty($item['_error'][$attribute['id']])) {
        $attribute['class'] = empty($attribute['class']) ? [] : (array) $attribute['class'];
        $attribute['class'][] = 'invalid';
    }

    $attribute['action'] = 'edit';

    return true;
}

/**
 * Prepare attribute if view action is allowed
 *
 * @param array $attribute
 *
 * @return bool
 */
function attribute_viewable(array & $attribute): bool
{
    return $attribute['action'] === 'system' || $attribute['action'] && meta_action($attribute['action'], $attribute);
}

/**
 * Retrieve attribute options
 *
 * @param array $attribute
 * @param array $item
 *
 * @return array
 */
function attribute_options(array $attribute, array $item): array
{
    if ($attribute['backend'] === 'bool') {
        return attribute_options_bool();
    } elseif (!empty($attribute['foreign_entity_id'])) {
        return attribute_options_foreign($attribute);
    } elseif (!empty($attribute['options_callback'])) {
        return attribute_options_callback($attribute, $item);
    }

    return attribute_options_translate($attribute['options']);
}

/**
 * Retrieve bool options
 *
 * @return array
 */
function attribute_options_bool(): array
{
    return attribute_options_translate([_('No'), _('Yes')]);
}

/**
 * Retrieve foreign entity options
 *
 * @param array $attribute
 *
 * @return array
 */
function attribute_options_foreign(array $attribute): array
{
    return attribute_options_translate(model_load($attribute['foreign_entity_id']));
}

/**
 * Retrieve callback options
 *
 * @param array $attribute
 * @param array $item
 *
 * @return array
 */
function attribute_options_callback(array $attribute, array $item): array
{
    $params = [];

    foreach ($attribute['options_callback_param'] as $param) {
        if ($param === ':attribute') {
            $params[] = $attribute;
        } elseif ($param === ':item') {
            $params[] = $item;
        } elseif (preg_match('#^:(attribute|item)\.(.+)#', $param, $match)) {
            $params[] = ${$match[1]}[$match[3]] ?? null;
        } else {
            $params[] = $param;
        }
    }

    return attribute_options_translate($attribute['options_callback'](...$params));
}

/**
 * Translate options
 *
 * @param array $options
 *
 * @return array
 */
function attribute_options_translate(array $options): array
{
    foreach ($options as $key => $value) {
        if (is_scalar($value)) {
            $options[$key] = _($value);
        } elseif (is_array($value) && !empty($value['name'])) {
            $options[$key]['name'] = _($value['name']);
        }
    }

    return $options;
}

/**
 * Option name
 *
 * @param int|string $id
 * @param mixed $value
 *
 * @return string
 */
function attribute_option_name($id, $value): string
{
    if (is_array($value) && !empty($value['name'])) {
        return $value['name'];
    } elseif (is_scalar($value)) {
        return (string) $value;
    }

    return (string) $id;
}

/**
 * Menubasis
 *
 * @param string $entity
 *
 * @return array
 */
function attribute_options_menubasis(string $entity): array
{
    $meta = data('meta', $entity);
    $root = !empty($meta['attributes']['root_id']);
    $collection = $root ? model_load($meta['attributes']['root_id']['foreign_entity_id']) : null;
    $data = [];

    foreach (model_load($entity) as $item) {
        if ($root && empty($data[$item['root_id']  . ':0'])) {
            $data[$item['root_id']  . ':0']['name'] = $collection[$item['root_id']]['name'];
            $data[$item['root_id']  . ':0']['class'] = 'group';
        }

        $data[$item['menubasis']]['name'] = $item['name'];
        $data[$item['menubasis']]['level'] = $item['level'];
    }

    // Add roots without items to index menubasis
    if ($root) {
        foreach ($collection as $id => $item) {
            if (empty($data[$id  . ':0'])) {
                $data[$id  . ':0']['name'] = $item['name'];
                $data[$id  . ':0']['class'] = 'group';
            }
        }
    }

    return $data;
}

/**
 * Load
 *
 * @param array $attribute
 * @param array $item
 *
 * @return mixed
 */
function attribute_load(array $attribute, array $item)
{
    return attribute_cast($attribute, $item[$attribute['id']] ?? null);
}

/**
 * Load datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return mixed
 */
function attribute_load_datetime(array $attribute, array $item)
{
    $code = $attribute['id'];

    return empty($item[$code]) || $item[$code] === '0000-00-00 00:00:00' ? null : $item[$code];
}

/**
 * Load JSON
 *
 * @param array $attribute
 * @param array $item
 *
 * @return array
 */
function attribute_load_json(array $attribute, array $item): array
{
    $code = $attribute['id'];

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
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_save_password(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && is_string($item[$code])) {
        $item[$code] = password_hash($item[$code], PASSWORD_DEFAULT);
    }

    return true;
}

/**
 * Save multiple
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_save_multiple(array $attribute, array & $item): bool
{
    $item[$attribute['id']] = json_encode(array_filter(array_map('trim', (array) $item[$attribute['id']])));

    return true;
}

/**
 * Save search index
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_save_index(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
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
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_delete_file(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    // Delete old file
    if (!empty($item[$code]) && !media_delete($item[$code])) {
        $item['_error'][$code] = _('Could not delete old file %s', $item[$code]);

        return false;
    }

    return true;
}

/**
 * Validate
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate(array $attribute, array & $item): bool
{
    // Skip attributes that need no validation or are uneditable (unless required and new)
    if (!empty($attribute['auto'])
        || !meta_action('edit', $attribute) && (empty($attribute['is_required']) || !empty($item['_old']))
    ) {
        return true;
    }

    return attribute_validate_unique($attribute, $item) && attribute_validate_required($attribute, $item);
}

/**
 * Validate string
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_string(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);
    $item[$code] = trim((string) filter_var($item[$code], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return attribute_validate($attribute, $item);
}

/**
 * Validate email
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_email(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_EMAIL)) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid email');

        return false;
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate url
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_url(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_URL)) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid URL');

        return false;
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate file
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_file(array $attribute, array & $item): bool
{
    static $files;

    // Init files and config
    if ($files === null) {
        $files = (array) files('data');
    }

    $code = $attribute['id'];
    $item[$code] = null;
    $file = !empty($files[$item['_id']][$code]) ? $files[$item['_id']][$code] : null;

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
        return attribute_validate($attribute, $item);
    }

    // Invalid file
    if (empty(file_ext($attribute['type'])[$file['extension']])) {
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

    return attribute_validate($attribute, $item);
}

/**
 * Validate datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_datetime(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);
    $format = $attribute['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';

    if (!empty($item[$code])) {
        if ($datetime = date_format(date_create($item[$code]), $format)) {
            $item[$code] = $datetime;
        } else {
            $item[$code] = null;
            $item['_error'][$code] = _('Invalid date');

            return false;
        }
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate number
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_number(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);

    return attribute_validate($attribute, $item);
}

/**
 * Validate editor
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_editor(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);

    if ($item[$code]) {
        $item[$code] = filter_html($item[$code]);
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate option
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_option(array $attribute, array & $item): bool
{
    $attribute['options'] = attribute_options($attribute, $item);
    $code = $attribute['id'];
    $item[$code] = attribute_cast($attribute, $item[$code] ?? null);

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
            if (!isset($attribute['options'][$v])) {
                $item[$code] = null;
                $item['_error'][$code] = _('Invalid option for attribute %s', $code);

                return false;
            }
        }
    } elseif (!empty($attribute['null'])) {
        $item[$code] = null;
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate menubasis
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_menubasis(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $meta = data('meta', $attribute['entity_id']);

    if (empty($meta['attributes']['root_id'])) {
        $item['basis'] = !empty($item[$code]) ? $item[$code] : null;
        $item['basis'] = attribute_cast($meta['attributes']['id'], $item['basis']);
    } elseif (!empty($item[$code]) && strpos($item[$code], ':') > 0) {
        $parts = explode(':', $item[$code]);
        $item['root_id'] = attribute_cast($meta['attributes']['root_id'], $parts[0]);
        $item['basis'] = attribute_cast($meta['attributes']['id'], $parts[1]);
    } else {
        $item['_error'][$code] = _('%s is a mandatory field', $attribute['name']);

        return false;
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate callback
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_callback(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && !is_callable($item[$code])) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid callback');

        return false;
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate option
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_json(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && json_decode($item[$code], true) === null) {
        $item[$code] = null;
        $item['_error'][$code] = _('Invalid JSON notation');

        return false;
    }

    return attribute_validate($attribute, $item);
}

/**
 * Validate required
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_required(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($attribute['is_required'])
        && empty($item[$code])
        && !attribute_options($attribute, $item)
        && !attribute_ignore($attribute, $item)
    ) {
        $item['_error'][$code] = _('%s is a mandatory field', $attribute['name']);

        return false;
    }

    return true;
}

/**
 * Validate unique
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function attribute_validate_unique(array $attribute, array & $item): bool
{
    static $data = [];

    if (empty($attribute['is_unique'])) {
        return true;
    }

    $code = $attribute['id'];
    $entity = $attribute['entity_id'];

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
    if (!empty($attribute['unique_callback']) && is_callable($attribute['unique_callback'])) {
        if (!empty($item[$code])) {
            $base = $item[$code];
        } elseif (!empty($attribute['unique_base']) && !empty($item[$attribute['unique_base']])) {
            $base = $item[$attribute['unique_base']];
        } else {
            $base = null;
        }

        $item[$code] = $attribute['unique_callback']($base, $data[$entity][$code], $item['_id']);

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

    $item['_error'][$code] = _('%s must be unique', $attribute['name']);

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
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_varchar(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attribute, $item) . '" type="' . $attribute['frontend'] . '" name="'
        . html_name($attribute, $item) . '" value="' . encode(attribute_value($attribute, $item))
        . '"' . html_required($attribute, $item) . html_title($attribute) . html_class($attribute) . ' />';

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit select
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_select(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $value = attribute_value($attribute, $item);
    $attribute['options'] = attribute_options($attribute, $item);
    $htmlId =  html_id($attribute, $item);
    $htmlName =  html_name($attribute, $item);
    $multiple = !empty($attribute['is_multiple']) ? ' multiple="multiple"' : '';

    if (!is_array($value)) {
        $value = empty($value) && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attribute['options'])) {
        $html = '<optgroup label="' . _('No options configured') . '"></optgroup>';
    } else {
        $html = '<option value="" class="empty">' . _('Please choose') . '</option>';

        foreach ($attribute['options'] as $optionId => $optionValue) {
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
                . attribute_option_name($optionId, $optionValue) . '</option>';
        }
    }

    $html = '<select id="' . $htmlId . '" name="' . $htmlName . '"' . html_required($attribute, $item)
        . html_title($attribute) . html_class($attribute) . $multiple . '>' . $html . '</select>';

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit input checkbox and radio
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_input_option(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $value = attribute_value($attribute, $item);

    if ($attribute['backend'] === 'bool' && $attribute['frontend'] === 'checkbox') {
        $attribute['options'] = [1 => _('Yes')];
    } else {
        $attribute['options'] = attribute_options($attribute, $item);
    }

    $htmlId =  html_id($attribute, $item);
    $htmlName =  html_name($attribute, $item);
    $html = '';

    if ($attribute['backend'] === 'bool') {
        $value = [(int) $value];
    } elseif (!is_array($value)) {
        $value = empty($value) && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attribute['options'])) {
        $html .= '<span id="' . $htmlId . '">' .  _('No options configured') . '</span>';
    } else {
        foreach ($attribute['options'] as $optionId => $optionValue) {
            $checked = in_array($optionId, $value) ? ' checked="checked"' : '';
            $html .= '<input id="' . $htmlId . '-' . $optionId . '" type="' . $attribute['frontend']
                . '" name="' . $htmlName . '" value="' . $optionId . '"' . html_required($attribute, $item)
                . html_title($attribute) . html_class($attribute) . $checked . ' /> <label for="' . $htmlId . '-'
                . $optionId . '" class="inline">' . attribute_option_name($optionId, $optionValue) . '</label>';
        }
    }

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit password
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_password(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attribute, $item) . '" type="' . $attribute['frontend']
        . '" name="' . html_name($attribute, $item) . '"  autocomplete="off"'
        . html_required($attribute, $item) . html_title($attribute)
        . html_class($attribute) . ' />';

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit file
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_file(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $html = '<div>' . $attribute['view']($attribute, $item) . '</div>'
        . '<input id="' . html_id($attribute, $item) . '" type="file" name="'
        . html_name($attribute, $item) . '"' . html_required($attribute, $item)
        . html_title($attribute) . html_class($attribute) . ' />';

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_datetime(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $code = $attribute['id'];
    $item[$code] = attribute_value($attribute, $item);
    $format = $attribute['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i:s';

    if (!empty($item[$code]) && ($datetime = date_format(date_create($item[$code]), $format))) {
        $item[$code] = $datetime;
    } else {
        $item[$code] = null;
    }

    return attribute_edit_varchar($attribute, $item);
}

/**
 * Edit number
 *
 * Renders input type range if min and max are set, otherwise input type number
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_number(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $value = attribute_value($attribute, $item);
    $step = '';
    $min = '';
    $max = '';

    if (!empty($attribute['step']) && is_numeric($attribute['step'])) {
        $step = ' step="' . $attribute['step'] . '"';
    }

    if (isset($attribute['min']) && is_numeric($attribute['min'])) {
        $min = ' min="' . $attribute['min'] . '"';
    }

    if (isset($attribute['max']) && is_numeric($attribute['max'])) {
        $max = ' max="' . $attribute['max'] . '"';
    }

    $type = $min && $max ? 'range' : 'number';
    $html = '<input id="' . html_id($attribute, $item) . '" type="' . $type
        . '" name="' . html_name($attribute, $item) . '" value="' . $value . '"'
        . html_required($attribute, $item) . html_title($attribute) . html_class($attribute) . $step . $min
        . $max . ' />';

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit textarea
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_textarea(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $html = '<textarea id="' . html_id($attribute, $item) . '" name="' . html_name($attribute, $item) . '"'
        . html_required($attribute, $item) . html_title($attribute) . html_class($attribute) . '>'
        . encode(attribute_value($attribute, $item)) . '</textarea>';

    return html_label($attribute, $item) . $html . html_flag($attribute, $item) . html_message($attribute, $item);
}

/**
 * Edit JSON
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_edit_json(array $attribute, array $item): string
{
    if (!attribute_editable($attribute, $item)) {
        return '';
    }

    $code = $attribute['id'];
    $item[$code] = attribute_value($attribute, $item);

    if (is_array($item[$code])) {
        $item[$code] = !empty($item[$code]) ? json_encode($item[$code]) : '';
    }

    return attribute_edit_textarea($attribute, $item);
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
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_view_default(array $attribute, array $item): string
{
    return attribute_viewable($attribute) ? encode(attribute_value($attribute, $item)) : '';
}

/**
 * View file
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_view_file(array $attribute, array $item): string
{
    if (!attribute_viewable($attribute)) {
        return '';
    }

    $value = attribute_value($attribute, $item);

    if ($attribute['action'] === 'system') {
        return $value;
    } elseif (!$value
        || !($file = media_load($value))
        || empty(file_ext($attribute['type'])[$file['extension']])
    ) {
        return '';
    }

    $class = 'file-' . $attribute['type'] . ' media-' . $attribute['action'];
    $config = data('media', $attribute['action']);

    if ($config) {
        $style = ' style="max-width:' . $config['width'] . 'px;max-height:' . $config['height'] . 'px;"';
    } else {
        $style = '';
    }

    $url = url_media($value);
    $link = '<a href="' . $url . '" title="' . $value . '" class="' . $class . '">' . $value . '</a>';

    if ($attribute['type'] === 'image') {
        return '<img src="' . media_image($file, $attribute['action']) . '" alt="' . $value . '" title="'
            . $value . '" class="' . $class . '" />';
    } elseif ($attribute['type'] === 'audio') {
        return '<audio src="' . $url . '" title="' . $value . '" controls="controls" class="' . $class . '"'
            . $style . '>' . $link . '</audio>';
    } elseif ($attribute['type'] === 'video') {
        return '<video src="' . $url . '" title="' . $value . '" controls="controls" class="' . $class . '"'
            . $style . '>' . $link . '</video>';
    } elseif ($attribute['type'] === 'embed') {
        return '<embed src="' . $url . '" title="' . $value . '" autoplay="no" loop="no" class="' . $class . '"'
            . $style . ' />';
    }

    return $link;
}

/**
 * View datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_view_datetime(array $attribute, array $item): string
{
    if (!attribute_viewable($attribute)) {
        return '';
    }

    $code = $attribute['id'];
    $format = $attribute['frontend'] === 'date' ? config('i18n.date_format') : config('i18n.datetime_format');

    return empty($item[$code]) ? '' : date_format(date_create($item[$code]), $format);
}

/**
 * View editor
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_view_editor(array $attribute, array $item): string
{
    return attribute_viewable($attribute) ? attribute_value($attribute, $item) : '';
}

/**
 * View option
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function attribute_view_option(array $attribute, array $item): string
{
    if (!attribute_viewable($attribute)) {
        return '';
    }

    $value = attribute_value($attribute, $item);

    if (!$attribute['options'] = attribute_options($attribute, $item)) {
        return '';
    }

    $values = [];

    foreach ((array) $value as $v) {
        if (!empty($attribute['options'][$v])) {
            if (is_array($attribute['options'][$v]) && !empty($attribute['options'][$v]['name'])) {
                $values[] = $attribute['options'][$v]['name'];
            } elseif (is_scalar($attribute['options'][$v])) {
                $values[] = $attribute['options'][$v];
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
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_label(array $attribute, array $item): string
{
    $message = '';

    if (!empty($attribute['is_required']) && !attribute_ignore($attribute, $item)) {
        $message .= ' <em class="required">' . _('Required') . '</em>';
    }

    if (!empty($attribute['is_unique'])) {
        $message .= ' <em class="unique">' . _('Unique') . '</em>';
    }

    return '<label for="' . html_id($attribute, $item) . '">' . _($attribute['name']) . $message
        . '</label>';
}

/**
 * Flag
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_flag(array $attribute, array $item): string
{
    $html = '';

    if (!empty($attribute['flag']) && is_array($attribute['flag'])) {
        foreach ($attribute['flag'] as $flag => $name) {
            $htmlId =  'data-' . $item['_id'] . '-' . $flag . '-' . $attribute['id'];
            $htmlName =  'data[' . $item['_id'] . '][' . $flag . ']' . '[' . $attribute['id'] . ']';
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
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_id(array $attribute, array $item): string
{
    return 'data-' . $item['_id'] . '-' . $attribute['id'];
}

/**
 * HTML name attribute
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_name(array $attribute, array $item): string
{
    return 'data[' . $item['_id'] . '][' . $attribute['id'] . ']' . (!empty($attribute['is_multiple']) ? '[]' : '');
}

/**
 * HTML required attribute
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_required(array $attribute, array $item): string
{
    return !empty($attribute['is_required']) && !attribute_ignore($attribute, $item) ? ' required="required"' : '';
}

/**
 * HTML class attribute
 *
 * @param array $attribute
 *
 * @return string
 */
function html_class(array $attribute): string
{
    if (empty($attribute['class'])) {
        return '';
    }

    $class = is_array($attribute['class']) ? implode(' ', $attribute['class']) : $attribute['class'];

    return ' class="' . $class . '"';
}

/**
 * HTML title attribute
 *
 * @param array $attribute
 *
 * @return string
 */
function html_title(array $attribute): string
{
    return !empty($attribute['description']) ? ' title="' . _($attribute['description']) . '"' : '';
}

/**
 * Message
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_message(array $attribute, array $item): string
{
    $message = '';

    if (!empty($attribute['description'])) {
        $message .= '<p class="message">' . _($attribute['description']) . '</p>';
    }

    if (!empty($item['_error'][$attribute['id']])) {
        $message .= '<p class="message error">' . $item['_error'][$attribute['id']] . '</p>';
    }

    return $message;
}
