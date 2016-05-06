<?php
namespace qnd;

/**
 * Editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor(array $attr, array $item): string
{
    if (!editable($attr, $item) || !$attr['editor']) {
        return '';
    }

    $item[$attr['id']] = value($attr, $item);

    return $attr['editor']($attr, $item);
}

/**
 * Varchar editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_varchar(array $attr, array $item): string
{
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend'] . '" name="'
        . html_name($attr, $item) . '" value="' . encode($item[$attr['id']])
        . '"' . html_required($attr, $item) . html_class($attr) . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Select editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_select(array $attr, array $item): string
{
    $value = $item[$attr['id']];
    $attr['options'] = option($attr, $item);
    $htmlId =  html_id($attr, $item);
    $htmlName =  html_name($attr, $item);
    $multiple = !empty($attr['multiple']) ? ' multiple="multiple"' : '';

    if (!is_array($value)) {
        $value = !$value && !is_numeric($value) ? [] : [$value];
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
        . html_class($attr) . $multiple . '>' . $html . '</select>';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Input option editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_input_option(array $attr, array $item): string
{
    if ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['options'] = [1 => _('Yes')];
    } else {
        $attr['options'] = option($attr, $item);
    }

    $value = $item[$attr['id']];
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
                . html_class($attr) . $checked . ' /> <label for="' . $htmlId . '-' . $optionId . '" class="inline">'
                . option_name($optionId, $optionValue) . '</label>';
        }
    }

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Password editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_password(array $attr, array $item): string
{
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend']
        . '" name="' . html_name($attr, $item) . '"  autocomplete="off"'
        . html_required($attr, $item) . html_class($attr) . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * File editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_file(array $attr, array $item): string
{
    $html = '<div>' . viewer($attr, $item) . '</div>'
        . '<input id="' . html_id($attr, $item) . '" type="file" name="' . html_name($attr, $item) . '"'
        . html_required($attr, $item) . html_class($attr) . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Date editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_date(array $attr, array $item): string
{
    $code = $attr['id'];

    if (!empty($item[$code]) && ($datetime = date_format(date_create($item[$code]), 'Y-m-d'))) {
        $item[$code] = $datetime;
    } else {
        $item[$code] = null;
    }

    return editor_varchar($attr, $item);
}

/**
 * Datetime editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_datetime(array $attr, array $item): string
{
    $code = $attr['id'];

    if (!empty($item[$code]) && ($datetime = date_format(date_create($item[$code]), 'Y-m-d\TH:i:s'))) {
        $item[$code] = $datetime;
    } else {
        $item[$code] = null;
    }

    return editor_varchar($attr, $item);
}

/**
 * Number editor
 *
 * Renders input type range if min and max are set, otherwise input type number
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_number(array $attr, array $item): string
{
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
        . '" name="' . html_name($attr, $item) . '" value="' . $item[$attr['id']] . '"'
        . html_required($attr, $item) . html_class($attr) . $step . $min
        . $max . ' />';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * Textarea editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_textarea(array $attr, array $item): string
{
    $html = '<textarea id="' . html_id($attr, $item) . '" name="' . html_name($attr, $item) . '"'
        . html_required($attr, $item) . html_class($attr) . '>'
        . encode($item[$attr['id']]) . '</textarea>';

    return html_label($attr, $item) . $html . html_flag($attr, $item) . html_message($attr, $item);
}

/**
 * JSON editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_json(array $attr, array $item): string
{
    $code = $attr['id'];

    if (is_array($item[$code])) {
        $item[$code] = !empty($item[$code]) ? json_encode($item[$code]) : '';
    }

    return editor_textarea($attr, $item);
}
