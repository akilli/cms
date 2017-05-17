<?php
declare(strict_types = 1);

namespace qnd;

use DomainException;
use RuntimeException;

/**
 * Cast to appropriate php type
 *
 * @param array $attr
 * @param mixed $val
 *
 * @return mixed
 */
function cast(array $attr, $val)
{
    if ($attr['nullable'] && ($val === null || $val === '')) {
        return null;
    }

    if ($attr['backend'] === 'bool') {
        return (bool) $val;
    }

    if ($attr['backend'] === 'int') {
        return (int) $val;
    }

    if ($attr['backend'] === 'decimal') {
        return (float) $val;
    }

    if ($attr['multiple'] && is_array($val)) {
        foreach ($val as $k => $v) {
            $val[$k] = cast($attr, $v);
        }

        return $val;
    }

    return (string) $val;
}

/**
 * Check wheter attribute can be ignored
 *
 * @param array $attr
 * @param array $data
 *
 * @return bool
 */
function ignorable(array $attr, array $data): bool
{
    return !empty($data['_old'][$attr['id']]) && $attr['frontend'] === 'file';
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

    if (empty($attr['opt'][0])) {
        return [];
    }

    if ($attr['type'] === 'entity') {
        return opt_entity($attr);
    }

    $args = $attr['opt'][1] ?? [];

    return call($attr['opt'][0], ...$args);
}

/**
 * Entity options
 *
 * @param array $attr
 *
 * @return array
 */
function opt_entity(array $attr): array
{
    $eId = $attr['opt'][0];
    $data = & registry('opt.entity.' . $eId);

    if ($data[$eId] === null) {
        if ($eId === 'page') {
            $data[$eId] = [];

            foreach (all('page', [], ['select' => ['id', 'name', 'pos'], 'order' => ['pos' => 'asc']]) as $item) {
                $a = array_replace($item['_entity']['attr']['pos'], ['context' => 'view', 'actions' => ['view']]);
                $data[$eId][$item['id']] = viewer($a, $item) . ' ' . $item['name'];
            }
        } else {
            $data[$eId] = array_column(all($eId, [], ['select' => ['id', 'name']]), 'name', 'id');
        }
    }

    return $data[$eId];
}

/**
 * Privilege options
 *
 * @return array
 */
function opt_privilege(): array
{
    $data = [];

    foreach (data('privilege') as $key => $priv) {
        if (empty($priv['callback'])) {
            $data[$key] = $priv['name'];
        }
    }

    return $data;
}

/**
 * Loader
 *
 * @param array $attr
 * @param array $data
 *
 * @return mixed
 */
function loader(array $attr, array $data)
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    return $attr['loader'] ? call('loader_' . $attr['loader'], $attr, $data) : $data[$attr['id']];
}

/**
 * JSON loader
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function loader_json(array $attr, array $data): array
{
    if (!$data[$attr['id']]) {
        return [];
    }

    if (is_array($data[$attr['id']])) {
        return $data[$attr['id']];
    }

    return json_decode($data[$attr['id']], true) ?: [];
}

/**
 * Saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function saver(array $attr, array $data): array
{
    return $attr['saver'] ? call('saver_' . $attr['saver'], $attr, $data) : $data;
}

/**
 * Password saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function saver_password(array $attr, array $data): array
{
    if ($data[$attr['id']]) {
        $data[$attr['id']] = password_hash($data[$attr['id']], PASSWORD_DEFAULT);
    }

    return $data;
}

/**
 * File saver
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws RuntimeException
 */
function saver_file(array $attr, array $data): array
{
    if ($data[$attr['id']] && (!($file = http_files($attr['id'])) || !file_upload($file['tmp_name'], $data[$attr['id']]))) {
        throw new RuntimeException(_('File upload failed for %s', $data[$attr['id']]));
    }

    return $data;
}

/**
 * Validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator(array $attr, array $data): array
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    if ($attr['nullable'] && $data[$attr['id']] === null) {
        return $data;
    }

    $attr['opt'] = opt($attr);

    if ($attr['validator']) {
        $data = call('validator_' . $attr['validator'], $attr, $data);
    }

    validator_uniq($attr, $data);
    validator_required($attr, $data);
    validator_boundary($attr, $data);

    return $data;
}

/**
 * Required validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_required(array $attr, array $data): array
{
    if ($attr['required'] && ($data[$attr['id']] === null || $data[$attr['id']] === '') && !ignorable($attr, $data)) {
        throw new DomainException(_('%s is required', $attr['name']));
    }

    return $data;
}

/**
 * Unique validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_uniq(array $attr, array $data): array
{
    if ($attr['uniq'] && $data[$attr['id']] !== ($data['_old'][$attr['id']] ?? null) && size($data['_entity']['id'], [[$attr['id'], $data[$attr['id']]]])) {
        throw new DomainException(_('%s must be unique', $attr['name']));
    }

    return $data;
}

/**
 * Boundary validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_boundary(array $attr, array $data): array
{
    $vals = $attr['multiple'] && is_array($data[$attr['id']]) ? $data[$attr['id']] : [$data[$attr['id']]];

    foreach ($vals as $val) {
        if (in_array($attr['backend'], ['json', 'text', 'varchar'])) {
            $val = strlen($val);
        }

        if ($attr['minval'] > 0 && $val < $attr['minval'] || $attr['maxval'] > 0 && $val > $attr['maxval']) {
            throw new DomainException(_('Value out of range'));
        }
    }

    return $data;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_opt(array $attr, array $data): array
{
    if (!empty($data[$attr['id']]) || is_scalar($data[$attr['id']]) && !is_string($data[$attr['id']])) {
        foreach ((array) $data[$attr['id']] as $v) {
            if (!isset($attr['opt'][$v])) {
                throw new DomainException(_('Invalid option for attribute %s', $attr['name']));
            }
        }
    }

    return $data;
}

/**
 * Option validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_page(array $attr, array $data): array
{
    $old = $data['_old']['id'] ?? null;

    if ($data[$attr['id']] && $old && in_array($old, one('page', [['id', $data[$attr['id']]]])['path'])) {
        throw new DomainException(_('Cannot assign the page itself or a child page as parent'));
    }

    return $data;
}

/**
 * Text validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_text(array $attr, array $data): array
{
    $data[$attr['id']] = trim((string) filter_var($data[$attr['id']], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR));

    return $data;
}

/**
 * ID validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_id(array $attr, array $data): array
{
    $data = validator_text($attr, $data);
    $data[$attr['id']] = filter_id($data[$attr['id']]);

    return $data;
}

/**
 * Email validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_email(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_EMAIL))) {
        throw new DomainException(_('Invalid email'));
    }

    return $data;
}

/**
 * URL validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_url(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_var($data[$attr['id']], FILTER_VALIDATE_URL))) {
        throw new DomainException(_('Invalid URL'));
    }

    return $data;
}

/**
 * JSON validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_json(array $attr, array $data): array
{
    if ($data[$attr['id']] && json_decode($data[$attr['id']], true) === null) {
        throw new DomainException(_('Invalid JSON notation'));
    }

    if (!$data[$attr['id']]) {
        $data[$attr['id']] = '[]';
    }

    return $data;
}

/**
 * Rich text validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 */
function validator_rte(array $attr, array $data): array
{
    $data[$attr['id']] = filter_html($data[$attr['id']]);

    return $data;
}

/**
 * Date validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_date(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], DATE['f'], DATE['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Datetime validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_datetime(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], DATETIME['f'], DATETIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * Time validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_time(array $attr, array $data): array
{
    if ($data[$attr['id']] && !($data[$attr['id']] = filter_date($data[$attr['id']], TIME['f'], TIME['b']))) {
        throw new DomainException(_('Invalid value'));
    }

    return $data;
}

/**
 * File validator
 *
 * @param array $attr
 * @param array $data
 *
 * @return array
 *
 * @throws DomainException
 */
function validator_file(array $attr, array $data): array
{
    if ($file = http_files($attr['id'])) {
        if (!in_array($attr['type'], data('file', pathinfo($file['name'], PATHINFO_EXTENSION)) ?? [])) {
            throw new DomainException(_('Invalid file %s', $file['name']));
        }

        if (($data['_old'][$attr['id']] ?? null) === $file['name']) {
            $data[$attr['id']] = $file['name'];
        } else {
            $data[$attr['id']] = filter_file($file['name'], path('media'));
        }
    }

    return $data;
}

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
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

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
        $error = html('div', ['class' => 'message error'], $data['_error'][$attr['id']]);
    }

    if ($attr['editor'] && ($html = call('editor_' . $attr['editor'], $attr, $data))) {
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
    $val = $data[$attr['id']];

    if (!is_array($val)) {
        $val = !$val && !is_numeric($val) ? [] : [$val];
    }

    if (empty($attr['opt'])) {
        $html = html('optgroup', ['label' => _('No options configured')]);
    } else {
        $html = html('option', ['value' => ''], _('Please choose'));

        foreach ($attr['opt'] as $optId => $optVal) {
            $a = ['value' => $optId];

            if (in_array($optId, $val)) {
                $a['selected'] = true;
            }

            $html .= html('option', $a, $optVal);
        }
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
        return html('span', ['id' => $attr['html']['id']], _('No options configured'));
    } elseif ($attr['backend'] === 'bool' && $attr['frontend'] === 'checkbox') {
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
        $html .= html('label', ['for' => $htmlId, 'class' => 'inline'], $optVal);
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

/**
 * Viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer(array $attr, array $data): string
{
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $attr['opt'] = opt($attr);

    if ($attr['viewer']) {
        return call('viewer_' . $attr['viewer'], $attr, $data);
    }

    return $data[$attr['id']] ? encode((string) $data[$attr['id']]) : (string) $data[$attr['id']];
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_opt(array $attr, array $data): string
{
    $vals = [];

    foreach ((array) $data[$attr['id']] as $val) {
        if (isset($attr['opt'][$val])) {
            $vals[] = $attr['opt'][$val];
        }
    }

    return $vals ? encode(implode(', ', $vals)) : '';
}

/**
 * Date viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_date(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'date')) : '';
}

/**
 * Datetime viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_datetime(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'datetime')) : '';
}

/**
 * Time viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_time(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'time')) : '';
}

/**
 * Rich text viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_rte(array $attr, array $data): string
{
    return (string) $data[$attr['id']];
}

/**
 * File viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_file(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('a', ['href' => url_media($data[$attr['id']])], $data[$attr['id']]) : '';
}

/**
 * Image viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_image(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('img', ['src' => url_media($data[$attr['id']]), 'alt' => $data[$attr['id']]], null, true) : '';
}

/**
 * Audio viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_audio(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('audio', ['src' => url_media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Video viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_video(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('video', ['src' => url_media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Filesize viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_filesize(array $attr, array $data): string
{
    if (!$data[$attr['id']]) {
        return '';
    }

    if ($data[$attr['id']] < 1000) {
        return $data[$attr['id']] . ' B';
    }

    if ($data[$attr['id']] > 1000000) {
        return round($data[$attr['id']] / 1000000, 1) . ' MB';
    }

    return round($data[$attr['id']] / 1000, 1) . ' kB';
}

/**
 * Position viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_pos(array $attr, array $data): string
{
    $parts = explode('.', $data[$attr['id']]);

    foreach ($parts as $k => $v) {
        $parts[$k] = ltrim($v, '0');
    }

    return implode('.', $parts);
}
