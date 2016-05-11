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
    if (!editable($attr, $item)) {
        return '';
    }

    $item[$attr['id']] = value($attr, $item);

    if ($attr['frontend'] === 'select') {
        return editor_select($attr, $item);
    } elseif (in_array($attr['frontend'], ['checkbox', 'radio'])) {
        return editor_option($attr, $item);
    }

    $callback = fqn('editor_' . $attr['type']);

    return is_callable($callback) ? $callback($attr, $item) : '';
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
    $attr['options'] = option($attr);
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
                $class = ' class="' . implode(' ', $optionValue['class']) . '"';
            }

            if (is_array($optionValue) && !empty($optionValue['level'])) {
                $level = ' data-level="' . $optionValue['level'] . '"';
            }

            $html .= '<option value="' . $optionId . '"' . $selected . $class . $level . '>'
                . option_name($optionId, $optionValue) . '</option>';
        }
    }

    $html = '<select id="' . $htmlId . '" name="' . $htmlName . '"' . html_required($attr, $item) . html_class($attr)
        . $multiple . '>' . $html . '</select>';

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * Option editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_option(array $attr, array $item): string
{
    if ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['options'] = [1 => _('Yes')];
    } else {
        $attr['options'] = option($attr);
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
            $html .= '<input id="' . $htmlId . '-' . $optionId . '" type="' . $attr['frontend'] . '" name="' . $htmlName
                . '" value="' . $optionId . '"' . html_required($attr, $item) . html_class($attr) . $checked
                . ' /> <label for="' . $htmlId . '-' . $optionId . '" class="inline">'
                . option_name($optionId, $optionValue) . '</label>';
        }
    }

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * Text editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_text(array $attr, array $item): string
{
    $min = isset($attr['min']) && is_numeric($attr['min']) ? ' minlength="' . $attr['min'] . '"' : '';
    $max = isset($attr['max']) && is_numeric($attr['max']) ? ' maxlength="' . $attr['max'] . '"' : '';
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend'] . '" name="'
        . html_name($attr, $item) . '" value="' . encode($item[$attr['id']]) . '"' . html_required($attr, $item)
        . html_class($attr) . $min . $max . ' />';

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * Email editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_email(array $attr, array $item): string
{
    return editor_text($attr, $item);
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
    $min = isset($attr['min']) && is_numeric($attr['min']) ? ' minlength="' . $attr['min'] . '"' : '';
    $max = isset($attr['max']) && is_numeric($attr['max']) ? ' maxlength="' . $attr['max'] . '"' : '';
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend'] . '" name="' . html_name($attr, $item)
        . '"  autocomplete="off"' . html_required($attr, $item) . html_class($attr) . $min . $max . ' />';

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * URL editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_url(array $attr, array $item): string
{
    return editor_text($attr, $item);
}

/**
 * Number editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_number(array $attr, array $item): string
{
    $step = !empty($attr['step']) && is_numeric($attr['step']) ? ' step="' . $attr['step'] . '"' : '';
    $min = isset($attr['min']) && is_numeric($attr['min']) ? ' min="' . $attr['min'] . '"' : '';
    $max = isset($attr['max']) && is_numeric($attr['max']) ? ' max="' . $attr['max'] . '"' : '';
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend']
        . '" name="' . html_name($attr, $item) . '" value="' . $item[$attr['id']] . '"'
        . html_required($attr, $item) . html_class($attr) . $step . $min . $max . ' />';

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * Decimal editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_decimal(array $attr, array $item): string
{
    return editor_number($attr, $item);
}

/**
 * Range editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_range(array $attr, array $item): string
{
    return editor_number($attr, $item);
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
    $format = $attr['type'] === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i:s';

    if (!empty($item[$attr['id']]) && ($datetime = date_format(date_create($item[$attr['id']]), $format))) {
        $item[$attr['id']] = $datetime;
    } else {
        $item[$attr['id']] = null;
    }

    $step = !empty($attr['step']) && is_numeric($attr['step']) ? ' step="' . $attr['step'] . '"' : '';
    $min = isset($attr['min']) && is_numeric($attr['min']) ? ' min="' . $attr['min'] . '"' : '';
    $max = isset($attr['max']) && is_numeric($attr['max']) ? ' max="' . $attr['max'] . '"' : '';
    $html = '<input id="' . html_id($attr, $item) . '" type="' . $attr['frontend'] . '" name="'
        . html_name($attr, $item) . '" value="' . encode($item[$attr['id']]) . '"' . html_required($attr, $item)
        . html_class($attr) . $step . $min . $max . ' />';

    return html_label($attr, $item) . $html . html_message($attr, $item);
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
    return editor_datetime($attr, $item);
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
    $min = isset($attr['min']) && is_numeric($attr['min']) ? ' minlength="' . $attr['min'] . '"' : '';
    $max = isset($attr['max']) && is_numeric($attr['max']) ? ' maxlength="' . $attr['max'] . '"' : '';
    $html = '<textarea id="' . html_id($attr, $item) . '" name="' . html_name($attr, $item) . '"'
        . html_required($attr, $item) . html_class($attr) . $min . $max . '>' . encode($item[$attr['id']])
        . '</textarea>';

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * Index editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_index(array $attr, array $item): string
{
    return editor_textarea($attr, $item);
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
    if (is_array($item[$attr['id']])) {
        $item[$attr['id']] = !empty($item[$attr['id']]) ? json_encode($item[$attr['id']]) : '';
    }

    return editor_textarea($attr, $item);
}

/**
 * Rich text editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_rte(array $attr, array $item): string
{
    return editor_textarea($attr, $item);
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
    $resetId =  'data-' . $item['_id'] . '-_reset-' . $attr['id'];
    $resetName =  'data[' . $item['_id'] . '][_reset]' . '[' . $attr['id'] . ']';
    $html = '<div>' . viewer($attr, $item) . '</div>'
        . '<input id="' . html_id($attr, $item) . '" type="file" name="' . html_name($attr, $item) . '"'
        . html_required($attr, $item) . html_class($attr) . ' />'
        . ' <input id="' .  $resetId . '" type="checkbox" name="' . $resetName . '" value="1" title="'
        . _('Reset') . '" /> <label for="' . $resetId . '" class="inline">' . _('Reset') . '</label>';

    return html_label($attr, $item) . $html . html_message($attr, $item);
}

/**
 * Audio editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_audio(array $attr, array $item): string
{
    return editor_file($attr, $item);
}

/**
 * Embed editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_embed(array $attr, array $item): string
{
    return editor_file($attr, $item);
}

/**
 * Image editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_image(array $attr, array $item): string
{
    return editor_file($attr, $item);
}

/**
 * Video editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_video(array $attr, array $item): string
{
    return editor_file($attr, $item);
}
