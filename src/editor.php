<?php
declare(strict_types = 1);

namespace qnd;

/**
 * Editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ?? $attr['val'];
    $attr['opt'] = opt($attr);
    $attr['html']['id'] =  'data-' . $attr['id'];
    $attr['html']['name'] =  'data[' . $attr['id'] . ']' . (!empty($attr['multiple']) ? '[]' : '');
    $attr['html']['data-type'] =  $attr['type'];
    $label = $attr['name'];
    $error = '';

    if ($attr['required'] && !ignorable($attr, $data)) {
        $attr['html']['required'] = true;
        $label .= ' ' . html('em', ['class' => 'required'], _('Required'));
    }

    if ($attr['uniq']) {
        $label .= ' ' . html('em', ['class' => 'uniq'], _('Unique'));
    }

    if ($attr['multiple']) {
        $attr['html']['multiple'] = true;
    }

    if (!empty($data['_error'][$attr['id']])) {
        $attr['html']['class'] = empty($attr['html']['class']) ? 'invalid' : $attr['html']['class'] . ' invalid';
        $error = html('div', ['class' => 'error'], $data['_error'][$attr['id']]);
    }

    if ($attr['editor'] && ($html = $attr['editor']($attr, $data))) {
        return html('label', ['for' => $attr['html']['id']], $label) . $html . $error;
    }

    return '';
}

/**
 * Select editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_select(array $attr, array $data): string
{
    if (!$attr['opt']) {
        return html('em', ['id' => $attr['html']['id']], _('No options configured'));
    }

    $val = $data[$attr['id']];

    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $html = html('option', ['value' => ''], _('Please choose'));

    foreach ($attr['opt'] as $optId => $optVal) {
        $a = ['value' => $optId];

        if (in_array($optId, $val)) {
            $a['selected'] = true;
        }

        $html .= html('option', $a, $optVal);
    }

    return html('select', $attr['html'], $html);
}

/**
 * Option editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_opt(array $attr, array $data): string
{
    if (!$attr['opt']) {
        return html('em', ['id' => $attr['html']['id']], _('No options configured'));
    }

    if ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
        $attr['opt'] = [1 => _('Yes')];
    }

    $val = $data[$attr['id']];

    if ($attr['backend'] === 'bool') {
        $val = [(int) $val];
    } elseif (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $html = '';

    foreach ($attr['opt'] as $optId => $optVal) {
        $htmlId = $attr['html']['id'] . '-' . $optId;
        $a = [
            'id' => $htmlId,
            'name' => $attr['html']['name'],
            'type' => $attr['frontend'],
            'value' => $optId,
            'checked' => in_array($optId, $val)
        ];
        $a = array_replace($attr['html'], $a);
        $html .= html('input', $a, null, true);
        $html .= html('label', ['for' => $htmlId], $optVal);
    }

    return $html;
}

/**
 * Text editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_text(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? encode($data[$attr['id']]) : $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html('input', $attr['html'], null, true);
}

/**
 * Password editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_password(array $attr, array $data): string
{
    $data[$attr['id']] = null;
    $attr['html']['autocomplete'] = 'off';

    return editor_text($attr, $data);
}

/**
 * Int editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_int(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['html']['type'] ?? $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['min'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['max'] = $attr['maxval'];
    }

    return html('input', $attr['html'], null, true);
}

/**
 * Date editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_date(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter_date($data[$attr['id']], DATE['b'], DATE['f']) : '';

    return editor_int($attr, $data);
}

/**
 * Datetime editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_datetime(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter_date($data[$attr['id']], DATETIME['b'], DATETIME['f']) : '';
    $attr['html']['type'] = 'datetime-local';

    return editor_int($attr, $data);
}

/**
 * Time editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_time(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? filter_date($data[$attr['id']], TIME['b'], TIME['f']) : '';

    return editor_int($attr, $data);
}

/**
 * File editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_file(array $attr, array $data): string
{
    $current = $data[$attr['id']] ? html('div', [], viewer($attr, $data)) : '';
    $hidden = html('input', ['name' => $attr['html']['name'], 'type' => 'hidden'], null, true);
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['accept'] = file_accept($attr['type']);

    return $current . $hidden . html('input', $attr['html'], null, true);
}

/**
 * Textarea editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_textarea(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? encode($data[$attr['id']]) : $data[$attr['id']];

    if ($attr['minval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['minlength'] = $attr['minval'];
    }

    if ($attr['maxval'] > 0 && $attr['minval'] <= $attr['maxval']) {
        $attr['html']['maxlength'] = $attr['maxval'];
    }

    return html('textarea', $attr['html'], $data[$attr['id']]);
}

/**
 * JSON editor
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function editor_json(array $attr, array $data): string
{
    if (is_array($data[$attr['id']])) {
        $data[$attr['id']] = !empty($data[$attr['id']]) ? json_encode($data[$attr['id']]) : '';
    }

    return editor_textarea($attr, $data);
}
