<?php
namespace akilli;

/**
 * Cast to appropriate php type
 *
 * @param array $attr
 * @param mixed $value
 *
 * @return mixed
 */
function cast(array $attr, $value)
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
            $value[$k] = cast($attr, $v);
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
function value(array $attr, array $item)
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
function ignorable(array $attr, array $item): bool
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
function editable(array & $attr, array $item): bool
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
function viewable(array & $attr): bool
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
    return cast($attr, $item[$attr['id']] ?? null);
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
    if (!editable($attr, $item)) {
        return '';
    }

    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend'] . '" name="'
        . html_name($attr, $item) . '" value="' . encode(value($attr, $item))
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
    if (!editable($attr, $item)) {
        return '';
    }

    $value = value($attr, $item);
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
    if (!editable($attr, $item)) {
        return '';
    }

    $value = value($attr, $item);

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
    if (!editable($attr, $item)) {
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
    if (!editable($attr, $item)) {
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
    if (!editable($attr, $item)) {
        return '';
    }

    $code = $attr['id'];
    $item[$code] = value($attr, $item);
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
    if (!editable($attr, $item)) {
        return '';
    }

    $value = value($attr, $item);
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
    if (!editable($attr, $item)) {
        return '';
    }

    $html = '<textarea id="' . html_id($attr, $item) . '" name="' . html_name($attr, $item) . '"'
        . html_required($attr, $item) . html_title($attr) . html_class($attr) . '>'
        . encode(value($attr, $item)) . '</textarea>';

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
    if (!editable($attr, $item)) {
        return '';
    }

    $code = $attr['id'];
    $item[$code] = value($attr, $item);

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
    return viewable($attr) ? encode(value($attr, $item)) : '';
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
    if (!viewable($attr)) {
        return '';
    }

    $value = value($attr, $item);

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
    if (!viewable($attr)) {
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
    return viewable($attr) ? value($attr, $item) : '';
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
    if (!viewable($attr)) {
        return '';
    }

    $value = value($attr, $item);

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
