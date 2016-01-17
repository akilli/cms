<?php
namespace attribute;

use app;
use config;
use file;
use filter;
use http;
use i18n;
use media;
use metadata;
use model;
use url;

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
function ignore(array $attribute, array $item)
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
function is_edit(array & $attribute, array $item)
{
    if (!metadata\action('edit', $attribute)) {
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
 * @return string
 */
function is_view(array & $attribute)
{
    return $attribute['action'] === 'system'
    || $attribute['action'] && metadata\action($attribute['action'], $attribute);
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
function options(array $attribute, array $item = null, $cache = false)
{
    static $data = [];

    if (isset($data[$attribute['entity_id']][$attribute['id']]) && $cache) {
        return $data[$attribute['entity_id']][$attribute['id']];
    }

    if ($attribute['backend'] === 'bool') {
        $options = [i18n\translate('No'), i18n\translate('Yes')];
    } elseif (!empty($attribute['foreign_entity_id'])) {
        $options = model\load($attribute['foreign_entity_id']);
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
function options_translate(array $options)
{
    if (!$options) {
        return $options;
    }

    return array_map(
        function ($value) {
            if (is_scalar($value)) {
                $value = i18n\translate($value);
            } elseif (is_array($value) && !empty($value['name'])) {
                $value['name'] = i18n\translate($value['name']);
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
function option_name($id, $value)
{
    if (is_array($value) && !empty($value['name'])) {
        return $value['name'];
    } elseif (is_scalar($value)) {
        return $value;
    }

    return $id;
}

/**
 * Menubasis
 *
 * @param string $entity
 *
 * @return array
 */
function options_menubasis($entity)
{
    $metadata = app\data('metadata', $entity);
    $root = !empty($metadata['attributes']['root_id']);
    $collection = $root ? model\load($metadata['attributes']['root_id']['foreign_entity_id']) : null;
    $data = [];

    foreach (model\load($entity) as $item) {
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
    return type($attribute, isset($item[$attribute['id']]) ? $item[$attribute['id']] : null);
}

/**
 * Load datetime
 *
 * @param array $attribute
 * @param array $item
 *
 * @return array
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
function load_json(array $attribute, array $item)
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
function save()
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
function save_password(array $attribute, array & $item)
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
function save_multiple(array $attribute, array & $item)
{
    $item[$attribute['id']] = json_encode(array_filter(array_map('trim', (array) $item[$attribute['id']])));

    return true;
}

/**
 * Delete
 *
 * @return bool
 */
function delete()
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
function delete_file(array $attribute, array & $item)
{
    $code = $attribute['id'];

    // Delete old file
    if (!empty($item[$code]) && !media\delete($item[$code])) {
        $item['__error'][$code] = i18n\translate('Could not delete old file %s', $item[$code]);

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
function validate(array $attribute, array & $item)
{
    // Skip attributes that need no validation or are uneditable (unless required and new)
    if (!empty($attribute['auto'])
        || !metadata\action('edit', $attribute) && (empty($attribute['is_required']) || !empty($item['_original']))
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
function validate_string(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);
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
function validate_email(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_EMAIL)) {
        $item[$code] = null;
        $item['__error'][$code] = i18n\translate('Invalid email');

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
function validate_url(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);

    if ($item[$code] && !$item[$code] = filter_var($item[$code], FILTER_VALIDATE_URL)) {
        $item[$code] = null;
        $item['__error'][$code] = i18n\translate('Invalid URL');

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
function validate_file(array $attribute, array & $item)
{
    static $files;

    // Init files and config
    if ($files === null) {
        $files = (array) http\files('data');
    }

    $code = $attribute['id'];
    $item[$code] = null;
    $file = (!empty($files[$item['_id']][$code])) ? $files[$item['_id']][$code] : null;

    // Delete old file
    if (!empty($item['_original'][$code])
        && ($file || !empty($item['__reset'][$code]))
        && !media\delete($item['_original'][$code])
    ) {
        $item['__error'][$code] = i18n\translate('Could not delete old file %s', $item['_original'][$code]);

        return false;
    }

    // No upload
    if (!$file) {
        return validate($attribute, $item);
    }

    // Invalid file
    if (!in_array($file['extension'], file\extensions($attribute['type']))) {
        $item['__error'][$code] = i18n\translate('Invalid file');

        return false;
    }

    $value = unique_file($file['name'], app\path('media'));

    // Upload failed
    if (!file\upload($file['tmp_name'], app\path('media', $value))) {
        $item['__error'][$code] = i18n\translate('File upload failed');

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
function validate_datetime(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);
    $timezone = $attribute['frontend'] === 'datetime' ? timezone_open('UTC') : null;
    $format = $attribute['frontend'] === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';

    if (!empty($item[$code])) {
        if (($datetime = date_create($item[$code], $timezone)) && ($datetime = date_format($datetime, $format))) {
            $item[$code] = $datetime;
        } else {
            $item[$code] = null;
            $item['__error'][$code] = i18n\translate('Invalid date');

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
function validate_number(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);

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
function validate_editor(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);

    if ($item[$code]) {
        $item[$code] = filter\html($item[$code]);
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
function validate_option(array $attribute, array & $item)
{
    $attribute['options'] = options($attribute, $item);
    $code = $attribute['id'];
    $item[$code] = type($attribute, isset($item[$code]) ? $item[$code] : null);

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
                $item['__error'][$code] = i18n\translate('Invalid option for attribute %s', $code);

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
function validate_menubasis(array $attribute, array & $item)
{
    $code = $attribute['id'];
    $metadata = app\data('metadata', $attribute['entity_id']);

    if (empty($metadata['attributes']['root_id'])) {
        $item['basis'] = (!empty($item[$code])) ? $item[$code] : null;
        $item['basis'] = type($metadata['attributes']['id'], $item['basis']);
    } elseif (!empty($item[$code]) && strpos($item[$code], ':') > 0) {
        $parts = explode(':', $item[$code]);
        $item['root_id'] = type($metadata['attributes']['root_id'], $parts[0]);
        $item['basis'] = type($metadata['attributes']['id'], $parts[1]);
    } else {
        $item['__error'][$code] = i18n\translate('%s is a mandatory field', $attribute['name']);

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
function validate_callback(array $attribute, array & $item)
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && !is_callable($item[$code])) {
        $item[$code] = null;
        $item['__error'][$code] = i18n\translate('Invalid callback');

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
function validate_json(array $attribute, array & $item)
{
    $code = $attribute['id'];

    if (!empty($item[$code]) && json_decode($item[$code], true) === null) {
        $item[$code] = null;
        $item['__error'][$code] = i18n\translate('Invalid JSON notation');

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
function validate_required(array $attribute, array & $item)
{
    $code = $attribute['id'];

    if (!empty($attribute['is_required'])
        && empty($item[$code])
        && !options($attribute, $item)
        && !ignore($attribute, $item)
    ) {
        $item['__error'][$code] = i18n\translate('%s is a mandatory field', $attribute['name']);

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
function validate_unique(array $attribute, array & $item)
{
    static $data = [];

    if (empty($attribute['is_unique'])) {
        return true;
    }

    $code = $attribute['id'];
    $entity = $attribute['entity_id'];

    // Existing values
    if (!isset($data[$entity])) {
        $data[$entity] = model\load($entity, null, 'unique');

        if ($entity === 'entity' && ($ids = array_keys(app\data('metadata')))
            || $entity === 'attribute' && ($ids = array_keys(app\data('metadata', 'eav_content')['attributes']))
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
    } elseif (!empty($item[$code]) && (array_search($item[$code], $data[$entity][$code]) === $item['_id']
            || !in_array($item[$code], $data[$entity][$code]))
    ) {
        // Provided value is unique
        $data[$entity][$code][$item['_id']] = $item[$code];

        return true;
    }

    $item['__error'][$code] = i18n\translate('%s must be unique', $attribute['name']);

    return false;
}

/**
 * Edit
 *
 * @return string
 */
function edit()
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
function edit_varchar(array $attribute, array $item)
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attribute, $item) . '" type="' . $attribute['frontend'] . '" name="'
        . html_name($attribute, $item) . '" value="' . filter\encode(value($attribute, $item))
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
function edit_select(array $attribute, array $item)
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
        $html = '<optgroup label="' . i18n\translate('No options configured') . '"></optgroup>';
    } else {
        $html = '<option value="" class="empty">' . i18n\translate('Please choose') . '</option>';

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
function edit_input_option(array $attribute, array $item)
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $value = value($attribute, $item);

    if ($attribute['backend'] === 'bool' && $attribute['frontend'] === 'checkbox') {
        $attribute['options'] = [1 => i18n\translate('Yes')];
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
        $html .= '<span id="' . $htmlId . '">' .  i18n\translate('No options configured') . '</span>';
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
function edit_password(array $attribute, array $item)
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
function edit_file(array $attribute, array $item)
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
function edit_datetime(array $attribute, array $item)
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $code = $attribute['id'];
    $item[$code] = value($attribute, $item);
    $timezone = $attribute['frontend'] === 'datetime' ? timezone_open('UTC') : null;

    if ($attribute['frontend'] === 'date') {
        $format = 'Y-m-d';
    } elseif ($attribute['frontend'] === 'datetime') {
        $format = 'Y-m-d\TH:i:s\Z';
    } else {
        $format = 'Y-m-d\TH:i:s';
    }

    if (!empty($item[$code])
        && ($datetime = date_create($item[$code], $timezone))
        && ($datetime = date_format($datetime, $format))
    ) {
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
function edit_number(array $attribute, array $item)
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
function edit_textarea(array $attribute, array $item)
{
    if (!is_edit($attribute, $item)) {
        return '';
    }

    $html = '<textarea id="' . html_id($attribute, $item) . '" name="' . html_name($attribute, $item) . '"'
        . html_required($attribute, $item) . html_title($attribute) . html_class($attribute) . '>'
        . filter\encode(value($attribute, $item)) . '</textarea>';

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
function edit_json(array $attribute, array $item)
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
function view()
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
function view_default(array $attribute, array $item)
{
    return is_view($attribute) ? filter\encode(value($attribute, $item)) : '';
}

/**
 * View file
 *
 * @param array $attribute
 * @param array $item
 *
 *
 * @return string
 */
function view_file(array $attribute, array $item)
{
    if (!is_view($attribute)) {
        return '';
    }

    $value = value($attribute, $item);

    if ($attribute['action'] === 'system') {
        return $value;
    } elseif (!$value
        || !($file = media\load($value))
        || !in_array($file['extension'], file\extensions($attribute['type']))
    ) {
        return '';
    }

    $class = 'file-' . $attribute['type'] . ' media-' . $attribute['action'];
    $config = app\data('media', $attribute['action']);

    if ($config) {
        $style = ' style="max-width:' . $config['width'] . 'px;max-height:' . $config['height'] . 'px;"';
    } else {
        $style = '';
    }

    $url = url\media($value);
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
function view_datetime(array $attribute, array $item)
{
    static $formats, $tz;

    if (!is_view($attribute)) {
        return '';
    }

    if ($formats === null) {
        $formats = [
            'date' => config\value('i18n.date_format'),
            'datetime' => config\value('i18n.datetime_format')
        ];
        $tz = timezone_open(config\value('i18n.timezone'));
    }

    $code = $attribute['id'];
    $timezone = $attribute['frontend'] === 'datetime' ? timezone_open('UTC') : null;
    $format = $attribute['frontend'] === 'date' ? $formats['date'] : $formats['datetime'];

    if (empty($item[$code]) || !$datetime = date_create($item[$code], $timezone)) {
        return '';
    }

    if (timezone_name_get(date_timezone_get($datetime)) !== timezone_name_get($tz)) {
        date_timezone_set($datetime, $tz);
    }

    return date_format($datetime, $format) ?: '';
}

/**
 * View editor
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function view_editor(array $attribute, array $item)
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
function view_option(array $attribute, array $item)
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

    return filter\encode(implode(', ', $values));
}

/**
 * Label
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_label(array $attribute, array $item)
{
    $message = '';

    if (!empty($attribute['is_required']) && !ignore($attribute, $item)) {
        $message .= ' <em class="required">' . i18n\translate('Required') . '</em>';
    }

    if (!empty($attribute['is_unique'])) {
        $message .= ' <em class="unique">' . i18n\translate('Unique') . '</em>';
    }

    return '<label for="' . html_id($attribute, $item) . '">' . i18n\translate($attribute['name']) . $message
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
function html_flag(array $attribute, array $item)
{
    $html = '';

    if (!empty($attribute['flag']) && is_array($attribute['flag'])) {
        foreach ($attribute['flag'] as $flag => $name) {
            $htmlId =  'data-' . $item['_id'] . '-' . $flag . '-' . $attribute['id'];
            $htmlName =  'data[' . $item['_id'] . '][' . $flag . ']' . '[' . $attribute['id'] . ']';
            $html .= ' <input id="' .  $htmlId . '" type="checkbox" name="' . $htmlName . '" value="1" title="'
                . i18n\translate($name) . '" /> <label for="' . $htmlId . '" class="inline">'
                . i18n\translate($name) . '</label>';
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
function html_id(array $attribute, array $item)
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
function html_name(array $attribute, array $item)
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
function html_required(array $attribute, array $item)
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
function html_class(array $attribute)
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
function html_title(array $attribute)
{
    return !empty($attribute['description']) ? ' title="' . i18n\translate($attribute['description']) . '"' : '';
}

/**
 * Message
 *
 * @param array $attribute
 * @param array $item
 *
 * @return string
 */
function html_message(array $attribute, array $item)
{
    $message = '';

    if (!empty($attribute['description'])) {
        $message .= '<p class="message">' . i18n\translate($attribute['description']) . '</p>';
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
 * @param int $id
 *
 * @return mixed
 */
function unique($needle, array & $haystack, $id)
{
    $needle = trim(preg_replace(['#/#', '#[-]+#i'], '-', filter\identifier($needle)), '-_');

    if (array_search($needle, $haystack) === $id || !in_array($needle, $haystack)) {
        $haystack[$id] = $needle;

        return $needle;
    }

    $needle .= '-';

    for ($i = 1; in_array($needle . $i, $haystack) && array_search($needle . $i, $haystack) !== $id; $i++) {
    }

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
function unique_file($str, $path)
{
    $parts = explode('.', $str);
    $ext = array_pop($parts);
    $str = filter\identifier(implode('-', $parts));

    if (file_exists($path . '/' . $str . '.' . $ext)) {
        $str .= '-';

        for ($i = 1; file_exists($path . '/' . $str . $i . '.' . $ext); $i++) {
        }

        $str .= $i;
    }

    $str .= '.' . $ext;

    return $str;
}
