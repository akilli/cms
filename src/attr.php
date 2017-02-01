<?php
namespace qnd;

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
    if (in_array($value, [null, '']) && !empty($attr['nullable'])) {
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

    if ($attr['multiple'] && is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = cast($attr, $v);
        }

        return $value;
    }

    return strval($value);
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
    return !empty($item['_old'][$attr['uid']]) && empty($item['_delete'][$attr['uid']]) && in_array($attr['frontend'], ['password', 'file']);
}

/**
 * Option
 *
 * @param array $attr
 *
 * @return array
 */
function opt(array $attr): array
{
    if ($attr['backend'] === 'bool') {
        return [_('No'), _('Yes')];
    }

    if ($attr['type'] === 'entity') {
        return array_column(all(...$attr['opt']), 'name', 'id');
    }

    if (empty($attr['opt'][0])) {
        return [];
    }

    if (is_array($attr['opt'][0])) {
        return $attr['opt'][0];
    }

    $call = fqn($attr['opt'][0]);
    $params = $attr['opt'][1] ?? [];

    return $call(...$params);
}

/**
 * Attribute options
 *
 * @return array
 */
function opt_attr(): array
{
    return array_map(
        function ($item) {
            return $item['name'];
        },
        data('attr')
    );
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    return array_map(
        function ($item) {
            return $item['name'];
        },
        array_filter(
            data('privilege'),
            function ($item) {
                return !empty($item['active']) && empty($item['callback']);
            }
        )
    );
}

/**
 * Theme options
 *
 * @return array
 */
function opt_theme(): array
{
    $data = [];

    foreach (glob(path('theme', '*'), GLOB_ONLYDIR) as $dir) {
        $theme = basename($dir);
        $data[$theme] = $theme;
    }

    return $data;
}

/**
 * Menu options
 *
 * @return array
 */
function opt_position(): array
{
    $nodes = all('node', [], ['index' => ['root_id', 'id']]);
    $data = [];

    foreach (all('menu') as $id => $menu) {
        $data[$id  . ':0'] = $menu['name'];

        if (!empty($nodes[$id])) {
            foreach ($nodes[$id] as $node) {
                $data[$node['pos']] = str_repeat('&nbsp;', $node['level'] * 4) . $node['name'];
            }
        }
    }

    return $data;
}

/**
 * Loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return mixed
 */
function loader(array $attr, array $item)
{
    $item[$attr['uid']] = cast($attr, $item[$attr['uid']] ?? null);

    return $attr['loader'] && ($call = fqn('loader_' . $attr['loader'])) ? $call($attr, $item) : $item[$attr['uid']];
}

/**
 * JSON loader
 *
 * @param array $attr
 * @param array $item
 *
 * @return array
 */
function loader_json(array $attr, array $item): array
{
    if (empty($item[$attr['uid']])) {
        return [];
    }

    if (is_array($item[$attr['uid']])) {
        return $item[$attr['uid']];
    }

    return json_decode($item[$attr['uid']], true) ?: [];
}

/**
 * Deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function deleter(array $attr, array & $item): bool
{
    return !$attr['deleter'] || ($call = fqn('deleter_' . $attr['deleter'])) && $call($attr, $item);
}

/**
 * File deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function deleter_file(array $attr, array & $item): bool
{
    if (!empty($item[$attr['uid']]) && !file_delete_media($item[$attr['uid']])) {
        $item['_error'][$attr['uid']] = _('Could not delete old file %s', $item[$attr['uid']]);
        return false;
    }

    return true;
}

/**
 * Saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function saver(array $attr, array & $item): bool
{
    return !$attr['saver'] || ($call = fqn('saver_' . $attr['saver'])) && $call($attr, $item);
}

/**
 * Password saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function saver_password(array $attr, array & $item): bool
{
    if (!empty($item[$attr['uid']]) && is_string($item[$attr['uid']])) {
        $item[$attr['uid']] = password_hash($item[$attr['uid']], PASSWORD_DEFAULT);
    }

    return true;
}

/**
 * File saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function saver_file(array $attr, array & $item): bool
{
    $item[$attr['uid']] = null;
    $file = http_files('data')[$item['_id']][$attr['uid']] ?? null;

    // Delete old file
    if (!empty($item['_old'][$attr['uid']])
        && ($file || !empty($item['_delete'][$attr['uid']]))
        && !file_delete_media($item['_old'][$attr['uid']])
    ) {
        $item['_error'][$attr['uid']] = _('Could not delete old file %s', $item['_old'][$attr['uid']]);
        return false;
    }

    // No upload
    if (!$file) {
        return true;
    }

    $value = filter_file($file['name'], project_path('media'));

    // Upload failed
    if (!move_uploaded_file($file['tmp_name'], project_path('media', $value))) {
        $item['_error'][$attr['uid']] = _('File upload failed');
        return false;
    }

    $item[$attr['uid']] = $value;

    return true;
}

/**
 * Validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator(array $attr, array & $item): bool
{
    if (!in_array('edit', $attr['actions'])) {
        return true;
    }

    $item[$attr['uid']] = cast($attr, $item[$attr['uid']] ?? null);

    if ($item[$attr['uid']] === null && !empty($attr['nullable'])) {
        return true;
    }

    $attr['opt'] = opt($attr);
    $valid = !$attr['validator'] || ($call = fqn('validator_' . $attr['validator'])) && $call($attr, $item);

    return $valid && validator_uniq($attr, $item) && validator_required($attr, $item) && validator_boundary($attr, $item);
}

/**
 * Required validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_required(array $attr, array & $item): bool
{
    if (!$attr['required'] || $item[$attr['uid']] || $attr['opt'] || ignorable($attr, $item)) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('%s is a mandatory field', $attr['name']);

    return false;
}

/**
 * Unique validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_uniq(array $attr, array & $item): bool
{
    if (empty($attr['uniq']) || !empty($attr['nullable']) && $item[$attr['uid']] === null) {
        return true;
    }

    $old = all($item['_entity']['uid'], [$attr['uid'] => $item[$attr['uid']]]);

    if (!$old || count($old) === 1 && !empty($old[$item['_id']])) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('%s must be unique', $attr['name']);

    return false;
}

/**
 * Boundary validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_boundary(array $attr, array & $item): bool
{
    $values = $attr['multiple'] && is_array($item[$attr['uid']]) ? $item[$attr['uid']] : [$item[$attr['uid']]];

    foreach ($values as $value) {
        if (in_array($attr['backend'], ['json', 'text', 'varchar'])) {
            $value = strlen($value);
        }

        if (isset($attr['minval']) && $value < $attr['minval'] || isset($attr['maxval']) && $value > $attr['maxval']) {
            return false;
        }
    }

    return true;
}

/**
 * Keyword validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_keyword(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']] || !in_array($item[$attr['uid']], data('sql', 'keyword'))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Reserved database keywords must not be used');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_opt(array $attr, array & $item): bool
{
    if (is_array($item[$attr['uid']])) {
        $item[$attr['uid']] = array_filter(
            $item[$attr['uid']],
            function ($value) {
                return !empty($value) || !is_string($value);
            }
        );
    }

    if (!empty($item[$attr['uid']]) || is_scalar($item[$attr['uid']]) && !is_string($item[$attr['uid']])) {
        foreach ((array) $item[$attr['uid']] as $v) {
            if (!isset($attr['opt'][$v])) {
                $item['_error'][$attr['uid']] = _('Invalid option for attribute %s', $attr['name']);
                $item[$attr['uid']] = null;

                return false;
            }
        }
    } elseif (!empty($attr['nullable'])) {
        $item[$attr['uid']] = null;
    }

    return true;
}

/**
 * Attribute UID validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_attr(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']]
        || !($eUid = array_search($item['entity_id'], array_filter(array_column(data('entity'), 'id', 'uid'))))
        || !($old = data('entity', $eUid)['attr'][$item[$attr['uid']]] ?? null)
        || !empty($item['id']) && $item['id'] === $old['id']
    ) {
        return validator_keyword($attr, $item);
    }

    $item['_error'][$attr['uid']] = _('UID is already in use');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Entity UID validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_entity(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']]
        || !($old = data('entity', $item[$attr['uid']]))
        || !empty($item['id']) && $item['id'] === $old['id']
    ) {
        return validator_keyword($attr, $item);
    }

    $item['_error'][$attr['uid']] = _('UID is already in use');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Text validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_text(array $attr, array & $item): bool
{
    $item[$attr['uid']] = trim((string) filter_var($item[$attr['uid']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return true;
}

/**
 * Color validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_color(array $attr, array & $item): bool
{
    return (bool) preg_match('/#[a-f0-9]{6}/', $item[$attr['uid']]);
}

/**
 * Email validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_email(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_var($item[$attr['uid']], FILTER_VALIDATE_EMAIL))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid email');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * URL validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_url(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_var($item[$attr['uid']], FILTER_VALIDATE_URL))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid URL');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * JSON validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_json(array $attr, array & $item): bool
{
    if (!$item[$attr['uid']] && ($item[$attr['uid']] = '[]') || json_decode($item[$attr['uid']], true) !== null) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid JSON notation');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Rich text validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_rte(array $attr, array & $item): bool
{
    $item[$attr['uid']] = filter_html($item[$attr['uid']]);

    return true;
}

/**
 * Date validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_date(array $attr, array & $item): bool
{
    $in = data('format', 'date.frontend');
    $out = data('format', 'date.backend');

    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_date($item[$attr['uid']], $in, $out))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid value');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Datetime validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_datetime(array $attr, array & $item): bool
{
    $in = data('format', 'datetime.frontend');
    $out = data('format', 'datetime.backend');

    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_date($item[$attr['uid']], $in, $out))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid value');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * Time validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_time(array $attr, array & $item): bool
{
    $in = data('format', 'time.frontend');
    $out = data('format', 'time.backend');

    if (!$item[$attr['uid']] || ($item[$attr['uid']] = filter_date($item[$attr['uid']], $in, $out))) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid value');
    $item[$attr['uid']] = null;

    return false;
}

/**
 * File validator
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function validator_file(array $attr, array & $item): bool
{
    $file = http_files('data')[$item['_id']][$attr['uid']] ?? null;

    if (!$file || ($ext = data('file', $file['ext'])) && in_array($attr['type'], $ext)) {
        return true;
    }

    $item['_error'][$attr['uid']] = _('Invalid file %s', $file);

    return false;
}

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

/**
 * Viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer(array $attr, array $item): string
{
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $attr['opt'] = opt($attr);

    if ($attr['viewer'] && ($call = fqn('viewer_' . $attr['viewer']))) {
        return $call($attr, $item);
    }

    return $item[$attr['uid']] ? encode((string) $item[$attr['uid']]) : (string) $item[$attr['uid']];
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_opt(array $attr, array $item): string
{
    if ($attr['opt'] && $item[$attr['uid']]) {
        $values = array_intersect_key($attr['opt'], array_fill_keys((array) $item[$attr['uid']], null));
    }

    return !empty($values) ? encode(implode(', ', $values)) : '';
}

/**
 * Date viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_date(array $attr, array $item): string
{
    return $item[$attr['uid']] ? date_format(date_create($item[$attr['uid']]), data('format', 'date.view')) : '';
}

/**
 * Datetime viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_datetime(array $attr, array $item): string
{
    return $item[$attr['uid']] ? date_format(date_create($item[$attr['uid']]), data('format', 'datetime.view')) : '';
}

/**
 * Time viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_time(array $attr, array $item): string
{
    return $item[$attr['uid']] ? date_format(date_create($item[$attr['uid']]), data('format', 'time.view')) : '';
}

/**
 * Rich text viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_rte(array $attr, array $item): string
{
    return (string) $item[$attr['uid']];
}

/**
 * Audio viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_audio(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('audio', ['src' => url_media($item[$attr['uid']]), 'controls' => true]) : '';
}

/**
 * Embed viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_embed(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('embed', ['src' => url_media($item[$attr['uid']])], null, true) : '';
}

/**
 * File viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_file(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('a', ['href' => url_media($item[$attr['uid']])], $item[$attr['uid']]) : '';
}

/**
 * Image viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_image(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('img', ['src' => image($item[$attr['uid']], $attr['context']), 'alt' => $item[$attr['uid']]], null, true) : '';
}

/**
 * Object viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_object(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('object', ['data' => url_media($item[$attr['uid']])]) : '';
}

/**
 * Video viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_video(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('video', ['src' => url_media($item[$attr['uid']]), 'controls' => true]) : '';
}

/**
 * Filesize viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_filesize(array $attr, array $item): string
{
    if (!$item[$attr['uid']]) {
        return '';
    }

    if ($item[$attr['uid']] < 1000) {
        return $item[$attr['uid']] . ' B';
    }

    if ($item[$attr['uid']] > 1000000) {
        return ($item[$attr['uid']] / 1000000) . ' MB';
    }

    return ($item[$attr['uid']] / 1000) . ' kB';
}
