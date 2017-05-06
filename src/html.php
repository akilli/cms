<?php
declare(strict_types = 1);

namespace qnd;

/**
 * HTML attributes
 *
 * @param string $name
 * @param array $attrs
 * @param string $val
 * @param bool $empty
 *
 * @return string
 */
function html_tag(string $name, array $attrs = [], string $val = null, bool $empty = false): string
{
    return '<' . $name . html_attr($attrs) . ($empty ? ' />' : '>' . $val . '</' . $name . '>');
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
        }

        $html .= ' ' . $key . '="' . addcslashes((string) $val, '"') . '"';
    }

    return $html;
}

/**
 * HTML id attribute
 *
 * @param array $attr
 *
 * @return string
 */
function html_id(array $attr): string
{
    return 'data-' . $attr['id'];
}

/**
 * HTML name attribute
 *
 * @param array $attr
 *
 * @return string
 */
function html_name(array $attr): string
{
    return 'data[' . $attr['id'] . ']' . (!empty($attr['multiple']) ? '[]' : '');
}

/**
 * Label
 *
 * @param array $attr
 *
 * @return string
 */
function html_label(array $attr): string
{
    $label = $attr['name'];

    if (!empty($attr['html']['required'])) {
        $label .= ' ' . html_tag('em', ['class' => 'required'], _('Required'));
    }

    if ($attr['uniq']) {
        $label .= ' ' . html_tag('em', ['class' => 'uniq'], _('Unique'));
    }

    return html_tag('label', ['for' => html_id($attr)], $label);
}

/**
 * Message
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function html_message(array $attr, array $data): string
{
    if (empty($data['_error'][$attr['id']])) {
        return '';
    }

    return html_tag('div', ['class' => 'message error'], $data['_error'][$attr['id']]);
}
