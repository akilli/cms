<?php
namespace attribute;

use akilli;
use media;

/**
 * Set appropriate php type
 *
 * @param array $attribute
 * @param mixed $value
 *
 * @return mixed
 */
function type(array $attribute, $value)
{
    if ($value === null && !empty($attribute['null'])) {
        return null;
    }

    if ($attribute['backend'] === 'bool') {
        return !empty($value);
    }

    if ($attribute['backend'] === 'int') {
        return intval($value);
    }

    if ($attribute['backend'] === 'decimal') {
        return floatval($value);
    }

    if (!empty($attribute['is_multiple']) && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = type($attribute, $v);
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
function value(array $attribute, array $item)
{
    if (array_key_exists($attribute['id'], $item)) {
        return $item[$attribute['id']];
    } elseif (array_key_exists('default', $attribute)) {
        return $attribute['default'];
    }

    return null;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function ignore(array $attribute, array $item): bool
{
    $code = $attribute['id'];
    $mustEdit = empty($item[$code]) || $attribute['action'] === 'edit' && !empty($item[$code]);

    return !empty($item['_original'])
        && empty($item['__reset'][$code])
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
function is_edit(array & $attribute, array $item): bool
{
    if (!akilli\metadata_action('edit', $attribute)) {
        return false;
    }

    if (!empty($item['__error'][$attribute['id']])) {
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
function is_view(array & $attribute): bool
{
    return $attribute['action'] === 'system'
        || $attribute['action'] && akilli\metadata_action($attribute['action'], $attribute);
}

/**
 * Retrieve attribute options
 *
 * @param array $attribute
 * @param array $item
 * @param bool $cache
 *
 * @return array
 */
function options(array $attribute, array $item = null, bool $cache = false): array
{
    static $data = [];

    if (isset($data[$attribute['entity_id']][$attribute['id']]) && $cache) {
        return $data[$attribute['entity_id']][$attribute['id']];
    }

    if ($attribute['backend'] === 'bool') {
        $options = [akilli\_('No'), akilli\_('Yes')];
    } elseif (!empty($attribute['foreign_entity_id'])) {
        $options = akilli\model_load($attribute['foreign_entity_id']);
    } elseif (!empty($attribute['options_callback'])) {
        if (empty($attribute['options_callback_param'])) {
            $options = $attribute['options_callback']();
        } else {
            $options = call_user_func_array(
                $attribute['options_callback'],
                array_map(
                    function ($param) use ($attribute, $item, & $cache) {
                        // Replace placeholders
                        if ($param === ':attribute') {
                            return $attribute;
                        } elseif ($param === ':item') {
                            $cache = false;

                            return $item;
                        } elseif (preg_match('#^:(attribute|item)_(.+)#', $param, $match)) {
                            $cache = $cache && ${$match[1]} !== 'item';

                            return array_key_exists($match[2], ${$match[1]}) ? ${$match[1]}[$match[2]] : null;
                        }

                        return $param;
                    },
                    $attribute['options_callback_param']
                )
            );
        }
    } else {
        $options = (array) $attribute['options'];
    }

    $options = options_translate($options);

    if ($cache) {
        $data[$attribute['entity_id']][$attribute['id']] = $options;
    }

    return $options;
}

/**
 * Translate options
 *
 * @param array $options
 *
 * @return array
 */
function options_translate(array $options): array
{
    if (!$options) {
        return $options;
    }

    return array_map(
        function ($value) {
            if (is_scalar($value)) {
                $value = akilli\_($value);
            } elseif (is_array($value) && !empty($value['name'])) {
                $value['name'] = akilli\_($value['name']);
            }

            return $value;
        },
        $options
    );
}

/**
 * Option name
 *
 * @param int|string $id
 * @param mixed $value
 *
 * @return string
 */
function option_name($id, $value): string
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
function options_menubasis(string $entity): array
{
    $metadata = akilli\data('metadata', $entity);
    $root = !empty($metadata['attributes']['root_id']);
    $collection = $root ? akilli\model_load($metadata['attributes']['root_id']['foreign_entity_id']) : null;
    $data = [];

    foreach (akilli\model_load($entity) as $item) {
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
function load(array $attribute, array $item)
{
    return type($attribute, $item[$attribute['id']] ?? null);
}

/**
 * Load datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return mixed
 */
function load_datetime(array $attribute, array $item)
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
function load_json(array $attribute, array $item): array
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
function save(): bool
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
function save_password(array $attribute, array & $item): bool
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
function save_multiple(array $attribute, array & $item): bool
{
    $item[$attribute['id']] = json_encode(array_filter(array_map('trim', (array) $item[$attribute['id']])));

    return true;
}

/**
 * Delete
 *
 * @return bool
 */
function delete(): bool
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
function delete_file(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    // Delete old file
    if (!empty($item[$code]) && !media\delete($item[$code])) {
        $item['__error'][$code] = akilli\_('Could not delete old file %s', $item[$code]);

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
function validate(array $attribute, array & $item): bool
{
    // Skip attributes that need no validation or are uneditable (unless required and new)
    if (!empty($attribute['auto'])
        || !akilli\metadata_action('edit', $attribute) && (empty($attribute['is_required']) || !empty($item['_original']))
    ) {
        return true;
    }

    return validate_unique($attribute, $item) && validate_required($attribute, $item);
}

/**
 * Validate string
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_string(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);
    $item[$code] = trim((string) filter_var($item[$code], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return validate($attribute, $item);
}

/**
 * Validate email
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_email(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_EMAIL)) {
        $item[$code] = null;
        $item['__error'][$code] = akilli\_('Invalid email');

        return false;
    }

    return validate($attribute, $item);
}

/**
 * Validate url
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_url(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_URL)) {
        $item[$code] = null;
        $item['__error'][$code] = akilli\_('Invalid URL');

        return false;
    }

    return validate($attribute, $item);
}

/**
 * Validate file
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_file(array $attribute, array & $item): bool
{
    static $files;

    // Init files and config
    if ($files === null) {
        $files = (array) akilli\files('data');
    }

    $code = $attribute['id'];
    $item[$code] = null;
    $file = !empty($files[$item['_id']][$code]) ? $files[$item['_id']][$code] : null;

    // Delete old file
    if (!empty($item['_original'][$code])
        && ($file || !empty($item['__reset'][$code]))
        && !media\delete($item['_original'][$code])
    ) {
        $item['__error'][$code] = akilli\_('Could not delete old file %s', $item['_original'][$code]);

        return false;
    }

    // No upload
    if (!$file) {
        return validate($attribute, $item);
    }

    // Invalid file
    if (empty(akilli\file_ext($attribute['type'])[$file['extension']])) {
        $item['__error'][$code] = akilli\_('Invalid file');

        return false;
    }

    $value = unique_file($file['name'], akilli\path('media'));

    // Upload failed
    if (!akilli\file_upload($file['tmp_name'], akilli\path('media', $value))) {
        $item['__error'][$code] = akilli\_('File upload failed');

        return false;
    }

    $item[$code] = $value;

    return validate($attribute, $item);
}

/**
 * Validate datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_datetime(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);
    $format = $attribute['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';

    if (!empty($item[$code])) {
        if ($datetime = date_format(date_create($item[$code]), $format)) {
            $item[$code] = $datetime;
        } else {
            $item[$code] = null;
            $item['__error'][$code] = akilli\_('Invalid date');

            return false;
        }
    }

    return validate($attribute, $item);
}

/**
 * Validate number
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_number(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);

    return validate($attribute, $item);
}

/**
 * Validate editor
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_editor(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);

    if ($item[$code]) {
        $item[$code] = akilli\filter_html($item[$code]);
    }

    return validate($attribute, $item);
}

/**
 * Validate option
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_option(array $attribute, array & $item): bool
{
    $attribute['options'] = options($attribute, $item);
    $code = $attribute['id'];
    $item[$code] = type($attribute, $item[$code] ?? null);

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
                $item['__error'][$code] = akilli\_('Invalid option for attribute %s', $code);

                return false;
            }
        }
    } elseif (!empty($attribute['null'])) {
        $item[$code] = null;
    }

    return validate($attribute, $item);
}

/**
 * Validate menubasis
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_menubasis(array $attribute, array & $item): bool
{
    $code = $attribute['id'];
    $metadata = akilli\data('metadata', $attribute['entity_id']);

    if (empty($metadata['attributes']['root_id'])) {
        $item['basis'] = !empty($item[$code]) ? $item[$code] : null;
        $item['basis'] = type($metadata['attributes']['id'], $item['basis']);
    } elseif (!empty($item[$code]) && strpos($item[$code], ':') > 0) {
        $parts = explode(':', $item[$code]);
        $item['root_id'] = type($metadata['attributes']['root_id'], $parts[0]);
        $item['basis'] = type($metadata['attributes']['id'], $parts[1]);
    } else {
        $item['__error'][$code] = akilli\_('%s is a mandatory field', $attribute['name']);

        return false;
    }

    return validate($attribute, $item);
}

/**
 * Validate callback
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_callback(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && !is_callable($item[$code])) {
        $item[$code] = null;
        $item['__error'][$code] = akilli\_('Invalid callback');

        return false;
    }

    return validate($attribute, $item);
}

/**
 * Validate option
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_json(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && json_decode($item[$code], true) === null) {
        $item[$code] = null;
        $item['__error'][$code] = akilli\_('Invalid JSON notation');

        return false;
    }

    return validate($attribute, $item);
}

/**
 * Validate required
 *
 * @param array $attribute
 * @param array $item
 *
 * @return bool
 */
function validate_required(array $attribute, array & $item): bool
{
    $code = $attribute['id'];

    if (!empty($attribute['is_required'])
        && empty($item[$code])
        && !options($attribute, $item)
        && !ignore($attribute, $item)
    ) {
        $item['__error'][$code] = akilli\_('%s is a mandatory field', $attribute['name']);

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
function validate_unique(array $attribute, array & $item): bool
{
    static $data = [];

    if (empty($attribute['is_unique'])) {
        return true;
    }

    $code = $attribute['id'];
    $entity = $attribute['entity_id'];

    // Existing values
    if (!isset($data[$entity])) {
        $data[$entity] = akilli\model_load($entity, null, 'unique');

        if ($entity === 'entity' && ($ids = array_keys(akilli\data('metadata')))
            || $entity === 'attribute' && ($ids = array_keys(akilli\data('metadata', 'eav_content')['attributes']))
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

    $item['__error'][$code] = akilli\_('%s must be unique', $attribute['name']);

    return false;
}

/**
 * Edit
 *
 * @return string
 */
function edit(): string
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
function edit_varchar(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attribute, $item) . '" type="' . $attribute['frontend'] . '" name="'
        . html_name($attribute, $item) . '" value="' . akilli\encode(value($attribute, $item))
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
function edit_select(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $value = value($attribute, $item);
    $attribute['options'] = options($attribute, $item);
    $htmlId =  html_id($attribute, $item);
    $htmlName =  html_name($attribute, $item);
    $multiple = !empty($attribute['is_multiple']) ? ' multiple="multiple"' : '';

    if (!is_array($value)) {
        $value = empty($value) && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attribute['options'])) {
        $html = '<optgroup label="' . akilli\_('No options configured') . '"></optgroup>';
    } else {
        $html = '<option value="" class="empty">' . akilli\_('Please choose') . '</option>';

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
                . option_name($optionId, $optionValue) . '</option>';
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
function edit_input_option(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $value = value($attribute, $item);

    if ($attribute['backend'] === 'bool' && $attribute['frontend'] === 'checkbox') {
        $attribute['options'] = [1 => akilli\_('Yes')];
    } else {
        $attribute['options'] = options($attribute, $item);
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
        $html .= '<span id="' . $htmlId . '">' .  akilli\_('No options configured') . '</span>';
    } else {
        foreach ($attribute['options'] as $optionId => $optionValue) {
            $checked = in_array($optionId, $value) ? ' checked="checked"' : '';
            $html .= '<input id="' . $htmlId . '-' . $optionId . '" type="' . $attribute['frontend']
                . '" name="' . $htmlName . '" value="' . $optionId . '"' . html_required($attribute, $item)
                . html_title($attribute) . html_class($attribute) . $checked . ' /> <label for="' . $htmlId . '-'
                . $optionId . '" class="inline">' . option_name($optionId, $optionValue) . '</label>';
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
function edit_password(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
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
function edit_file(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
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
function edit_datetime(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $code = $attribute['id'];
    $item[$code] = value($attribute, $item);
    $format = $attribute['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i:s';

    if (!empty($item[$code]) && ($datetime = date_format(date_create($item[$code]), $format))) {
        $item[$code] = $datetime;
    } else {
        $item[$code] = null;
    }

    return edit_varchar($attribute, $item);
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
function edit_number(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $value = value($attribute, $item);
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
function edit_textarea(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $html = '<textarea id="' . html_id($attribute, $item) . '" name="' . html_name($attribute, $item) . '"'
        . html_required($attribute, $item) . html_title($attribute) . html_class($attribute) . '>'
        . akilli\encode(value($attribute, $item)) . '</textarea>';

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
function edit_json(array $attribute, array $item): string
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $code = $attribute['id'];
    $item[$code] = value($attribute, $item);

    if (is_array($item[$code])) {
        $item[$code] = !empty($item[$code]) ? json_encode($item[$code]) : '';
    }

    return edit_textarea($attribute, $item);
}

/**
 * View
 *
 * @return string
 */
function view(): string
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
function view_default(array $attribute, array $item): string
{
    return is_view($attribute) ? akilli\encode(value($attribute, $item)) : '';
}

/**
 * View file
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function view_file(array $attribute, array $item): string
{
    if (!is_view($attribute)) {
        return '';
    }

    $value = value($attribute, $item);

    if ($attribute['action'] === 'system') {
        return $value;
    } elseif (!$value
        || !($file = media\load($value))
        || empty(akilli\file_ext($attribute['type'])[$file['extension']])
    ) {
        return '';
    }

    $class = 'file-' . $attribute['type'] . ' media-' . $attribute['action'];
    $config = akilli\data('media', $attribute['action']);

    if ($config) {
        $style = ' style="max-width:' . $config['width'] . 'px;max-height:' . $config['height'] . 'px;"';
    } else {
        $style = '';
    }

    $url = akilli\url_media($value);
    $link = '<a href="' . $url . '" title="' . $value . '" class="' . $class . '">' . $value . '</a>';

    if ($attribute['type'] === 'image') {
        return '<img src="' . media\image($file, $attribute['action']) . '" alt="' . $value . '" title="'
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
function view_datetime(array $attribute, array $item): string
{
    if (!is_view($attribute)) {
        return '';
    }

    $code = $attribute['id'];
    $format = $attribute['frontend'] === 'date' ? akilli\config('i18n.date_format') : akilli\config('i18n.datetime_format');

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
function view_editor(array $attribute, array $item): string
{
    return is_view($attribute) ? value($attribute, $item) : '';
}

/**
 * View option
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function view_option(array $attribute, array $item): string
{
    if (!is_view($attribute)) {
        return '';
    }

    $value = value($attribute, $item);

    if (!$attribute['options'] = options($attribute, $item, true)) {
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

    return akilli\encode(implode(', ', $values));
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

    if (!empty($attribute['is_required']) && !ignore($attribute, $item)) {
        $message .= ' <em class="required">' . akilli\_('Required') . '</em>';
    }

    if (!empty($attribute['is_unique'])) {
        $message .= ' <em class="unique">' . akilli\_('Unique') . '</em>';
    }

    return '<label for="' . html_id($attribute, $item) . '">' . akilli\_($attribute['name']) . $message
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
                . akilli\_($name) . '" /> <label for="' . $htmlId . '" class="inline">'
                . akilli\_($name) . '</label>';
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
    return !empty($attribute['is_required']) && !ignore($attribute, $item) ? ' required="required"' : '';
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
    return !empty($attribute['description']) ? ' title="' . akilli\_($attribute['description']) . '"' : '';
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
        $message .= '<p class="message">' . akilli\_($attribute['description']) . '</p>';
    }

    if (!empty($item['__error'][$attribute['id']])) {
        $message .= '<p class="message error">' . $item['__error'][$attribute['id']] . '</p>';
    }

    return $message;
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
function unique(string $needle, array & $haystack, $id): string
{
    $needle = trim(preg_replace(['#/#', '#[-]+#i'], '-', akilli\filter_identifier($needle)), '-_');

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
function unique_file(string $str, string $path): string
{
    $parts = explode('.', $str);
    $ext = array_pop($parts);
    $str = akilli\filter_identifier(implode('-', $parts));

    if (file_exists($path . '/' . $str . '.' . $ext)) {
        $str .= '-';

        for ($i = 1; file_exists($path . '/' . $str . $i . '.' . $ext); $i++);

        $str .= $i;
    }

    $str .= '.' . $ext;

    return $str;
}
