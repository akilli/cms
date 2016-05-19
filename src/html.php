<?php
namespace qnd;

/**
 * HTML attributes
 *
 * @param array $attrs
 *
 * @return string
 */
function html_attr(array $attrs): string
{
    $cfg['default'] = ['del' => '', 'start' => '', 'end' => '', 'pre' => '', 'post' => '', 'sep' => ''];
    $cfg['array'] = ['del' => ', ', 'start' => '[', 'end' => ']', 'sep' => ' => '];
    $cfg['attr'] = ['del' => ' ', 'sep' => '='];

    $data = [];

    foreach ($attrs as $key => $val) {
        if (is_array($val)) {
            $val = implode(' ', $val);
        } elseif ($val === false) {
            continue;
        } elseif ($val === true) {
            $val = $key;
        }

        $data[$key] = $key . '="' . addcslashes($val, '"') . '"';
    }

    return implode(' ', $data);
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

    if (!empty($attr['required']) && !ignorable($attr, $item)) {
        $message .= ' <em class="required">' . _('Required') . '</em>';
    }

    if (!empty($attr['unambiguous'])) {
        $message .= ' <em class="unambiguous">' . _('Unambiguous') . '</em>';
    }

    return '<label for="' . html_id($attr, $item) . '">' . _($attr['name']) . $message . '</label>';
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
    return 'data[' . $item['_id'] . '][' . $attr['id'] . ']' . (!empty($attr['multiple']) ? '[]' : '');
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
    return !empty($attr['required']) && !ignorable($attr, $item) ? ' required="required"' : '';
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
    return $attr['class'] ? ' class="' . implode(' ', $attr['class']) . '"' : '';
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

    return '<div class="message error">' . $item['_error'][$attr['id']] . '</div>';
}
