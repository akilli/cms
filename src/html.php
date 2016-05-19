<?php
namespace qnd;

/**
 * HTML attributes
 *
 * @param string $name
 * @param array $attrs
 * @param string $value
 * @param bool $empty
 *
 * @return string
 */
function html_tag(string $name, array $attrs = [], string $value = null, bool $empty = false): string
{
    return '<' . $name . html_attr($attrs) . ($empty ? ' />' : '>' . $value . '</' . $name . '>');
}

/**
 * HTML attributes
 *
 * @param array $attrs
 *
 * @return string
 */
function html_attr(array $attrs): string
{
    $html = '';

    foreach ($attrs as $key => $val) {
        if ($val === false) {
            continue;
        } elseif ($val === true) {
            $val = $key;
        } elseif (is_array($val)) {
            $val = implode(' ', $val);
        }

        $html .= ' ' . $key . '="' . addcslashes($val, '"') . '"';
    }

    return $html;
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
    $label = _($attr['name']);

    if (!empty($attr['required']) && !ignorable($attr, $item)) {
        $label .= ' ' . html_tag('em', ['class' => 'required'], _('Required'));
    }

    if (!empty($attr['uniq'])) {
        $label .= ' ' . html_tag('em', ['class' => 'uniq'], _('Unique'));
    }

    return html_tag('label', ['for' => html_id($attr, $item)], $label);
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
    return !empty($attr['required']) && !ignorable($attr, $item) ? html_attr(['required' => true]) : '';
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
    return !empty($attr['html']['class']) ? html_attr(['class' => $attr['html']['class']]) : '';
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

    return html_tag('div', ['class' => 'message error'], $item['_error'][$attr['id']]);
}
