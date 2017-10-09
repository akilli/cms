<?php
declare(strict_types = 1);

namespace cms;

/**
 * Editor
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

    foreach ([['min', 'max'], ['minlength', 'maxlength']] as $edge) {
        if ($attr[$edge[0]] <= $attr[$edge[1]]) {
            if ($attr[$edge[0]] > 0) {
                $attr['html'][$edge[0]] = $attr[$edge[0]];
            }

            if ($attr[$edge[1]] > 0) {
                $attr['html'][$edge[1]] = $attr[$edge[1]];
            }
        }
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
 * Checkbox editor
 */
function editor_checkbox(array $attr, array $data): string
{
    if ($attr['backend'] === 'bool') {
        $attr['opt'] = [1 => _('Yes')];
    }

    $hidden = html('input', ['name' => $attr['html']['name'], 'type' => 'hidden'], null, true);

    return $hidden . editor_radio($attr, $data);
}

/**
 * Radio editor
 */
function editor_radio(array $attr, array $data): string
{
    $val = $data[$attr['id']];

    if (!is_array($val)) {
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
 * Select editor
 */
function editor_select(array $attr, array $data): string
{
    $val = $data[$attr['id']];

    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    $html = html('option', ['value' => ''], _('Please choose'));

    foreach ($attr['opt'] as $optId => $optVal) {
        $html .= html('option', ['value' => $optId, 'selected' => in_array($optId, $val)], $optVal);
    }

    return html('select', $attr['html'], $html);
}

/**
 * Text editor
 */
function editor_text(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? encode($data[$attr['id']]) : $data[$attr['id']];

    return html('input', $attr['html'], null, true);
}

/**
 * Password editor
 */
function editor_password(array $attr, array $data): string
{
    $data[$attr['id']] = null;
    $attr['html']['autocomplete'] = 'off';

    return editor_text($attr, $data);
}

/**
 * Int editor
 */
function editor_int(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']];

    return html('input', $attr['html'], null, true);
}

/**
 * Date editor
 */
function editor_date(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? filter_date($data[$attr['id']], DATE['b'], DATE['f']) : '';

    return html('input', $attr['html'], null, true);
}

/**
 * Datetime editor
 */
function editor_datetime(array $attr, array $data): string
{
    $attr['html']['type'] = 'datetime-local';
    $attr['html']['value'] = $data[$attr['id']] ? filter_date($data[$attr['id']], DATETIME['b'], DATETIME['f']) : '';

    return html('input', $attr['html'], null, true);
}

/**
 * Time editor
 */
function editor_time(array $attr, array $data): string
{
    $attr['html']['type'] = $attr['frontend'];
    $attr['html']['value'] = $data[$attr['id']] ? filter_date($data[$attr['id']], TIME['b'], TIME['f']) : '';

    return html('input', $attr['html'], null, true);
}

/**
 * File editor
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
 */
function editor_textarea(array $attr, array $data): string
{
    $data[$attr['id']] = $data[$attr['id']] ? encode($data[$attr['id']]) : $data[$attr['id']];

    return html('textarea', $attr['html'], $data[$attr['id']]);
}

/**
 * JSON editor
 */
function editor_json(array $attr, array $data): string
{
    if (is_array($data[$attr['id']])) {
        $data[$attr['id']] = !empty($data[$attr['id']]) ? json_encode($data[$attr['id']]) : '';
    }

    return editor_textarea($attr, $data);
}
