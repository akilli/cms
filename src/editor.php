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
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $item[$attr['uid']] = $item[$attr['uid']] ?? $attr['val'];
    $attr['opt'] = opt($attr);
    $attr['html']['id'] =  html_id($attr, $item);
    $attr['html']['name'] =  html_name($attr, $item);
    $attr['html']['data-type'] =  $attr['type'];

    if ($attr['required'] && !ignorable($attr, $item)) {
        $attr['html']['required'] = true;
    }

    if ($attr['multiple']) {
        $attr['html']['multiple'] = true;
    }

    if (!empty($item['_error'][$attr['uid']])) {
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
    }

    if ($attr['editor'] && ($call = fqn('editor_' . $attr['editor'])) && ($html = $call($attr, $item))) {
        return html_label($attr, $item) . $html . html_message($attr, $item);
    }

    return '';
}

/**
 * Delete editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_delete(array $attr, array $item): string
{
    if (!isset($item['_old'][$attr['uid']])) {
        return '';
    }

    $input = [
        'id' => 'data-' . $item['_id'] . '-_delete-' . $attr['uid'],
        'name' => 'data[' . $item['_id'] . '][_delete]' . '[' . $attr['uid'] . ']',
        'type' => 'checkbox',
        'value' => 1,
    ];
    $label = ['for' => $input['id'], 'class' => 'inline'];

    return html_tag('input', $input, null, true) . html_tag('label', $label, _('Reset'));
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
    $value = $item[$attr['uid']];

    if (!is_array($value)) {
        $value = !$value && !is_numeric($value) ? [] : [$value];
    }

    if (empty($attr['opt'])) {
        $html = html_tag('optgroup', ['label' => _('No options configured')]);
    } else {
        $html = html_tag('option', [], _('Please choose'));

        foreach ($attr['opt'] as $optId => $optVal) {
            $a = ['value' => $optId];

            if (in_array($optId, $value)) {
                $a['selected'] = true;
            }

            $html .= html_tag('option', $a, $optVal);
        }
    }

    return html_tag('select', $attr['html'], $html);
}

/**
 * Option editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_opt(array $attr, array $item): string
{
    if (!$attr['opt']) {
        return html_tag('span', ['id' => $attr['html']['id']], _('No options configured'));
    } elseif ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['opt'] = [1 => _('Yes')];
    }

    $value = $item[$attr['uid']];

    if ($attr['backend'] === 'bool') {
        $value = [(int) $value];
    } elseif (!is_array($value)) {
        $value = !$value && !is_numeric($value) ? [] : [$value];
    }

    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => $attr['frontend'],
            'value' => $optId,
            'checked' => in_array($optId, $value)
        ];
        $a = array_replace($attr['html'], $a);
        $html .= html_tag('input', $a, null, true);
        $html .= html_tag('label', ['for' => $htmlId, 'class' => 'inline'], $optVal);
    }

    return $html;
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
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['uid']] ? encode($item[$attr['uid']]) : $item[$attr['uid']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
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
    $item[$attr['uid']] = null;
    $attr['html']['autocomplete'] = 'off';

    return editor_text($attr, $item);
}

/**
 * Int editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_int(array $attr, array $item): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['uid']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
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
    $in = data('format', 'date.backend');
    $out = data('format', 'date.frontend');
    $item[$attr['uid']] = $item[$attr['uid']] ? filter_date($item[$attr['uid']], $in, $out) : '';
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['uid']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
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
    $in = data('format', 'datetime.backend');
    $out = data('format', 'datetime.frontend');
    $item[$attr['uid']] = $item[$attr['uid']] ? filter_date($item[$attr['uid']], $in, $out) : '';
    $attr['html']['type'] = 'datetime-local';
    $attr['html']['value'] = $item[$attr['uid']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
}

/**
 * Time editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function editor_time(array $attr, array $item): string
{
    $in = data('format', 'time.backend');
    $out = data('format', 'time.frontend');
    $item[$attr['uid']] = $item[$attr['uid']] ? filter_date($item[$attr['uid']], $in, $out) : '';
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $item[$attr['uid']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html_tag('input', $attr['html'], null, true);
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
    $attr['html']['type'] = $attr['frontend'];
    $delete = editor_delete($attr, $item);

    return html_tag('div', [], viewer($attr, $item)) . html_tag('input', $attr['html'], null, true) . $delete;
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
    $item[$attr['uid']] = $item[$attr['uid']] ? encode($item[$attr['uid']]) : $item[$attr['uid']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html_tag('textarea', $attr['html'], $item[$attr['uid']]);
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
    if (is_array($item[$attr['uid']])) {
        $item[$attr['uid']] = !empty($item[$attr['uid']]) ? json_encode($item[$attr['uid']]) : '';
    }

    return editor_textarea($attr, $item);
}
