<?php
namespace qnd;

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

    if (!empty($attr['is_required']) && !ignorable($attr, $item)) {
        $message .= ' <em class="required">' . _('Required') . '</em>';
    }

    if (!empty($attr['is_unique'])) {
        $message .= ' <em class="unique">' . _('Unique') . '</em>';
    }

    return '<label for="' . html_id($attr, $item) . '">' . _($attr['name']) . $message . '</label>';
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
    return !empty($attr['is_required']) && !ignorable($attr, $item) ? ' required="required"' : '';
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
 * Message
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function html_message(array $attr, array $item): string
{
    if (empty($item['_error'][$attr['id']])) {
        return '';
    }

    return '<p class="message error">' . $item['_error'][$attr['id']] . '</p>';
}
